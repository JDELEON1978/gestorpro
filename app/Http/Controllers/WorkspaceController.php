<?php
namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\Request;

class WorkspaceController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:120'],
        ]);

        $user = $request->user();

        $workspace = Workspace::create([
            'name' => $data['name'],
            'owner_user_id' => $user->id,
        ]);

        // El dueño también es miembro con rol owner
        $workspace->users()->attach($user->id, ['role' => 'owner']);

        // (Etapa 2) podríamos guardar workspace actual en sesión
        // session(['current_workspace_id' => $workspace->id]);

        return redirect()->route('dashboard')->with('success', 'Workspace creado.');
    }
}
