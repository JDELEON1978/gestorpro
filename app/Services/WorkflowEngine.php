<?php

namespace App\Services;

use App\Models\{
    Expediente,
    ExpedienteItem,
    Evidencia,
    Nodo,
    NodoItem,
    NodoRelacion,
    Proceso
};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WorkflowEngine
{
    public function bootstrapExpediente(int $procesoId, array $data = [], ?int $userId = null): Expediente
    {
        return DB::transaction(function () use ($procesoId, $data, $userId) {

            $proceso = Proceso::query()->findOrFail($procesoId);

            $nodoInicial = $this->getNodoInicial($proceso);
            if (!$nodoInicial) {
                throw ValidationException::withMessages([
                    'proceso_id' => 'El proceso no tiene nodos activos para iniciar.',
                ]);
            }

            $exp = Expediente::query()->create([
                'proceso_id'     => $proceso->id,
                'nodo_actual_id' => $nodoInicial->id,
                'correlativo'    => $data['correlativo'] ?? $this->makeCorrelativo($proceso->id),
                'titulo'         => $data['titulo'] ?? ('Expediente ' . now()->format('Y-m-d H:i')),
                'estado'         => $data['estado'] ?? 'abierto',
                'creado_por'     => $userId,
            ]);

            $this->ensureItemsForNodo($exp->id, $nodoInicial->id);

            $this->audit('expedientes', $exp->id, 'BOOTSTRAP', null, [
                'nodo_inicial_id' => $nodoInicial->id,
            ], $userId);

            return $exp->fresh(['proceso','nodoActual']);
        });
    }

    public function getDestinosValidos(Expediente $expediente)
    {
        return NodoRelacion::query()
            ->where('proceso_id', $expediente->proceso_id)
            ->where('nodo_origen_id', $expediente->nodo_actual_id)
            ->orderBy('prioridad')
            ->with('destino:id,nombre')
            ->get();
    }

    public function canTransition(Expediente $expediente, int $nodoDestinoId): array
    {
        $faltantes = [];

        if (!$expediente->nodo_actual_id) {
            return ['ok' => false, 'faltantes' => ['Expediente sin nodo_actual_id.']];
        }

        // 1) Debe existir relación
        $relExiste = NodoRelacion::query()
            ->where('proceso_id', $expediente->proceso_id)
            ->where('nodo_origen_id', $expediente->nodo_actual_id)
            ->where('nodo_destino_id', $nodoDestinoId)
            ->exists();

        if (!$relExiste) {
            $faltantes[] = 'No existe relación desde el nodo actual hacia el destino.';
        }

        // 2) Obligatorios aprobados y evidencias si aplica
        $nodoActualId = $expediente->nodo_actual_id;

        $obligatorios = NodoItem::query()
            ->where('nodo_id', $nodoActualId)
            ->where('obligatorio', 1)
            ->with('item:id,nombre,requiere_evidencia')
            ->get();

        foreach ($obligatorios as $ni) {
            $ei = ExpedienteItem::query()
                ->where('expediente_id', $expediente->id)
                ->where('nodo_id', $nodoActualId)
                ->where('item_id', $ni->item_id)
                ->first();

            if (!$ei) {
                $faltantes[] = "Falta item requerido: {$ni->item->nombre}";
                continue;
            }

            // Regla anti-caos: estado y aprobado consistentes
            if ($ei->estado !== ExpedienteItem::EST_APROBADO || !$ei->aprobado) {
                $faltantes[] = "Item no aprobado: {$ni->item->nombre}";
                continue;
            }

            if ((int)$ni->item->requiere_evidencia === 1) {
                $hasEv = Evidencia::query()
                    ->where('expediente_item_id', $ei->id)
                    ->exists();

                if (!$hasEv) {
                    $faltantes[] = "Falta evidencia para: {$ni->item->nombre}";
                }
            }
        }

        return ['ok' => count($faltantes) === 0, 'faltantes' => $faltantes];
    }

    public function transition(Expediente $expediente, int $nodoDestinoId, ?int $userId = null, ?string $motivo = null): Expediente
    {
        return DB::transaction(function () use ($expediente, $nodoDestinoId, $userId, $motivo) {

            $exp = Expediente::query()->lockForUpdate()->findOrFail($expediente->id);

            $check = $this->canTransition($exp, $nodoDestinoId);
            if (!$check['ok']) {
                throw ValidationException::withMessages([
                    'transition' => $check['faltantes'],
                ]);
            }

            $from = $exp->nodo_actual_id;

            $exp->nodo_actual_id = $nodoDestinoId;
            if ($exp->estado === 'abierto') {
                $exp->estado = 'en_proceso';
            }
            $exp->save();

            // Crear items del siguiente nodo (si no existen)
            $this->ensureItemsForNodo($exp->id, $nodoDestinoId);

            $this->audit('expedientes', $exp->id, 'TRANSITION', [
                'from_nodo_id' => $from,
            ], [
                'to_nodo_id' => $nodoDestinoId,
                'motivo' => $motivo,
            ], $userId);

            return $exp->fresh(['proceso','nodoActual']);
        });
    }

    public function approveItem(ExpedienteItem $ei, ?int $userId = null, ?string $obs = null): ExpedienteItem
    {
        // Si el item requiere evidencia, no permitas aprobar sin evidencia
        $ei->loadMissing('item:id,requiere_evidencia', 'evidencias:id,expediente_item_id');

        if ((int)$ei->item->requiere_evidencia === 1 && $ei->evidencias->count() === 0) {
            throw ValidationException::withMessages([
                'accion' => ['No puedes aprobar: el item requiere evidencia y no hay archivos cargados.'],
            ]);
        }

        $ei->estado = ExpedienteItem::EST_APROBADO;
        $ei->aprobado = true;
        $ei->revisado_en = now();
        $ei->revisado_por = $userId;
        $ei->observaciones = $obs;
        $ei->save();

        $this->audit('expediente_items', $ei->id, 'APPROVE', null, [
            'estado' => $ei->estado,
        ], $userId);

        return $ei->fresh(['item','evidencias']);
    }

    public function rejectItem(ExpedienteItem $ei, ?int $userId = null, ?string $obs = null, ?int $regresarNodoId = null): ExpedienteItem
    {
        $ei->estado = ExpedienteItem::EST_RECHAZADO;
        $ei->aprobado = false;
        $ei->revisado_en = now();
        $ei->revisado_por = $userId;
        $ei->observaciones = $obs;
        $ei->rechazado_regresar_a_nodo_id = $regresarNodoId;
        $ei->save();

        $this->audit('expediente_items', $ei->id, 'REJECT', null, [
            'estado' => $ei->estado,
            'rechazado_regresar_a_nodo_id' => $regresarNodoId,
        ], $userId);

        return $ei->fresh(['item','evidencias']);
    }

    public function markUploaded(ExpedienteItem $ei, ?int $userId = null): ExpedienteItem
    {
        // SOLO marca SUBIDO si aún no está aprobado/rechazado
        if (!in_array($ei->estado, [ExpedienteItem::EST_APROBADO, ExpedienteItem::EST_RECHAZADO], true)) {
            $ei->estado = ExpedienteItem::EST_SUBIDO;
            $ei->entregado_en = now();
            $ei->recibido_por = $userId;
            $ei->aprobado = false;
            $ei->save();

            $this->audit('expediente_items', $ei->id, 'UPLOAD', null, [
                'estado' => $ei->estado,
            ], $userId);
        }

        return $ei->fresh(['item','evidencias']);
    }

    // ---------------- Helpers ----------------

    private function getNodoInicial(Proceso $proceso): ?Nodo
    {
        return Nodo::query()
            ->where('proceso_id', $proceso->id)
            ->where('activo', 1)
            ->orderBy('orden')
            ->orderBy('id')
            ->first();
    }

    private function ensureItemsForNodo(int $expedienteId, int $nodoId): void
    {
        $nodoItems = NodoItem::query()
            ->where('nodo_id', $nodoId)
            ->get(['item_id']);

        foreach ($nodoItems as $ni) {
            ExpedienteItem::query()->firstOrCreate(
                [
                    'expediente_id' => $expedienteId,
                    'nodo_id'       => $nodoId,
                    'item_id'       => $ni->item_id,
                ],
                [
                    'estado'   => ExpedienteItem::EST_PENDIENTE,
                    'aprobado' => false,
                ]
            );
        }
    }

    private function makeCorrelativo(int $procesoId): string
    {
        return now()->format('YmdHis') . '-' . $procesoId . '-' . Str::upper(Str::random(4));
    }

    private function audit(string $entidad, ?int $entidadId, string $accion, $antes, $despues, ?int $userId): void
    {
        // Si tu audit_logs tiene otros nombres de columnas, aquí es donde se ajusta.
        DB::table('audit_logs')->insert([
            'usuario_id'   => $userId,
            'entidad'      => $entidad,
            'entidad_id'   => $entidadId,
            'accion'       => $accion,
            'antes_json'   => $antes ? json_encode($antes) : null,
            'despues_json' => $despues ? json_encode($despues) : null,
            'ip'           => request()?->ip(),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }
}