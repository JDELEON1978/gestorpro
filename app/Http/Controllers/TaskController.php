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
        ]);

        // status debe pertenecer al proyecto
        $status = $project->statuses()->where('id', $data['status_id'])->firstOrFail();

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
        $task->created_by  = auth()->id();
        $task->position    = (int)($maxPos ?? 0) + 1;
        $task->save();

        return redirect()->route('dashboard', [
            'project_id' => $project->id,
            'view'       => request('view', 'tablero'),
        ])->with('ok', 'Tarea creada.');
    }

    /**
     * PATCH /tasks/{task}/move
     * Body JSON:
     * - status_id: int (nuevo estado)
     * - ordered_ids: array<int> (ids de la columna ya en orden visual)
     */
    public function move(Task $task, Request $request)
    {
        $data = $request->validate([
            'status_id'    => ['required','integer'],
            'ordered_ids'  => ['nullable','array'],
            'ordered_ids.*'=> ['integer'],
        ]);

        $projectId = (int) $task->project_id;

        // Validar que el status destino pertenezca al mismo proyecto
        $status = $task->project->statuses()->where('id', (int)$data['status_id'])->firstOrFail();

        DB::transaction(function () use ($task, $status, $data, $projectId) {

            // Si mandan ordered_ids, reordenamos con eso
            $ordered = collect($data['ordered_ids'] ?? [])
                ->map(fn($id) => (int)$id)
                ->filter()
                ->values();

            if ($ordered->isNotEmpty()) {

                // Seguridad: todos los IDs deben ser tareas del MISMO proyecto
                $validCount = Task::where('project_id', $projectId)
                    ->whereIn('id', $ordered->all())
                    ->count();

                if ($validCount !== $ordered->count()) {
                    abort(422, 'ordered_ids contiene tareas inválidas.');
                }

                // Actualizar status de la tarea movida (si no está ya)
                $task->status_id = $status->id;
                $task->save();

                // Reasignar posiciones según orden visual
                foreach ($ordered as $idx => $id) {
                    Task::where('project_id', $projectId)
                        ->where('id', $id)
                        ->update([
                            'status_id' => $status->id,
                            'position'  => $idx + 1,
                        ]);
                }
            } else {
                // Si no mandan orden, solo movemos al final
                $maxPos = Task::where('project_id', $projectId)
                    ->where('status_id', $status->id)
                    ->max('position');

                $task->status_id = $status->id;
                $task->position  = (int)($maxPos ?? 0) + 1;
                $task->save();
            }
        });

        // Respuesta JSON para el fetch()
        return response()->json(['ok' => true]);
    }
}
