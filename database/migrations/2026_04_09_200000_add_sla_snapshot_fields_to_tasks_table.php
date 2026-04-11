<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('tasks')) {
            return;
        }

        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'sla_hours')) {
                $table->unsignedInteger('sla_hours')->nullable()->after('position');
            }

            if (!Schema::hasColumn('tasks', 'sla_started_at')) {
                $table->dateTime('sla_started_at')->nullable()->after('sla_hours');
            }

            if (!Schema::hasColumn('tasks', 'sla_due_at')) {
                $table->dateTime('sla_due_at')->nullable()->after('sla_started_at');
            }
        });

        DB::statement("
            UPDATE tasks t
            INNER JOIN nodos n ON n.id = t.nodo_id
            SET
              t.sla_hours = CASE
                WHEN t.sla_hours IS NULL OR t.sla_hours = 0 THEN NULLIF(n.sla_horas, 0)
                ELSE t.sla_hours
              END,
              t.sla_started_at = COALESCE(t.sla_started_at, t.created_at),
              t.sla_due_at = COALESCE(t.sla_due_at, t.due_at)
            WHERE
              t.nodo_id IS NOT NULL
              AND NULLIF(n.sla_horas, 0) IS NOT NULL
        ");
    }

    public function down(): void
    {
        if (!Schema::hasTable('tasks')) {
            return;
        }

        Schema::table('tasks', function (Blueprint $table) {
            foreach (['sla_due_at', 'sla_started_at', 'sla_hours'] as $column) {
                if (Schema::hasColumn('tasks', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
