<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

/**
 * TaskPolicy: seguridad multi-tenant.
 *
 * Regla:
 * - Solo miembros del Workspace del proyecto pueden operar tareas.
 */
class TaskPolicy
{
    /**
     * Listar tareas dentro de un proyecto.
     */
    public function viewAny(User $user, Project $project): bool
    {
        if (!$project->workspace) {
            return false;
        }

        return $project->workspace->members()
            ->where('users.id', $user->id)
            ->exists();
    }

    /**
     * Crear tareas en un proyecto.
     */
    public function create(User $user, Project $project): bool
    {
        return $this->viewAny($user, $project);
    }

    /**
     * Ver una tarea específica.
     */
    public function view(User $user, Task $task): bool
    {
        if (!$task->project || !$task->project->workspace) {
            return false;
        }

        return $task->project->workspace->members()
            ->where('users.id', $user->id)
            ->exists();
    }

    /**
     * Mover/reordenar una tarea también es "update".
     */
    public function update(User $user, Task $task): bool
    {
        return $this->view($user, $task);
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->view($user, $task);
    }
}
