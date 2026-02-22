<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpedienteItemReviewRequest;
use App\Http\Requests\ExpedienteTransitionRequest;
use App\Models\{Evidencia, Expediente, ExpedienteItem, Proceso};
use App\Services\WorkflowEngine;
use Illuminate\Http\Request;

class ExpedienteController extends Controller
{
    public function __construct(private WorkflowEngine $engine) {}

    public function index()
    {
        $rows = Expediente::query()
            ->with(['proceso:id,nombre,version', 'nodoActual:id,nombre'])
            ->orderByDesc('id')
            ->paginate(20);

        $procesos = Proceso::query()
            ->where('estado', 'activo')
            ->orderBy('id')
            ->get(['id','nombre','version']);

        return view('expedientes.index', compact('rows','procesos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'proceso_id' => ['required','integer','exists:procesos,id'],
            'titulo'     => ['required','string','max:255'],
        ]);

        $exp = $this->engine->bootstrapExpediente(
            (int)$request->proceso_id,
            ['titulo' => $request->titulo],
            auth()->id()
        );

        return redirect()->route('expedientes.show', $exp)->with('success', 'Expediente creado.');
    }

    public function show(Expediente $expediente)
    {
        $expediente->load([
            'proceso:id,nombre,version',
            'nodoActual:id,nombre',
        ]);

        $itemsNodoActual = $expediente->items()
            ->where('nodo_id', $expediente->nodo_actual_id)
            ->with([
                'item:id,nombre,requiere_evidencia',
                'evidencias:id,expediente_item_id,archivo_path,created_at',
            ])
            ->orderBy('id')
            ->get();

        $destinos = $this->engine->getDestinosValidos($expediente);

        return view('expedientes.show', compact('expediente','itemsNodoActual','destinos'));
    }

    public function transition(ExpedienteTransitionRequest $request, Expediente $expediente)
    {
        $this->engine->transition(
            $expediente,
            (int)$request->nodo_destino_id,
            auth()->id(),
            $request->motivo
        );

        return back()->with('success', 'Transición aplicada.');
    }

    public function reviewItem(ExpedienteItemReviewRequest $request, ExpedienteItem $expedienteItem)
    {
        if ($request->accion === 'aprobar') {
            $this->engine->approveItem($expedienteItem, auth()->id(), $request->observaciones);
        } else {
            $this->engine->rejectItem(
                $expedienteItem,
                auth()->id(),
                $request->observaciones,
                $request->rechazado_regresar_a_nodo_id
            );
        }

        return back()->with('success', 'Revisión guardada.');
    }

    public function uploadEvidencia(Request $request, ExpedienteItem $expedienteItem)
    {
        $request->validate([
            'archivo' => ['required','file','max:10240'], // 10MB
        ]);

        $file = $request->file('archivo');

        $path = $file->storeAs(
            "public/expedientes/{$expedienteItem->expediente_id}/items/{$expedienteItem->id}",
            $file->getClientOriginalName()
        );

        Evidencia::query()->create([
            'expediente_item_id' => $expedienteItem->id,
            'archivo_path'       => $path,
            'mime_type'          => $file->getMimeType(),
            'tamano_bytes'       => $file->getSize(),
            'hash_sha256'        => hash_file('sha256', $file->getRealPath()),
            'subido_por'         => auth()->id(),
        ]);

        $this->engine->markUploaded($expedienteItem->fresh(), auth()->id());

        return back()->with('success', 'Evidencia cargada.');
    }

    // opcional: endpoint para validar antes de transicionar (sin ejecutar)
    public function canTransition(Request $request, Expediente $expediente)
    {
        $request->validate([
            'nodo_destino_id' => ['required','integer','exists:nodos,id'],
        ]);

        return response()->json(
            $this->engine->canTransition($expediente, (int)$request->nodo_destino_id)
        );
    }
}