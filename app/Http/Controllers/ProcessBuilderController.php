<?php

namespace App\Http\Controllers;

use App\Models\Proceso;
use App\Models\Nodo;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Models\NodoRelacion;
use Illuminate\Support\Facades\DB;

class ProcessBuilderController extends Controller
{
    public function index($procesoId = null)
    {
        $procesos = \App\Models\Proceso::orderByDesc('id')->get();

        $proceso = null;
        $nodos = collect();
        $itemsByCategoria = [
            'DOCUMENTO'  => collect(),
            'FORMULARIO' => collect(),
            'OPERACION'  => collect(),
        ];

        if ($procesoId) {
            $proceso = \App\Models\Proceso::findOrFail($procesoId);

            $nodos = \App\Models\Nodo::where('proceso_id', $proceso->id)
                ->orderBy('orden')
                ->orderBy('id')
                ->get();

            $items = \App\Models\Item::where('proceso_id', $proceso->id)
                ->orderBy('categoria')
                ->orderBy('nombre')
                ->get();

            $itemsByCategoria = $items->groupBy('categoria');
        } else {
            $proceso = $procesos->first();
            if ($proceso) {
                return redirect()->to(url('/process-builder/'.$proceso->id));
            }
        }

        // ROLES (tu modelo es Rol)
        $roles = \App\Models\Rol::orderBy('nombre')->get();

        return view('process_builder.index', [
            'procesos' => $procesos,
            'proceso' => $proceso,
            'nodos' => $nodos,
            'itemsByCategoria' => $itemsByCategoria,
            'roles' => $roles,
        ]);
    }

public function guardarRelacionesNodo(Request $request, Nodo $nodo)
{
    // OJO: "present|array" permite []
    $data = $request->validate([
        'relaciones' => ['present','array'],

        // si viene id, validarlo
        'relaciones.*.id' => ['nullable','integer','exists:nodo_relaciones,id'],

        // condicion:
        // - para decision: requerida
        // - para otros: opcional
        'relaciones.*.condicion' => ['nullable','string','max:255'],

        'relaciones.*.nodo_destino_id' => ['required','integer','exists:nodos,id','different:'.$nodo->id],
        'relaciones.*.prioridad' => ['nullable','integer','min:1','max:999'],
    ]);

    // Validación condicional: si el nodo es decision, obligar condicion
    if ($nodo->tipo_nodo === 'decision') {
        foreach (($data['relaciones'] ?? []) as $i => $r) {
            if (empty(trim((string)($r['condicion'] ?? '')))) {
                return response()->json([
                    'message' => 'La condición es obligatoria para nodos tipo decision.',
                    'errors' => ["relaciones.$i.condicion" => ['La condición es obligatoria.']]
                ], 422);
            }
        }
    }

    DB::transaction(function() use ($data, $nodo) {

        $idsRecibidos = collect($data['relaciones'])
            ->pluck('id')
            ->filter()
            ->values();

        // Relaciones actuales de ese nodo
        $q = NodoRelacion::query()
            ->where('proceso_id', $nodo->proceso_id)
            ->where('nodo_origen_id', $nodo->id);

        // Si NO vienen ids => BORRA TODAS (eso es lo que tú quieres al eliminar todas en el modal)
        if ($idsRecibidos->count() > 0) {
            $q->whereNotIn('id', $idsRecibidos);
        }

        $q->delete();

        // Re-crear / actualizar
        foreach ($data['relaciones'] as $r) {
            $attrs = [
                'proceso_id'      => $nodo->proceso_id,
                'nodo_origen_id'  => $nodo->id,
                'nodo_destino_id' => $r['nodo_destino_id'],
                'condicion'       => $r['condicion'] ?? null,
                'prioridad'       => $r['prioridad'] ?? 1,
            ];

            if (!empty($r['id'])) {
                NodoRelacion::query()
                    ->where('id', $r['id'])
                    ->where('proceso_id', $nodo->proceso_id)
                    ->where('nodo_origen_id', $nodo->id)
                    ->update($attrs);
            } else {
                NodoRelacion::query()->create($attrs);
            }
        }
    });

    return response()->json(['ok' => true]);
}

public function relacionesNodo(Nodo $nodo)
{
    $rels = NodoRelacion::query()
        ->where('proceso_id', $nodo->proceso_id)
        ->where('nodo_origen_id', $nodo->id)
        ->orderBy('prioridad')
        ->get(['id','condicion','nodo_destino_id','prioridad']);

    return response()->json(['relaciones' => $rels]);
}
    
    public function graph(Proceso $proceso)
    {
        $nodos = Nodo::where('proceso_id', $proceso->id)
            ->select('id','proceso_id','nombre','tipo_nodo','orden','pos_x','pos_y')
            ->get();

        $relaciones = NodoRelacion::where('proceso_id', $proceso->id)
            ->select('id','nodo_origen_id','nodo_destino_id','condicion','prioridad')
            ->get();

        return response()->json([
            'nodos' => $nodos,
            'relaciones' => $relaciones,
        ]);
    }

        public function updateNodoPosition(Request $r, Nodo $nodo)
        {
            $data = $r->validate([
                'pos_x' => 'required|integer|min:0|max:5000',
                'pos_y' => 'required|integer|min:0|max:5000',
            ]);

            $nodo->pos_x = $data['pos_x'];
            $nodo->pos_y = $data['pos_y'];
            $nodo->save();

            return response()->json(['ok' => true]);
        }
    public function storeRelacion(Request $r, Proceso $proceso)
    {
        $data = $r->validate([
            'nodo_origen_id'  => 'required|integer',
            'nodo_destino_id' => 'required|integer|different:nodo_origen_id',
            'condicion'       => 'nullable|string|max:120',
            'prioridad'       => 'nullable|integer|min:1|max:100',
        ]);

        // Ambos nodos deben pertenecer al proceso
        $okOrigen = Nodo::where('id', $data['nodo_origen_id'])
            ->where('proceso_id', $proceso->id)->exists();

        $okDestino = Nodo::where('id', $data['nodo_destino_id'])
            ->where('proceso_id', $proceso->id)->exists();

        abort_unless($okOrigen && $okDestino, 422, 'Nodos no pertenecen al proceso');

        // Evitar duplicado
        $rel = NodoRelacion::firstOrCreate(
            [
                'proceso_id'      => $proceso->id,
                'nodo_origen_id'  => $data['nodo_origen_id'],
                'nodo_destino_id' => $data['nodo_destino_id'],
            ],
            [
                'condicion' => $data['condicion'] ?? null,
                'prioridad' => $data['prioridad'] ?? 1,
            ]
        );

        return response()->json(['ok'=>true,'relacion'=>$rel]);
    }

    public function storeProceso(Request $r)
    {
        $data = $r->validate([
            'codigo' => 'nullable|string|max:100',
            'nombre' => 'required|string|max:255',
            'version' => 'nullable|string|max:50',
            'estado' => 'nullable|string|max:30',
            'descripcion' => 'nullable|string',
        ]);

        $p = Proceso::create($data);
        return redirect()->route('process.builder', $p->id);
    }

    public function updateProceso(Request $r, Proceso $proceso)
    {
        $data = $r->validate([
            'codigo' => 'nullable|string|max:100',
            'nombre' => 'required|string|max:255',
            'version' => 'nullable|string|max:50',
            'estado' => 'nullable|string|max:30',
            'descripcion' => 'nullable|string',
        ]);

        $proceso->update($data);
        return back();
    }

public function storeNodo(Request $r, Proceso $proceso)
{
    $data = $r->validate([
        'nombre' => 'required|string|max:255',
        'tipo_nodo' => 'required|in:inicio,actividad,decision,fin,conector',
        'orden' => 'nullable|integer|min:1',
        'sla_horas' => 'nullable|integer|min:0',
        'activo' => 'nullable|boolean',
        'responsable_rol_id' => 'nullable|integer|exists:roles,id',
        'descripcion' => 'nullable|string|max:5000',
    ]);

    $data['proceso_id'] = $proceso->id;
    $data['activo'] = $r->boolean('activo', true);

    // auto-orden si no mandan
    if (empty($data['orden'])) {
        $data['orden'] = (int) \App\Models\Nodo::where('proceso_id', $proceso->id)->max('orden') + 1;
    }

    \App\Models\Nodo::create($data);

    return back()->with('ok', 'Nodo creado');
}


    public function updateNodo(Request $r, Nodo $nodo)
    {
        $data = $r->validate([
            'nombre' => 'required|string|max:255',
            'tipo_nodo' => 'required|string|max:30',
            'orden' => 'nullable|integer|min:1',
            'sla_horas' => 'nullable|integer|min:0',
            'activo' => 'nullable|boolean',
            'responsable_rol_id' => 'nullable|integer|exists:roles,id',
            'descripcion' => 'nullable|string|max:5000',
        ]);

        $data['activo'] = $r->boolean('activo', true);

        $nodo->update($data);
        return back();
    }

    public function storeItem(Request $r, Proceso $proceso)
    {
        $data = $r->validate([
            'nombre' => 'required|string|max:255',
            'categoria' => 'required|in:DOCUMENTO,FORMULARIO,OPERACION',
            'requiere_evidencia' => 'nullable|boolean',
            'activo' => 'nullable|boolean',
        ]);

        $data['proceso_id'] = $proceso->id;
        $data['requiere_evidencia'] = $r->boolean('requiere_evidencia', true);
        $data['activo'] = $r->boolean('activo', true);

        Item::create($data);

        return back()->with('ok', 'Item creado');
    }


    public function updateItem(Request $r, Item $item)
    {
        $data = $r->validate([
            'nombre' => 'required|string|max:255',
            'categoria' => 'required|in:DOCUMENTO,FORMULARIO,OPERACION',
            'requiere_evidencia' => 'nullable|boolean',
            'activo' => 'nullable|boolean',
        ]);

        $data['requiere_evidencia'] = $r->boolean('requiere_evidencia', true);
        $data['activo'] = $r->boolean('activo', true);

        $item->update($data);
        return back();
    }
}
