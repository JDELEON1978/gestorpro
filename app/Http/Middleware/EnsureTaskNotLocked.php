<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnsureTaskNotLocked
{
    public function handle(Request $request, Closure $next)
    {
        $task = $request->route('task'); // Route-model binding: {task}

        if ($task) {
            $statusId = (int)($task->project_status_id ?: $task->status_id);
            $estado = $statusId ? DB::table('project_statuses')->where('id', $statusId)->value('estado') : null;

            if ($estado === 'RECHAZADO') {
                abort(409, 'La tarea está RECHAZADA y no se puede modificar.');
            }
            if ($estado === 'APROBADO') {
                // Solo permitir si explícitamente viene un status destino RECHAZADO (caso move/update)
                $destStatusId = (int)($request->input('status_id') ?? 0);
                if ($destStatusId > 0) {
                    $destEstado = DB::table('project_statuses')->where('id', $destStatusId)->value('estado');
                    if ($destEstado === 'RECHAZADO') {
                        return $next($request);
                    }
                }
                abort(409, 'La tarea está APROBADA: solo puede pasar a RECHAZADO.');
            }
        }

        return $next($request);
    }
}