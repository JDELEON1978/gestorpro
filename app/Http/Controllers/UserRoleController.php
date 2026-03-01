<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Rol;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{
    public function index(Request $request)
    {
        // Búsqueda opcional por nombre/email
        $q = trim((string) $request->get('q', ''));

        $users = User::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
            })
            ->with('roles:id,nombre')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $roles = Rol::query()
            ->select('id', 'nombre')
            ->orderBy('nombre')
            ->get();

        return view('admin.user_roles.index', compact('users', 'roles', 'q'));
    }

    public function update(Request $request, User $user)
    {
        // roles[] puede venir vacío => dejar sin roles
        $roleIds = $request->input('roles', []);

        if (!is_array($roleIds)) {
            $roleIds = [];
        }

        // Validación: solo IDs existentes en roles
        $validatedRoleIds = Rol::query()
            ->whereIn('id', $roleIds)
            ->pluck('id')
            ->all();

        // Sync en pivot role_user
        $user->roles()->sync($validatedRoleIds);

        return redirect()
            ->route('admin.user_roles.index', $request->only('q', 'page'))
            ->with('status', "Roles actualizados para {$user->name}.");
    }
}