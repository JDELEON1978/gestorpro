<?php

namespace App\Http\Controllers;

use App\Models\Activo;
use App\Models\ActivoEvento;
use App\Models\Catalogo;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ActivoEventoController extends Controller
{
    public function index(Request $request, Workspace $workspace): View
    {
        $this->ensureWorkspaceAccess($request, $workspace);

        $query = ActivoEvento::query()
            ->select('activo_eventos.*')
            ->join('activos', 'activos.id', '=', 'activo_eventos.activo_id')
            ->where('activos.workspace_id', $workspace->id)
            ->with(['activo.central', 'user'])
            ->orderByDesc('fecha_evento');

        if ($activoId = (int) $request->query('activo_id', 0)) {
            $query->where('activo_eventos.activo_id', $activoId);
        }

        if ($tipoEvento = $request->query('tipo_evento')) {
            $query->where('activo_eventos.tipo_evento', $tipoEvento);
        }

        if ($resultado = $request->query('resultado')) {
            $query->where('activo_eventos.resultado', $resultado);
        }

        if ($fechaDesde = $request->query('fecha_desde')) {
            $query->whereDate('activo_eventos.fecha_evento', '>=', $fechaDesde);
        }

        if ($fechaHasta = $request->query('fecha_hasta')) {
            $query->whereDate('activo_eventos.fecha_evento', '<=', $fechaHasta);
        }

        if ($solo = $request->query('solo')) {
            if ($solo === 'mantenimiento') {
                $query->where('activo_eventos.tipo_evento', 'MANTENIMIENTO');
            }

            if ($solo === 'fallas') {
                $query->where(function ($inner) {
                    $inner->where('activo_eventos.tipo_evento', 'FALLA')
                        ->orWhere('activo_eventos.resultado', 'FALLA');
                });
            }
        }

        return view('activo_eventos.index', [
            'workspace' => $workspace,
            'eventos' => $query->paginate(20)->withQueryString(),
            'activos' => $workspace->activos()->orderBy('nombre')->get(),
            'tipoEventoOptions' => $this->catalogItems('tipo_evento_activo'),
            'resultadoEventoOptions' => $this->catalogItems('resultado_evento_activo'),
            'filters' => [
                'activo_id' => $request->query('activo_id'),
                'tipo_evento' => $request->query('tipo_evento'),
                'resultado' => $request->query('resultado'),
                'fecha_desde' => $request->query('fecha_desde'),
                'fecha_hasta' => $request->query('fecha_hasta'),
                'solo' => $request->query('solo'),
            ],
        ]);
    }

    public function store(Request $request, Workspace $workspace, Activo $activo): RedirectResponse
    {
        $this->ensureWorkspaceAccess($request, $workspace);
        $this->ensureActivoBelongsToWorkspace($workspace, $activo);

        $data = $this->validatedData($request);

        $data['activo_id'] = $activo->id;
        $data['user_id'] = $request->user()->id;

        $evento = ActivoEvento::create($data);

        $this->syncActivoHorasOperacion($activo);

        return redirect()
            ->route('workspaces.activos.show', [$workspace, $activo])
            ->with('success', 'Evento registrado correctamente.');
    }

    public function edit(Request $request, Workspace $workspace, Activo $activo, ActivoEvento $evento): View
    {
        $this->ensureWorkspaceAccess($request, $workspace);
        $this->ensureActivoBelongsToWorkspace($workspace, $activo);
        abort_unless($evento->activo_id === $activo->id, 404);

        $evento->load('evidencias.user');

        return view('activo_eventos.edit', [
            'workspace' => $workspace,
            'activo' => $activo,
            'evento' => $evento,
            'tipoEventoOptions' => $this->catalogItems('tipo_evento_activo'),
            'resultadoEventoOptions' => $this->catalogItems('resultado_evento_activo'),
        ]);
    }

    public function update(Request $request, Workspace $workspace, Activo $activo, ActivoEvento $evento): RedirectResponse
    {
        $this->ensureWorkspaceAccess($request, $workspace);
        $this->ensureActivoBelongsToWorkspace($workspace, $activo);
        abort_unless($evento->activo_id === $activo->id, 404);

        $data = $this->validatedData($request);
        $data['user_id'] = $request->user()->id;

        $evento->update($data);
        $this->syncActivoHorasOperacion($activo);

        return redirect()
            ->route('workspaces.activos.show', [$workspace, $activo])
            ->with('success', 'Evento actualizado correctamente.');
    }

    public function destroy(Request $request, Workspace $workspace, Activo $activo, ActivoEvento $evento): RedirectResponse
    {
        $this->ensureWorkspaceAccess($request, $workspace);
        $this->ensureActivoBelongsToWorkspace($workspace, $activo);
        abort_unless($evento->activo_id === $activo->id, 404);

        $evento->delete();
        $this->syncActivoHorasOperacion($activo);

        return redirect()
            ->route('workspaces.activos.show', [$workspace, $activo])
            ->with('success', 'Evento eliminado correctamente.');
    }

    protected function validatedData(Request $request): array
    {
        return $request->validate([
            'tipo_evento' => ['required', 'string', 'max:30'],
            'fecha_evento' => ['required', 'date'],
            'resultado' => ['nullable', 'string', 'max:30', Rule::in($this->resultCodes())],
            'horas_operacion' => ['nullable', 'numeric', 'min:0'],
            'valor_medicion' => ['nullable', 'numeric'],
            'unidad_medicion' => ['nullable', 'string', 'max:20'],
            'costo' => ['nullable', 'numeric', 'min:0'],
            'proximo_evento_programado' => ['nullable', 'date'],
            'descripcion' => ['nullable', 'string'],
        ]);
    }

    protected function syncActivoHorasOperacion(Activo $activo): void
    {
        $maxHoras = $activo->eventos()->max('horas_operacion');

        $activo->update([
            'horas_operacion' => $maxHoras ?? 0,
        ]);
    }

    protected function catalogItems(string $nombre): Collection
    {
        $items = Catalogo::query()
            ->where('nombre', $nombre)
            ->with(['items' => fn ($query) => $query->where('activo', true)->orderBy('valor')])
            ->first()
            ?->items
            ?? collect();

        if ($items->isNotEmpty()) {
            return $items;
        }

        return match ($nombre) {
            'tipo_evento_activo' => collect([
                (object) ['codigo' => 'INSPECCION', 'valor' => 'Inspeccion'],
                (object) ['codigo' => 'MANTENIMIENTO', 'valor' => 'Mantenimiento'],
                (object) ['codigo' => 'LECTURA', 'valor' => 'Lectura'],
                (object) ['codigo' => 'FALLA', 'valor' => 'Falla'],
                (object) ['codigo' => 'PARO', 'valor' => 'Paro'],
                (object) ['codigo' => 'REPARACION', 'valor' => 'Reparacion'],
                (object) ['codigo' => 'CALIBRACION', 'valor' => 'Calibracion'],
            ]),
            'resultado_evento_activo' => collect([
                (object) ['codigo' => 'OK', 'valor' => 'OK'],
                (object) ['codigo' => 'ALERTA', 'valor' => 'Alerta'],
                (object) ['codigo' => 'FALLA', 'valor' => 'Falla'],
                (object) ['codigo' => 'PENDIENTE', 'valor' => 'Pendiente'],
            ]),
            default => collect(),
        };
    }

    protected function resultCodes(): array
    {
        $items = $this->catalogItems('resultado_evento_activo');

        if ($items->isNotEmpty()) {
            return $items->pluck('codigo')->all();
        }

        return ['OK', 'ALERTA', 'FALLA', 'PENDIENTE'];
    }

    protected function ensureWorkspaceAccess(Request $request, Workspace $workspace): void
    {
        $allowed = $request->user()
            ->workspaces()
            ->where('workspaces.id', $workspace->id)
            ->exists();

        abort_unless($allowed, 403);
    }

    protected function ensureActivoBelongsToWorkspace(Workspace $workspace, Activo $activo): void
    {
        abort_unless($activo->workspace_id === $workspace->id, 404);
    }
}
