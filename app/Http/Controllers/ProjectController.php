<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\Project;
use App\Models\Proceso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    public function index(Workspace $workspace)
    {
        $projects = $workspace->projects()->orderBy('name')->get();
        return view('projects.index', compact('workspace', 'projects'));
    }

    public function create(Workspace $workspace)
    {
        $procesos = Proceso::orderBy('nombre')->get();
        return view('projects.create', compact('workspace','procesos'));
    }

    public function store(Request $request, Workspace $workspace)
    {
        $data = $request->validate([
            'proceso_id'   => ['required','integer','exists:procesos,id'],
            'name'         => ['required','string','max:255'],
            'description'  => ['nullable','string'],
        ]);

        $userId = $request->user()?->id;

        DB::transaction(function () use ($data, $workspace, $userId) {

            $slugBase = Str::slug($data['name']);
            $slug = $slugBase ?: 'project';
            $i = 2;

            while ($workspace->projects()->where('slug', $slug)->exists()) {
                $slug = ($slugBase ?: 'project') . '-' . $i;
                $i++;
            }

            $project = Project::create([
                'workspace_id' => $workspace->id,
                'proceso_id'   => $data['proceso_id'],
                'name'         => $data['name'],
                'slug'         => $slug,
                'description'  => $data['description'] ?? null,
                'archived'     => 0,
            ]);

            // correlativo simple (luego lo afinamos)
            $correlativo = 'PRJ-' . $project->id;

            \App\Models\Expediente::create([
                'project_id'     => $project->id,
                'proceso_id'     => $project->proceso_id,
                'nodo_actual_id' => null,
                'correlativo'    => $correlativo,
                'titulo'         => $project->name,
                'estado'         => 'abierto',
                'creado_por'     => $userId,
            ]);
        });

        return redirect()->route('dashboard')->with('success', 'Proyecto creado');
    }

    /**
     * GET /projects/{project}/start-node
     * Devuelve el nodo de inicio del proceso asociado al proyecto (o el nodo_id solicitado),
     * junto con ítems y transiciones (salidas).
     */
    public function startNode(Project $project, Request $request)
    {
        // Solo JSON/AJAX
        if (!$request->expectsJson() && !$request->ajax()) {
            return response()->json(['ok' => false, 'message' => 'Bad request'], 400);
        }

        // Si el proyecto NO tiene proceso ligado
        if (empty($project->proceso_id)) {
            return response()->json(['ok' => false]);
        }

        // 1) Determinar nodo: si viene nodo_id => ese; si no => inicio
        $nodoId = (int) $request->query('nodo_id', 0);

        if ($nodoId > 0) {
            $nodo = DB::table('nodos')
                ->where('id', $nodoId)
                ->where('proceso_id', $project->proceso_id)
                ->first();
        } else {
            $nodo = DB::table('nodos')
                ->where('proceso_id', $project->proceso_id)
                ->orderBy('orden', 'asc')
                ->orderBy('id', 'asc')
                ->first();
        }

        if (!$nodo) {
            return response()->json(['ok' => false, 'message' => 'No hay nodo disponible para este proceso'], 404);
        }

        // 2) ITEMS
        $items = [];
        try {
             if (Schema::hasTable('nodo_items') && Schema::hasTable('items')) {
                $rows = DB::table('nodo_items as ni')
                    ->join('items as i', 'i.id', '=', 'ni.item_id')
                    ->where('ni.nodo_id', $nodo->id)
                    ->select([
                        'ni.id as nodo_item_id',          // ✅ ID único del ítem dentro del nodo
                        'ni.item_id as item_id',          // (opcional) ID del catálogo de items
                        'i.nombre as nombre',
                        'i.categoria as categoria',
                        'i.requiere_evidencia as requiere_evidencia',
                        'ni.obligatorio as obligatorio',
                    ])
                    ->orderBy('ni.id')
                    ->get();

                $items = $rows->map(function ($r) {
                    return [
                        'id'          => (int)($r->nodo_item_id ?? 0),   // ✅ lo que tu frontend necesita
                        'item_id'     => (int)($r->item_id ?? 0),        // opcional
                        'nombre'      => $r->nombre ?? '',
                        'categoria'   => $r->categoria ?? '—',
                        'requiere_evidencia' => (bool)($r->requiere_evidencia ?? false),
                        'obligatorio' => (bool)($r->obligatorio ?? false),
                    ];
                })->all();
            }
        } catch (\Throwable $e) {
            // En local te conviene loguear para no quedar “ciego”
            Log::error('start-node items error: '.$e->getMessage());
            $items = [];
        }

        // 3) TRANSICIONES (salidas)
        // Tu tabla nodo_relaciones usa:
        // - condicion   => etiqueta del botón
        // - prioridad   => orden
        $transiciones = [];
        try {
            if (Schema::hasTable('nodo_relaciones') && Schema::hasTable('nodos')) {
                $rows = DB::table('nodo_relaciones as r')
                    ->join('nodos as nd', 'nd.id', '=', 'r.nodo_destino_id')
                    ->where('r.nodo_origen_id', $nodo->id)
                    ->where('nd.proceso_id', $project->proceso_id)
                    ->select([
                        'r.condicion as etiqueta',
                        'r.prioridad as orden',
                        'nd.id as nodo_destino_id',
                        'nd.nombre as nodo_destino_nombre',
                    ])
                    ->orderBy('r.prioridad')
                    ->orderBy('nd.id')
                    ->get();

                $rows = DB::table('nodo_relaciones as r')
                ->join('nodos as nd', 'nd.id', '=', 'r.nodo_destino_id')
                ->where('r.nodo_origen_id', $nodo->id)
                ->where('nd.proceso_id', $project->proceso_id)
                ->select([
                    'r.condicion as etiqueta',
                    'r.prioridad as orden',
                    'nd.id as nodo_destino_id',
                    'nd.nombre as nodo_destino_nombre',
                    'nd.tipo_nodo as nodo_destino_tipo',
                ])
                ->orderBy('r.prioridad')
                ->orderBy('nd.id')
                ->get();

            $transiciones = $rows->map(function ($r) {
                $label = trim((string)($r->etiqueta ?? ''));

                if ($label === '') {
                    $label = trim((string)($r->nodo_destino_nombre ?? '')) ?: 'Continuar';
                }

                return [
                    'etiqueta'            => $label,
                    'orden'               => (int)($r->orden ?? 0),
                    'nodo_destino_id'     => (int)($r->nodo_destino_id ?? 0),
                    'nodo_destino_nombre' => $r->nodo_destino_nombre ?? '',
                    'is_end'              => strtolower((string)($r->nodo_destino_tipo ?? '')) === 'fin',
                ];
            })->all();
            }
        } catch (\Throwable $e) {
            Log::error('start-node transiciones error: '.$e->getMessage());
            $transiciones = [];
        }

        return response()->json([
            'ok'   => true,
            'nodo' => [
                'id'           => (int)$nodo->id,
                'nombre'       => $nodo->nombre ?? '',
                'descripcion'  => $nodo->descripcion ?? '',
                'sla_horas'    => isset($nodo->sla_horas) && (int)$nodo->sla_horas > 0 ? (int)$nodo->sla_horas : null,
                'items'        => $items,
                'transiciones' => $transiciones,
            ],
        ]);
    }

    public function startTasks(Project $project, Request $request)
        {
            if (!$request->expectsJson() && !$request->ajax()) {
                return response()->json(['ok' => false, 'message' => 'Bad request'], 400);
            }

            if (empty($project->proceso_id)) {
                return response()->json(['ok' => true, 'tasks' => []]);
            }

            $rows = \DB::table('tasks as t')
                ->join('nodos as n', 'n.id', '=', 't.nodo_id')
                ->where('t.project_id', $project->id)
                ->whereNull('t.archived_at')
                ->where('n.proceso_id', $project->proceso_id)
                ->where('n.tipo_nodo', 'inicio')
                ->select([
                    't.id',
                    't.title',
                    't.description',
                    't.status_id',
                    't.priority',
                    \DB::raw('DATE(t.start_at) as start_at'),
                    \DB::raw('DATE(t.due_at) as due_at'),
                    't.nodo_id',
                ])
                ->orderBy('t.id', 'desc')
                ->limit(200) // crítico: evita combos gigantes
                ->get();

            return response()->json([
                'ok' => true,
                'tasks' => $rows,
            ]);
        }
}
