<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function index(Workspace $workspace)
    {
        // MVP: listar proyectos del workspace
        $projects = $workspace->projects()->orderBy('name')->get();
        return view('projects.index', compact('workspace', 'projects'));
    }

    public function create(Workspace $workspace)
    {
        return view('projects.create', compact('workspace'));
    }

    public function store(Request $request, Workspace $workspace)
    {
        // MVP: sin políticas todavía, asumimos que el user ya es miembro.
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data, $workspace) {
            $slugBase = Str::slug($data['name']);
            $slug = $slugBase ?: 'project';

            $i = 2;
            while ($workspace->projects()->where('slug', $slug)->exists()) {
                $slug = ($slugBase ?: 'project') . '-' . $i;
                $i++;
            }

            Project::create([
                'workspace_id' => $workspace->id,
                'name' => $data['name'],
                'slug' => $slug,
                'description' => $data['description'] ?? null,
                'archived' => 0,
            ]);
        });

        // ✅ redirección pedida
        return redirect()->route('dashboard')->with('success', 'Proyecto creado');
    }
}
