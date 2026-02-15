<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // 1) Vista actual (lista|tablero|tabla)
        $viewMode = $request->query('view', 'tablero');
        if (!in_array($viewMode, ['lista', 'tablero', 'tabla'], true)) {
            $viewMode = 'tablero';
        }

        // 2) Workspaces del usuario + projects
        $workspaces = $user->workspaces()
            ->with(['projects' => function ($q) {
                $q->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        // 3) Proyecto seleccionado
        $projectId = (int) $request->query('project_id', 0);

        $currentProject = null;
        $statuses = collect();
        $tasksByStatus = [];

        if ($projectId > 0) {
            $currentProject = Project::query()
                ->with([
                    'workspace',
                    'statuses' => function ($q) {
                        $q->orderBy('position');
                    }
                ])
                ->findOrFail($projectId);

            $statuses = $currentProject->statuses;

            // IMPORTANTE:
            // Aquí asumimos que Task usa status_id (como tu modelo Task.php).
            $tasks = $currentProject->tasks()
                ->with(['assignee'])
                ->whereNull('archived_at')
                ->orderBy('status_id')
                ->orderBy('position')
                ->get();

            foreach ($statuses as $st) {
                $tasksByStatus[$st->id] = $tasks->where('status_id', $st->id)->values();
            }
        }

        return view('dashboard', compact(
            'workspaces',
            'currentProject',
            'statuses',
            'tasksByStatus',
            'viewMode'
        ));
    }
}
