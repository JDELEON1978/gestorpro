<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;

/**
 * Policy de proyectos (multi-tenant por workspace).
 *
 * Reglas:
 * - Un usuario solo puede operar proyectos si pertenece al workspace.
 */
class ProjectPolicy
{
    public function viewAny(User $user, Workspace $workspace): bool
    {
        return $workspace->members()->where('users.id', $user->id)->exists();
    }

    public function create(User $user, Workspace $workspace): bool
    {
        return $workspace->members()->where('users.id', $user->id)->exists();
    }

    public function view(User $user, Project $project): bool
    {
        return $project->workspace->members()->where('users.id', $user->id)->exists();
    }

    public function update(User $user, Project $project): bool
    {
        return $this->view($user, $project);
    }

    public function delete(User $user, Project $project): bool
    {
        return $this->view($user, $project);
    }
}
