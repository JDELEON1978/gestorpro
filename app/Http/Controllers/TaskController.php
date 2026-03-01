<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function index(Project $project)
    {
        return redirect()->route('dashboard', [
            'project_id' => $project->id,
            'view'       => request('view', 'tablero'),
        ]);
    }

    public function create(Project $project, Request $request)
    {
        $statuses = $project->statuses()->orderBy('position')->get();

        $preStatusId = $request->integer('status_id');
        if (!$preStatusId) {
            $preStatusId = optional($statuses->firstWhere('is_default', true))->id
                ?? optional($statuses->first())->id;
        }

        return view('tasks.create', [
            'project'     => $project,
            'statuses'    => $statuses,
            'preStatusId' => $preStatusId,
        ]);
    }

    public function store(Project $project, Request $request)
    {
        $data = $request->validate([
            'title'       => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'priority'    => ['nullable','integer','min:1','max:5'],
            'status_id'   => ['required','integer'],
            'start_at'    => ['nullable','date'],
            'due_at'      => ['nullable','date'],
            'nodo_id'     => ['nullable','integer','exists:nodos,id'],
        ]);

        // ✅ Validar que el status pertenezca al proyecto
        $status = $project->statuses()
            ->where('id', (int)$data['status_id'])
            ->firstOrFail();

        // ✅ Si el proyecto está ligado a proceso:
        //    solo permitir crear tarea del NODO INICIO (una sola vez).
        if (!empty($project->proceso_id)) {

            // nodo_id obligatorio
            if (empty($data['nodo_id'])) {
                return response()->json([
                    'message' => 'Este proyecto está ligado a un proceso. Falta nodo_id.'
                ], 422);
            }

            // Buscar nodo inicio: el más antiguo del proceso (ajústalo si tienes campo is_start)
            $startNodoId = DB::table('nodos')
                ->where('proceso_id', $project->proceso_id)
                ->where('tipo_nodo', 'inicio')
                ->orderBy('id')
                ->value('id');

            if (!$startNodoId) {
                return response()->json([
                    'message' => 'El proceso asociado no tiene nodos. Configura un nodo inicio.'
                ], 422);
            }

            // Solo permitir el nodo inicio
            if ((int)$data['nodo_id'] !== (int)$startNodoId) {
                return response()->json([
                    'message' => 'Solo se permite crear la tarea del nodo de inicio para este proyecto.'
                ], 422);
            }

            // Evitar duplicado (si ya existe la tarea del inicio)
            $yaExisteInicio = Task::where('project_id', $project->id)
                ->where('nodo_id', (int)$startNodoId)
                ->exists();

           /* if ($yaExisteInicio) {
                return response()->json([
                    'message' => 'Ya existe la tarea de inicio de este proceso en el proyecto.'
                ], 422);
            }*/
        }

        // Posición al final dentro de la columna
        $maxPos = Task::where('project_id', $project->id)
            ->where('status_id', $status->id)
            ->max('position');

        $task = new Task();
        $task->project_id  = $project->id;
        $task->status_id   = $status->id;
        $task->title       = $data['title'];
        $task->description = $data['description'] ?? null;
        $task->priority    = $data['priority'] ?? null;
        $task->start_at    = $data['start_at'] ?? null;
        $task->due_at      = $data['due_at'] ?? null;
        $task->nodo_id     = $data['nodo_id'] ?? null;
        $task->created_by  = auth()->id();
        $task->position    = (int)($maxPos ?? 0) + 1;
        $task->save();

        // ✅ Como estás usando fetch() con Accept: application/json → responde JSON
        return response()->json([
            'ok'   => true,
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'status_id' => $task->status_id,
            ]
        ]);
    }

    /**
     * PATCH /tasks/{task}/move
     */
    public function move(Task $task, Request $request)
    {
        $data = $request->validate([
            'status_id'    => ['required','integer'],
            'ordered_ids'  => ['nullable','array'],
            'ordered_ids.*'=> ['integer'],
        ]);

        $projectId = (int) $task->project_id;

        // Validar status destino pertenezca al mismo proyecto
        $status = $task->project
            ->statuses()
            ->where('id', (int)$data['status_id'])
            ->firstOrFail();

        DB::transaction(function () use ($task, $status, $data, $projectId) {

            $ordered = collect($data['ordered_ids'] ?? [])
                ->map(fn($id) => (int)$id)
                ->filter()
                ->values();

            if ($ordered->isNotEmpty()) {

                $validCount = Task::where('project_id', $projectId)
                    ->whereIn('id', $ordered->all())
                    ->count();

                if ($validCount !== $ordered->count()) {
                    abort(422, 'ordered_ids contiene tareas inválidas.');
                }

                $task->status_id = $status->id;
                $task->save();

                foreach ($ordered as $idx => $id) {
                    Task::where('project_id', $projectId)
                        ->where('id', $id)
                        ->update([
                            'status_id' => $status->id,
                            'position'  => $idx + 1,
                        ]);
                }
            } else {
                $maxPos = Task::where('project_id', $projectId)
                    ->where('status_id', $status->id)
                    ->max('position');

                $task->status_id = $status->id;
                $task->position  = (int)($maxPos ?? 0) + 1;
                $task->save();
            }
        });

        return response()->json(['ok' => true]);
    }


    public function advance(Task $task, Request $request)
    {
        $request->validate([
            'next_nodo_id' => ['nullable','integer'],
        ]);

        $projectId = (int)$task->project_id;
        if (!$projectId) {
            return response()->json(['ok'=>false,'message'=>'La tarea no tiene proyecto asociado.'], 422);
        }

        $doneStatusId = $this->projectStatusId($projectId, 'done');
        if (!$doneStatusId) {
            return response()->json(['ok'=>false,'message'=>'No existe el estado Done en este proyecto.'], 422);
        }

        $todoStatusId = $this->projectStatusId($projectId, 'todo')
            ?: $this->projectStatusIdByName($projectId, ['to do','todo'])
            ?: $this->projectDefaultStatusId($projectId)
            ?: $this->projectFirstStatusId($projectId);

        if (!$todoStatusId) {
            return response()->json(['ok'=>false,'message'=>'No hay estados configurados en este proyecto.'], 422);
        }

        // Si ya está Done, no se avanza
        if ((int)$task->project_status_id === (int)$doneStatusId) {
            return response()->json(['ok'=>false,'message'=>'La tarea ya está finalizada.(' . $doneStatusId . ')'.$todoStatusId], 409);
        }

        // Validar nodo actual
        if (!$task->nodo_id) {
            return response()->json(['ok'=>false,'message'=>'La tarea no tiene nodo asociado.'], 422);
        }

        $procesoId = (int)DB::table('projects')->where('id', $projectId)->value('proceso_id');
        if (!$procesoId) {
            return response()->json(['ok'=>false,'message'=>'El proyecto no tiene proceso asociado.'], 422);
        }

        $nextNodoId = (int)($request->input('next_nodo_id') ?? 0);

        // Validar transición (si mandaron next)
        if ($nextNodoId > 0) {
            $rel = DB::table('nodo_relaciones')
                ->where('proceso_id', $procesoId)
                ->where('nodo_origen_id', (int)$task->nodo_id)
                ->where('nodo_destino_id', $nextNodoId)
                ->first();

            if (!$rel) {
                return response()->json(['ok'=>false,'message'=>'Transición inválida para este nodo.'], 422);
            }
        }

        // Determinar si next es FIN
        $isEnd = false;
        $nextNodo = null;

        if ($nextNodoId > 0) {
            $nextNodo = DB::table('nodos')
                ->where('id', $nextNodoId)
                ->where('proceso_id', $procesoId)
                ->first();

            if (!$nextNodo) {
                return response()->json(['ok'=>false,'message'=>'Nodo destino no existe en este proceso.'], 404);
            }

            $isEnd = strtolower((string)($nextNodo->tipo_nodo ?? '')) === 'fin';
        }

        DB::transaction(function () use (
            $task, $doneStatusId, $todoStatusId, $projectId, $nextNodoId, $nextNodo, $isEnd
        ) {
            // 1) Marcar actual como DONE + completed_at
            $task->project_status_id = $doneStatusId;
            $task->status_id = $doneStatusId;
            $task->completed_at = now();
            $task->save();

            // 2) Si es fin o no hay next -> no crear nada
            if ($isEnd || $nextNodoId <= 0) {
                return;
            }

            // 3) Crear siguiente tarea (SILENCIOSO)
            \App\Models\Task::create([
                'project_id'        => $projectId,
                'title'             => $nextNodo->nombre ?? 'Siguiente',
                'description'       => $nextNodo->descripcion ?? null,
                'status_id'         => $todoStatusId,
                'project_status_id' => $todoStatusId,
                'nodo_id'           => $nextNodoId,
                'priority'          => 3,      // NOT NULL
                'position'          => 0,
                'created_by'        => auth()->id(),
                'parent_task_id'    => $task->id,
                'from_nodo_id'      => (int)$task->nodo_id,
            ]);
        });

        return response()->json([
            'ok' => true,
            'is_end' => $isEnd,
        ]);
    }


    public function update(Task $task, Request $request)
    {
        $doneId = $this->projectStatusId($task->project_id, 'done');
        if ($doneId && (int)$task->project_status_id === (int)$doneId) {
            return response()->json(['message' => 'La tarea está en Done y no se puede modificar.'], 409);
        }

        // --- aquí sigue tu lógica real de update ---
        // IMPORTANTE: asegúrate de actualizar project_status_id (no status_id) si así funciona tu tablero.
        // Ejemplo mínimo (adáptalo a tu app):
        $data = $request->validate([
            'title'        => ['required','string','max:255'],
            'description'  => ['nullable','string'],
            'status_id'    => ['required','integer'], // aquí viene el id de project_statuses
            'priority'     => ['nullable','integer','min:1','max:5'],
            'start_at'     => ['nullable','date'],
            'due_at'       => ['nullable'], // datetime o date según tu UI
            'nodo_id'      => ['nullable','integer'],
        ]);

        $task->title = $data['title'];
        $task->description = $data['description'] ?? null;
        $task->project_status_id = (int)$data['status_id']; // <-- clave
        $task->priority = (int)($data['priority'] ?? $task->priority ?? 3);
        $task->start_at = $data['start_at'] ?? null;
        $task->due_at = $data['due_at'] ?? null;
        $task->nodo_id = $data['nodo_id'] ?? $task->nodo_id;

        $task->save();

        return response()->json(['ok' => true]);
    }


    
    /* ===== helpers ===== */

    private function projectStatusId(int $projectId, string $slug)
    {
        return DB::table('project_statuses')
            ->where('project_id', $projectId)
            ->whereRaw('LOWER(slug) = ?', [strtolower($slug)])
            ->value('id');
    }

    private function projectStatusIdByName(int $projectId, array $names)
    {
        $names = array_map('strtolower', $names);
        return DB::table('project_statuses')
            ->where('project_id', $projectId)
            ->whereIn(DB::raw('LOWER(name)'), $names)
            ->value('id');
    }

    private function projectDefaultStatusId(int $projectId)
    {
        return DB::table('project_statuses')
            ->where('project_id', $projectId)
            ->where('is_default', 1)
            ->orderBy('position','asc')
            ->value('id');
    }

    private function projectFirstStatusId(int $projectId)
    {
        return DB::table('project_statuses')
            ->where('project_id', $projectId)
            ->orderBy('position','asc')
            ->value('id');
    }

}