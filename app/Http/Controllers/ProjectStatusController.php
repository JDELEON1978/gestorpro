<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectStatusController extends Controller
{
    public function store(Request $request, Project $project)
    {
        // Si todavía no tienes policies, comenta esta línea temporalmente:
        // $this->authorize('viewAny', [Task::class, $project]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
        ]);

        $maxPos = $project->statuses()->max('position');
        $position = is_null($maxPos) ? 1 : ((int)$maxPos + 1);

        $baseSlug = Str::slug($data['name']);
        $slug = $baseSlug ?: 'status';

        $i = 2;
        while ($project->statuses()->where('slug', $slug)->exists()) {
            $slug = ($baseSlug ?: 'status') . '-' . $i;
            $i++;
        }

        $project->statuses()->create([
            'name' => $data['name'],
            'slug' => $slug,
            'position' => $position,
            'is_default' => false,
        ]);

        return redirect()->route('dashboard', ['project_id' => $project->id, 'view' => 'tablero']);
    }
}
