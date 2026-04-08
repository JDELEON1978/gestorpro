<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TaskAuditController extends Controller
{
    public function show(Task $task, Request $request)
    {
        if (!$request->expectsJson() && !$request->ajax()) {
            return response()->json(['ok' => false, 'message' => 'Bad request'], 400);
        }

        $rootTask = $this->findRootTask($task);

        $rows = DB::select("
            WITH RECURSIVE chain AS (
                SELECT
                    t.id,
                    t.parent_task_id,
                    t.project_id,
                    t.title,
                    t.description,
                    t.status_id,
                    t.priority,
                    t.start_at,
                    t.due_at,
                    t.nodo_id,
                    t.created_at,
                    t.updated_at,
                    0 AS depth
                FROM tasks t
                WHERE t.id = ?

                UNION ALL

                SELECT
                    c2.id,
                    c2.parent_task_id,
                    c2.project_id,
                    c2.title,
                    c2.description,
                    c2.status_id,
                    c2.priority,
                    c2.start_at,
                    c2.due_at,
                    c2.nodo_id,
                    c2.created_at,
                    c2.updated_at,
                    chain.depth + 1 AS depth
                FROM tasks c2
                INNER JOIN chain ON c2.parent_task_id = chain.id
                WHERE chain.depth < 300
            )
            SELECT
                chain.*,
                ps.name AS status_name
            FROM chain
            LEFT JOIN project_statuses ps ON ps.id = chain.status_id
            ORDER BY depth ASC, id ASC
        ", [$rootTask->id]);

        $taskIds = collect($rows)->pluck('id')->map(fn($v) => (int)$v)->all();

        $activitiesByTask = TaskActivity::query()
            ->with(['user:id,name'])
            ->whereIn('task_id', $taskIds)
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy('task_id');

        $report = $this->buildAuditReport($rows, $activitiesByTask);

        return response()->json([
            'ok' => true,
            'task' => [
                'id'    => (int)$task->id,
                'title' => (string)$task->title,
            ],
            'root_task' => [
                'id'    => (int)$rootTask->id,
                'title' => (string)$rootTask->title,
            ],
            'report' => $report,
        ]);
    }

    private function findRootTask(Task $task): Task
    {
        $current = $task;

        while ($current->parent_task_id) {
            $parent = Task::find($current->parent_task_id);
            if (!$parent) {
                break;
            }
            $current = $parent;
        }

        return $current;
    }

    private function buildAuditReport(array $rows, $activitiesByTask): string
    {
        $lines = [];

        $lines[] = "REPORTE DE REVISIÓN DE AUDITORÍA";
        $lines[] = "";
        $lines[] = "1. OBJETO DE LA REVISIÓN";
        $lines[] = "Se presenta el detalle cronológico del proceso, desde la tarea inicial hasta la última tarea relacionada, incluyendo los movimientos y actividades registradas en el sistema.";
        $lines[] = "";

        foreach ($rows as $index => $r) {
            $n = $index + 1;

            $lines[] = "TAREA:  " . ($r->title ?: '—') ;
            $lines[] = "   1) ID de tarea: {$r->id}";
            //$lines[] = "   2) Título: " . ($r->title ?: '—');
            $lines[] = "   2) Estado actual: " . ($r->status_name ?: '—');
            $lines[] = "   3) Prioridad: " . ($r->priority ?: '—');
            $lines[] = "   4) Fecha de creación: " . $this->fmt($r->created_at);
            $lines[] = "   5) Fecha de inicio: " . $this->fmt($r->start_at);
            $lines[] = "   6) Fecha límite: " . $this->fmt($r->due_at);
            $lines[] = "   7) Última actualización: " . $this->fmt($r->updated_at);

            if ($r->parent_task_id) {
                $lines[] = "   8) Tarea predecesora: {$r->parent_task_id}";
            } else {
                $lines[] = "   8) Tarea predecesora: No aplica (tarea inicial del proceso)";
            }

            $acts = $activitiesByTask->get((int)$r->id, collect());

            if ($acts->isEmpty()) {
                $lines[] = "   9) Actividades registradas: No se encontraron actividades en bitácora.";
                $lines[] = "";
                continue;
            }

            $lines[] = "   9) Actividades registradas:";
            foreach ($acts->values() as $i => $a) {
                $detail = $this->describeActivity($a);
                $num = $i + 1;
                $lines[] = "       9.{$num}) {$detail}";
            }

            $lines[] = "";
        }

        $lines[] = "CONCLUSIÓN";
        $lines[] = "Con base en la trazabilidad disponible en el sistema, el proceso anterior refleja la secuencia de tareas y actividades registradas desde su inicio hasta su estado final observado al momento de la consulta.";

        return implode("\n", $lines);
    }

    private function describeActivity($a): string
    {
        $user = $a->user?->name ?: 'Sistema';
        $date = $this->fmt($a->created_at);
        $meta = is_array($a->meta) ? $a->meta : [];

        switch ((string)$a->event) {
            case 'created':
                return "El {$date}, {$user} creó la tarea con estado inicial " .
                    ($meta['status_id'] ?? '—') .
                    " y posición " . ($meta['position'] ?? '—') . ".";

            case 'updated':
                return "El {$date}, {$user} actualizó la tarea. Cambios registrados: " .
                    $this->describeChanges($meta['changes'] ?? []);

            case 'moved':
                return "El {$date}, {$user} movió la tarea desde el estado " .
                    ($meta['from_status_id'] ?? '—') .
                    " hacia el estado " . ($meta['to_status_id'] ?? '—') . ".";

            case 'advanced':
                return "El {$date}, {$user} avanzó la tarea dentro del flujo, desde el nodo " .
                    ($meta['from_nodo_id'] ?? '—') .
                    " hacia el nodo " . ($meta['to_nodo_id'] ?? '—') .
                    ", generando la siguiente tarea " . ($meta['next_task_id'] ?? '—') . ".";

            case 'created_by_workflow':
                return "El {$date}, {$user} generó automáticamente esta tarea como parte del flujo, proveniente de la tarea " .
                    ($meta['source_task_id'] ?? '—') . ".";

            case 'file_uploaded':
                return "El {$date}, {$user} adjuntó un archivo general a la tarea: " .
                    ($meta['original_name'] ?? '—') . ".";

            case 'file_deleted':
                return "El {$date}, {$user} eliminó un archivo general de la tarea: " .
                    ($meta['original_name'] ?? '—') . ".";

            case 'evidence_uploaded':
                return "El {$date}, {$user} cargó una evidencia asociada al ítem/nodo " .
                    ($meta['nodo_item_id'] ?? '—') .
                    ", archivo: " . ($meta['original_name'] ?? '—') . ".";

            case 'evidence_replaced':
                return "El {$date}, {$user} reemplazó la evidencia asociada al ítem/nodo " .
                    ($meta['nodo_item_id'] ?? '—') .
                    ", archivo: " . ($meta['original_name'] ?? '—') . ".";

            case 'evidence_status_changed':
                return "El {$date}, {$user} cambió el estado de la evidencia del ítem " .
                    ($meta['item_id'] ?? '—') .
                    " de " . ($meta['from'] ?? '—') .
                    " a " . ($meta['to'] ?? '—') . ".";

            default:
                return "El {$date}, {$user} registró el evento '{$a->event}'." .
                    (!empty($meta) ? " Detalle: " . json_encode($meta, JSON_UNESCAPED_UNICODE) : "");
        }
    }

    private function describeChanges(array $changes): string
    {
        if (empty($changes)) {
            return 'sin detalle específico.';
        }

        $parts = [];
        foreach ($changes as $field => $change) {
            $from = is_array($change) ? ($change['from'] ?? '—') : '—';
            $to   = is_array($change) ? ($change['to'] ?? '—') : '—';
            $parts[] = "{$field}: '{$from}' a '{$to}'";
        }

        return implode('; ', $parts) . '.';
    }

    private function fmt($value): string
    {
        if (!$value) return '—';

        try {
            return Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return (string)$value;
        }
    }
}