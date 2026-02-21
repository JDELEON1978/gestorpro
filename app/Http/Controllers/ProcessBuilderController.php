<?php

namespace App\Http\Controllers;

use App\Models\Proceso;
use App\Models\Nodo;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Models\NodoRelacion;

class ProcessBuilderController extends Controller
{
    public function index(?Proceso $proceso = null)
    {
        $procesos = Proceso::query()->orderBy('id', 'desc')->get();

        // si no viene proceso, toma el primero
        if (!$proceso && $procesos->count()) {
            $proceso = $procesos->first();
        }

        $nodos = $proceso
            ? Nodo::where('proceso_id', $proceso->id)->orderBy('orden')->get()
            : collect();

        $items = $proceso
            ? Item::where('proceso_id', $proceso->id)->orderBy('categoria')->orderBy('nombre')->get()
            : collect();

        $itemsByCategoria = $items->groupBy('categoria');

        return view('process_builder.index', compact('procesos', 'proceso', 'nodos', 'itemsByCategoria'));
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
