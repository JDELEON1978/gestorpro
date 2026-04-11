<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('tasks')) {
            return;
        }

        // start_at nació como DATE, pero la lógica del reloj necesita datetime consistente.
        DB::statement("ALTER TABLE tasks MODIFY start_at DATETIME NULL");

        // Fechas planificadas sin hora: inicio 08:00 y límite 17:00.
        DB::statement("
            UPDATE tasks
            SET start_at = TIMESTAMP(DATE(start_at), '08:00:00')
            WHERE start_at IS NOT NULL
              AND TIME(start_at) = '00:00:00'
        ");

        DB::statement("
            UPDATE tasks
            SET due_at = TIMESTAMP(DATE(due_at), '17:00:00')
            WHERE due_at IS NOT NULL
              AND TIME(due_at) = '00:00:00'
        ");

        // Backfill runtime real para tareas ya completadas.
        DB::statement("
            UPDATE tasks t
            LEFT JOIN project_statuses ps ON ps.id = COALESCE(t.project_status_id, t.status_id)
            SET
              t.started_at = COALESCE(t.started_at, t.created_at),
              t.completed_at = COALESCE(t.completed_at, t.updated_at)
            WHERE
              (
                LOWER(COALESCE(ps.slug, '')) = 'done'
                OR LOWER(COALESCE(ps.name, '')) IN ('done', 'finalizado', 'completado')
                OR UPPER(COALESCE(ps.estado, '')) = 'APROBADO'
              )
        ");

        DB::statement("
            UPDATE tasks
            SET started_at = created_at
            WHERE completed_at IS NOT NULL
              AND started_at IS NOT NULL
              AND started_at > completed_at
        ");
    }

    public function down(): void
    {
        // No revertimos automáticamente para no perder precisión temporal.
    }
};
