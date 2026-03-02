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

        $status = $project->statuses()
            ->where('id', (int)$data['status_id'])
            ->firstOrFail();

        $destEstado = $this->getEstadoByStatusId((int)$status->id);
        if ($destEstado === 'RECHAZADO') {
            return response()->json([
                'message' => 'No se permite crear tareas en un estado RECHAZADO.'
            ], 409);
        }

        if (!empty($project->proceso_id)) {

            if (empty($data['nodo_id'])) {
                return response()->json([
                    'message' => 'Este proyecto está ligado a un proceso. Falta nodo_id.'
                ], 422);
            }

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

            if ((int)$data['nodo_id'] !== (int)$startNodoId) {
                return response()->json([
                    'message' => 'Solo se permite crear la tarea del nodo de inicio para este proyecto.'
                ], 422);
            }
        }

        $maxPos = Task::where('project_id', $project->id)
            ->where('status_id', $status->id)
            ->max('position');

        $task = new Task();
        $task->project_id        = $project->id;
        $task->status_id         = (int)$status->id;
        $task->project_status_id = (int)$status->id;
        $task->title             = $data['title'];
        $task->description       = $data['description'] ?? null;
        $task->priority          = $data['priority'] ?? null;
        $task->start_at          = $data['start_at'] ?? null;
        $task->due_at            = $data['due_at'] ?? null;
        
        $dueAt = $data['due_at'] ?? null;

        if (!empty($project->proceso_id) && !empty($data['nodo_id'])) {
            $slaHoras = (int) DB::table('nodos')->where('id', (int)$data['nodo_id'])->value('sla_horas');
            if ($slaHoras > 0) {
                $dueAt = now()->addHours($slaHoras); // usando "ahora" como base de creación
            }
        }

        $task->due_at = $dueAt;
        
        $task->nodo_id           = $data['nodo_id'] ?? null;
        $task->created_by        = auth()->id();
        $task->position          = (int)($maxPos ?? 0) + 1;
        $task->save();

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

        $projectId = (int)$task->project_id;

        $statusDestino = $task->project
            ->statuses()
            ->where('id', (int)$data['status_id'])
            ->firstOrFail();

        $destStatusId = (int)$statusDestino->id;

        // Bloqueo SOLO para esta tarea
        $this->assertTaskEditable($task, $destStatusId);

        DB::transaction(function () use ($task, $destStatusId, $data, $projectId) {

            $ordered = collect($data['ordered_ids'] ?? [])
                ->map(fn($id) => (int)$id)
                ->filter()
                ->values();

            // Asegurar que ordered_ids incluya la tarea movida (si el JS no la incluyó)
            if ($ordered->isNotEmpty() && !$ordered->contains((int)$task->id)) {
                $ordered->push((int)$task->id);
            }

            // 1) Mover la tarea al status destino
            $task->status_id         = $destStatusId;
            $task->project_status_id = $destStatusId;
            $task->save();

            // 2) Reordenar posiciones en la columna destino
            if ($ordered->isNotEmpty()) {

                // Validar que todas las IDs pertenecen al proyecto
                $validCount = Task::where('project_id', $projectId)
                    ->whereIn('id', $ordered->all())
                    ->count();

                if ($validCount !== $ordered->count()) {
                    abort(422, 'ordered_ids contiene tareas inválidas.');
                }

                foreach ($ordered as $idx => $id) {
                    Task::where('project_id', $projectId)
                        ->where('id', $id)
                        ->update([
                            'position' => $idx + 1,
                        ]);
                }

                // Si el JS manda el orden correcto, con esto ya no debería “rebotar”
            } else {
                $maxPos = Task::where('project_id', $projectId)
                    ->where('status_id', $destStatusId)
                    ->max('position');

                $task->position = (int)($maxPos ?? 0) + 1;
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

        $this->assertTaskEditable($task, null);

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

        if (!$task->nodo_id) {
            return response()->json(['ok'=>false,'message'=>'La tarea no tiene nodo asociado.'], 422);
        }

        $procesoId = (int)DB::table('projects')->where('id', $projectId)->value('proceso_id');
        if (!$procesoId) {
            return response()->json(['ok'=>false,'message'=>'El proyecto no tiene proceso asociado.'], 422);
        }

        $nextNodoId = (int)($request->input('next_nodo_id') ?? 0);

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
            $task->status_id         = (int)$doneStatusId;
            $task->project_status_id = (int)$doneStatusId;
            $task->completed_at      = now();
            $task->save();

            if ($isEnd || $nextNodoId <= 0) {
                return;
            }

            Task::create([
                'project_id'        => $projectId,
                'title'             => $nextNodo->nombre ?? 'Siguiente',
                'description'       => $nextNodo->descripcion ?? null,
                'status_id'         => (int)$todoStatusId,
                'project_status_id' => (int)$todoStatusId,
                'nodo_id'           => $nextNodoId,
                'priority'          => 3,
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
        $data = $request->validate([
            'title'        => ['required','string','max:255'],
            'description'  => ['nullable','string'],
            'status_id'    => ['required','integer'],
            'priority'     => ['nullable','integer','min:1','max:5'],
            'start_at'     => ['nullable','date'],
            'due_at'       => ['nullable'],
            'nodo_id'      => ['nullable','integer'],
        ]);

        $destStatus = DB::table('project_statuses')
            ->where('project_id', (int)$task->project_id)
            ->where('id', (int)$data['status_id'])
            ->first();

        if (!$destStatus) {
            return response()->json(['message' => 'Estado destino inválido para este proyecto.'], 422);
        }

        $destStatusId = (int)$destStatus->id;

        $this->assertTaskEditable($task, $destStatusId);

        $task->title             = $data['title'];
        $task->description       = $data['description'] ?? null;
        $task->status_id         = $destStatusId;
        $task->project_status_id = $destStatusId;
        $task->priority          = (int)($data['priority'] ?? $task->priority ?? 3);
        $task->start_at          = $data['start_at'] ?? null;
        $task->due_at            = $data['due_at'] ?? null;
        $task->nodo_id           = $data['nodo_id'] ?? $task->nodo_id;

        $task->save();

        return response()->json(['ok' => true]);
    }

    /* =======================
     * Helpers (bloqueo por tarea)
     * ======================= */

    private function getTaskCurrentStatusId(Task $task): int
    {
        $sid = (int)($task->status_id ?? 0);
        if ($sid > 0) return $sid;

        return (int)($task->project_status_id ?? 0);
    }

    private function getEstadoByStatusId(int $statusId): ?string
    {
        if ($statusId <= 0) return null;

        return DB::table('project_statuses')
            ->where('id', $statusId)
            ->value('estado');
    }

    private function assertTaskEditable(Task $task, ?int $destStatusId = null): void
    {
        $currentStatusId = $this->getTaskCurrentStatusId($task);
        $currentEstado   = $this->getEstadoByStatusId($currentStatusId);

        if ($currentEstado === 'RECHAZADO') {
            abort(409, 'La tarea está RECHAZADA y no se puede modificar.');
        }

        if ($currentEstado === 'APROBADO') {
            if (!$destStatusId) {
                abort(409, 'La tarea está APROBADA: solo puede pasar a RECHAZADO.');
            }

            $destEstado = $this->getEstadoByStatusId($destStatusId);
            if ($destEstado !== 'RECHAZADO') {
                abort(409, 'La tarea está APROBADA: solo puede pasar a RECHAZADO.');
            }
        }
    }

    /* ===== helpers existentes ===== */

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