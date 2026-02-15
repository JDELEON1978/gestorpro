<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkspaceController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $workspaces = $user->workspaces()->orderBy('name')->get();

        return view('workspaces.index', compact('workspaces'));
    }

    public function create()
    {
        return view('workspaces.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);
        

        DB::transaction(function () use ($data) {
            $workspace = Workspace::create([
                'name' => $data['name'],
                'owner_user_id' => auth()->id(),
            ]);

            // pivote real: user_workspace
            $workspace->users()->syncWithoutDetaching([
                auth()->id() => ['role' => 'owner'],
            ]);
        });

        // ✅ redirección pedida
        return redirect()
        ->route('dashboard')
        ->with('success', 'Workspace creado');
    }
}
