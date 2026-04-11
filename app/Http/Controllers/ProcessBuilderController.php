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
         $items = collect();
        $itemsByCategoria = [
            'DOCUMENTO'  => collect(),
            'FORMULARIO' => collect(),
            'OPERACION'  => collect(),
        ];
        

        if ($procesoId) {
            $proceso = \App\Models\Proceso::findOrFail($procesoId);

            $nodos = Nodo::where('proceso_id', $proceso->id)
            ->with(['items:id']) // pivot obligatorio ya viene por withPivot
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

           $items = Item::where('proceso_id', $proceso->id)
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
            'items' => $items, // para pintar lista en el modal
            'itemsByCategoria' => $itemsByCategoria,
            'roles' => $roles,
        ]);
    }

    public function print(Proceso $proceso)
    {
        $proceso->load([
            'items',
            'nodos' => function ($query) {
                $query->with([
                    'items',
                    'responsableRol',
                    'salientes.destino',
                ])->orderBy('orden')->orderBy('id');
            },
            'relaciones' => function ($query) {
                $query->with(['origen', 'destino'])->orderBy('prioridad')->orderBy('id');
            },
        ]);

        $nodos = $proceso->nodos;
        $relaciones = $proceso->relaciones;

        return view('process_builder.print', [
            'proceso' => $proceso,
            'nodos' => $nodos,
            'relaciones' => $relaciones,
            'generatedAt' => now(),
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

    public function itemsNodo(Nodo $nodo)
    {
        $items = $nodo->items()
            ->select('items.id', 'items.nombre', 'items.categoria', 'items.requiere_evidencia', 'items.allowed_extensions')
            ->get()
            ->map(fn($it) => [
                'id' => $it->id,
                'nombre' => $it->nombre,
                'categoria' => $it->categoria,
                'requiere_evidencia' => (bool)$it->requiere_evidencia,
                'allowed_extensions'=> $it->allowed_extensions,
                'obligatorio' => (int)($it->pivot->obligatorio ?? 1),
            ]);

        return response()->json(['items' => $items]);
    }
    public function updateNodoPort(Request $r, Nodo $nodo)
    {
        $data = $r->validate([
            'port' => 'required|in:in,out',
            'side' => 'required|in:left,right,top,bottom',
            'offset' => 'required|integer|min:0|max:5000',
        ]);

        if ($data['port'] === 'in') {
            $nodo->in_side = $data['side'];
            $nodo->in_offset = $data['offset'];
        } else {
            $nodo->out_side = $data['side'];
            $nodo->out_offset = $data['offset'];
        }

        $nodo->save();

        return response()->json(['ok' => true]);
    }
    public function updateRelacionPort(Request $r, NodoRelacion $relacion)
    {
        $data = $r->validate([
            'side' => 'required|in:left,right,top,bottom',
            'offset' => 'required|integer|min:0|max:5000',
        ]);

        $relacion->out_side = $data['side'];
        $relacion->out_offset = $data['offset'];
        $relacion->save();

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
            ->select('id','proceso_id','nombre','tipo_nodo','orden','pos_x','pos_y','in_side','in_offset','out_side','out_offset')
            ->get();

        $relaciones = NodoRelacion::where('proceso_id', $proceso->id)
            ->select('id','nodo_origen_id','nodo_destino_id','condicion','prioridad','out_side','out_offset','bend_x','bend_y')
            ->get();
        return response()->json([
            'nodos' => $nodos,
            'relaciones' => $relaciones,
        ]);
    }

    public function autoLayout(Proceso $proceso)
    {
        $nodos = Nodo::where('proceso_id', $proceso->id)
            ->orderBy('orden')
            ->orderBy('id')
            ->get(['id', 'tipo_nodo', 'orden', 'pos_x', 'pos_y']);

        if ($nodos->isEmpty()) {
            return response()->json(['ok' => true, 'positions' => []]);
        }

        $relaciones = NodoRelacion::where('proceso_id', $proceso->id)
            ->orderBy('prioridad')
            ->orderBy('id')
            ->get(['id', 'nodo_origen_id', 'nodo_destino_id', 'prioridad']);

        $nodesById = $nodos->keyBy('id');
        $adjacency = [];
        $incoming = [];
        $depths = [];

        foreach ($nodos as $nodo) {
            $adjacency[$nodo->id] = [];
            $incoming[$nodo->id] = 0;
            $depths[$nodo->id] = 0;
        }

        foreach ($relaciones as $relacion) {
            if (!$nodesById->has($relacion->nodo_origen_id) || !$nodesById->has($relacion->nodo_destino_id)) {
                continue;
            }

            $adjacency[$relacion->nodo_origen_id][] = [
                'to' => (int) $relacion->nodo_destino_id,
                'priority' => (int) ($relacion->prioridad ?? 1),
            ];
            $incoming[$relacion->nodo_destino_id]++;
        }

        foreach ($adjacency as &$edges) {
            usort($edges, function ($a, $b) {
                return [$a['priority'], $a['to']] <=> [$b['priority'], $b['to']];
            });
        }
        unset($edges);

        $typeWeight = [
            'inicio' => 0,
            'actividad' => 1,
            'decision' => 2,
            'conector' => 3,
            'fin' => 4,
        ];

        $startIds = $nodos
            ->filter(fn ($nodo) => $nodo->tipo_nodo === 'inicio')
            ->sortBy(fn ($nodo) => [$nodo->orden ?? PHP_INT_MAX, $nodo->id])
            ->pluck('id')
            ->values()
            ->all();

        $zeroInDegreeIds = $nodos
            ->filter(fn ($nodo) => ($incoming[$nodo->id] ?? 0) === 0 && !in_array($nodo->id, $startIds, true))
            ->sortBy(fn ($nodo) => [$typeWeight[$nodo->tipo_nodo] ?? 9, $nodo->orden ?? PHP_INT_MAX, $nodo->id])
            ->pluck('id')
            ->values()
            ->all();

        $queue = array_values(array_merge($startIds, $zeroInDegreeIds));
        $visited = [];
        $topological = [];

        while (!empty($queue)) {
            $currentId = array_shift($queue);
            if (isset($visited[$currentId])) {
                continue;
            }

            $visited[$currentId] = true;
            $topological[] = $currentId;

            foreach ($adjacency[$currentId] as $edge) {
                $toId = $edge['to'];
                $depths[$toId] = max($depths[$toId] ?? 0, ($depths[$currentId] ?? 0) + 1);
                $incoming[$toId] = max(0, ($incoming[$toId] ?? 0) - 1);
                if (($incoming[$toId] ?? 0) === 0) {
                    $queue[] = $toId;
                }
            }

            usort($queue, function ($a, $b) use ($depths, $nodesById, $typeWeight) {
                $nodeA = $nodesById[$a];
                $nodeB = $nodesById[$b];

                return [
                    $depths[$a] ?? 0,
                    $typeWeight[$nodeA->tipo_nodo] ?? 9,
                    $nodeA->orden ?? PHP_INT_MAX,
                    $a,
                ] <=> [
                    $depths[$b] ?? 0,
                    $typeWeight[$nodeB->tipo_nodo] ?? 9,
                    $nodeB->orden ?? PHP_INT_MAX,
                    $b,
                ];
            });
        }

        $maxDepth = empty($depths) ? 0 : max($depths);
        $remaining = $nodos
            ->filter(fn ($nodo) => !isset($visited[$nodo->id]))
            ->sortBy(fn ($nodo) => [$typeWeight[$nodo->tipo_nodo] ?? 9, $nodo->orden ?? PHP_INT_MAX, $nodo->id])
            ->values();

        foreach ($remaining as $offset => $nodo) {
            $depths[$nodo->id] = $maxDepth + 1 + $offset;
            $topological[] = $nodo->id;
        }

        $nodeWidth = 260;
        $nodeHeight = 96;
        $columnGap = 340;
        $rowGap = 150;
        $baseX = 120;
        $baseY = 120;
        $printTitleBlockReserve = 420;
        $printFooterReserve = 140;
        $targetPrintableRatio = 1.58;
        $nodeCount = count($topological);

        $bestRowsPerColumn = 8;
        $bestScore = PHP_FLOAT_MAX;

        for ($rowsPerColumn = 5; $rowsPerColumn <= 12; $rowsPerColumn++) {
            $columnCount = (int) ceil($nodeCount / $rowsPerColumn);
            $layoutWidth = ($columnCount > 0 ? (($columnCount - 1) * $columnGap) : 0) + $nodeWidth + ($baseX * 2) + $printTitleBlockReserve;
            $layoutHeight = (($rowsPerColumn - 1) * $rowGap) + $nodeHeight + ($baseY * 2) + $printFooterReserve;
            $ratio = $layoutHeight > 0 ? ($layoutWidth / $layoutHeight) : $targetPrintableRatio;
            $score = abs($ratio - $targetPrintableRatio) + ($columnCount * 0.035);

            if ($score < $bestScore) {
                $bestScore = $score;
                $bestRowsPerColumn = $rowsPerColumn;
            }
        }

        $positions = [];
        $logicalOrder = 1;

        DB::transaction(function () use ($topological, $nodesById, $bestRowsPerColumn, $columnGap, $rowGap, $baseX, $baseY, &$positions, &$logicalOrder) {
            foreach (array_values($topological) as $index => $nodeId) {
                $node = $nodesById[$nodeId];
                $columnIndex = (int) floor($index / $bestRowsPerColumn);
                $rowIndex = $index % $bestRowsPerColumn;
                $x = $baseX + ($columnIndex * $columnGap);
                $y = $baseY + ($rowIndex * $rowGap);

                $node->update([
                    'orden' => $logicalOrder++,
                    'pos_x' => $x,
                    'pos_y' => $y,
                ]);

                $positions[] = [
                    'id' => $nodeId,
                    'pos_x' => $x,
                    'pos_y' => $y,
                    'orden' => $node->orden,
                ];
            }
        });

        return response()->json([
            'ok' => true,
            'positions' => $positions,
            'layout' => [
                'rows_per_column' => $bestRowsPerColumn,
                'orientation' => 'vertical',
            ],
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
        // ===========================
        // (A) Datos base del nodo
        // ===========================
        $data = $r->validate([
            'nombre' => 'required|string|max:255',
            'tipo_nodo' => 'required|string|max:30',
            'orden' => 'nullable|integer|min:1',
            'sla_horas' => 'nullable|integer|min:0',
            'activo' => 'nullable|boolean',
            'responsable_rol_id' => 'nullable|integer|exists:roles,id',
            'descripcion' => 'nullable|string|max:5000',

            // payload JSON con filas de items (para UI tipo "transiciones")
            'items_payload' => 'nullable|string',
        ]);

        $data['activo'] = $r->boolean('activo', true);

        // Actualiza nodo
        $nodo->update($data);

        // ===========================
        // (B) Guardar Items del nodo
        // ===========================
        // ❌ BUG ANTERIOR: estabas usando $request (NO EXISTE) en vez de $r
        // ✅ Ahora leemos un JSON con filas: [{item_id:1, obligatorio:1}, ...]
        $raw = $r->input('items_payload');

        $rows = [];
        if ($raw) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $rows = $decoded;
            }
        }

        // Construimos syncData para pivot nodo_items
        // formato: [ item_id => ['obligatorio' => 1], ... ]
        $syncData = [];
        foreach ($rows as $row) {
            $itemId = (int)($row['item_id'] ?? 0);
            if ($itemId <= 0) continue;

            $obligatorio = (int)($row['obligatorio'] ?? 1) === 1 ? 1 : 0;
            $syncData[$itemId] = ['obligatorio' => $obligatorio];
        }

        // ✅ Esto hace:
        // - agrega los seleccionados
        // - actualiza obligatorio
        // - elimina los que ya no estén
        $nodo->items()->sync($syncData);

        return back()->with('ok', 'Nodo actualizado');
    }

    public function storeItem(Request $r, Proceso $proceso)
    {
        $csv = $r->input('allowed_extensions_csv');
        $exts = $csv ? array_values(array_filter(array_map(fn($x)=>strtolower(trim($x)), explode(',', $csv)))) : null;

        
        $data = $r->validate([
            'nombre' => 'required|string|max:255',
            'categoria' => 'required|in:DOCUMENTO,FORMULARIO,OPERACION',
            'requiere_evidencia' => 'nullable|boolean',
            'activo' => 'nullable|boolean',
        ]);
        
        $data['allowed_extensions'] = $exts;
        $data['proceso_id'] = $proceso->id;
        $data['requiere_evidencia'] = $r->boolean('requiere_evidencia', true);
        $data['activo'] = $r->boolean('activo', true);

        Item::create($data);

        return back()->with('ok', 'Item creado');
    }


    public function updateItem(Request $r, Item $item)
    {
        $csv = $r->input('allowed_extensions_csv');
        $exts = $csv ? array_values(array_filter(array_map(fn($x)=>strtolower(trim($x)), explode(',', $csv)))) : null;
        $data = $r->validate([
            'nombre' => 'required|string|max:255',
            'categoria' => 'required|in:DOCUMENTO,FORMULARIO,OPERACION',
            'requiere_evidencia' => 'nullable|boolean',
            'activo' => 'nullable|boolean',
        ]);
        $data['allowed_extensions'] = $exts;
        $data['requiere_evidencia'] = $r->boolean('requiere_evidencia', true);
        $data['activo'] = $r->boolean('activo', true);

        $item->update($data);
        return back();
    }
}
