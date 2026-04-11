<?php

namespace App\Observers;

use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class TaskObserver
{
    public function created(Task $task): void
    {
        $task->logActivity('created', [
            'status_id'  => (string)($task->status_id ?? ''),
            'position'   => (int)($task->position ?? 0),
            'project_id' => (int)($task->project_id ?? 0),
            'nodo_id'    => $task->nodo_id ? (int)$task->nodo_id : null,
            'sla_hours'  => $task->sla_hours ? (int)$task->sla_hours : null,
            'sla_started_at' => $task->sla_started_at?->toIso8601String(),
            'sla_due_at' => $task->sla_due_at?->toIso8601String(),
        ], Auth::id());
    }

    public function updated(Task $task): void
    {
        // Evitar ruido: si no cambió nada relevante, no loguear.
        $dirty = array_keys($task->getDirty());
        if (empty($dirty)) return;

        // Si el movimiento de columnas ya lo registras en un endpoint "move",
        // NO lo dupliques aquí.
        $onlyMoveLike = collect($dirty)->every(fn($k) => in_array($k, ['status_id','position','updated_at'], true));
        if ($onlyMoveLike) return;

        $changes = [];
        foreach ($dirty as $k) {
            if ($k === 'updated_at') continue;
            $changes[$k] = [
                'from' => $task->getOriginal($k),
                'to'   => $task->{$k},
            ];
        }

        if (empty($changes)) return;

        $task->logActivity('updated', [
            'changes' => $changes,
        ], Auth::id());
    }
}
