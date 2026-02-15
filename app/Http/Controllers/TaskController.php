<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Project $project)
    {
        // Mantén tu idea: todo vive dentro de /dashboard
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
            'priority'    => ['nullable','integer','min:1','max:5'], // 1..5
            'status_id'   => ['required','integer'],
            'due_at'      => ['nullable','date'],                    // <- OJO: due_at
            'view'        => ['nullable','in:lista,tablero,tabla'],  // para mantener vista actual
        ]);

        // Asegurar que status_id pertenezca a ESTE proyecto
        $status = $project->statuses()->where('id', $data['status_id'])->firstOrFail();

        // Posición al final de la columna
        $maxPos = Task::where('project_id', $project->id)
            ->where('status_id', $status->id)
            ->max('position');

        $task = new Task();
        $task->project_id  = $project->id;
        $task->status_id   = $status->id;
        $task->title       = $data['title'];
        $task->description = $data['description'] ?? null;
        $task->priority    = $data['priority'] ?? null;
        $task->due_at      = $data['due_at'] ?? null;
        $task->created_by  = auth()->id();
        $task->position    = (int)($maxPos ?? 0) + 1;
        $task->save();

        $viewMode = $data['view'] ?? $request->query('view', 'tablero');

        // ✅ Si viene por AJAX, devolvemos SOLO el HTML del área de proyecto
        if ($request->expectsJson() || $request->ajax()) {
            $currentProject = $project->load(['workspace', 'statuses' => function ($q) {
                $q->orderBy('position');
            }]);

            $statuses = $currentProject->statuses;

            $tasks = $currentProject->tasks()
                ->with(['assignee'])
                ->whereNull('archived_at')
                ->orderBy('status_id')
                ->orderBy('position')
                ->get();

            $tasksByStatus = [];
            foreach ($statuses as $st) {
                $tasksByStatus[$st->id] = $tasks->where('status_id', $st->id)->values();
            }

            $html = view('dashboard._project_area', [
                'currentProject' => $currentProject,
                'statuses'       => $statuses,
                'tasksByStatus'  => $tasksByStatus,
                'viewMode'       => $viewMode,
                'justCreated'    => true,
            ])->render();

            return response()->json([
                'ok'   => true,
                'html' => $html,
            ]);
        }

        // Fallback normal
        return redirect()->route('dashboard', [
            'project_id' => $project->id,
            'view'       => $viewMode,
        ])->with('ok', 'Tarea creada.');
    }

    public function move(Task $task, Request $request)
    {
        abort(501, 'move() pendiente.');
    }
}
