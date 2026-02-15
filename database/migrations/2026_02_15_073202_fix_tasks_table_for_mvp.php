<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajuste seguro de la tabla tasks para el MVP.
 *
 * Objetivo:
 * - Usar project_status_id (FK) para workflow.
 * - Soportar asignación, prioridad, fechas, orden kanban, archivado.
 * - Crear índices para consultas típicas en shared hosting.
 *
 * Nota:
 * - Esta migración está pensada para ejecutarse aunque la tabla tasks ya exista.
 * - Si alguna columna ya existe, Laravel podría lanzar error al intentar añadirla.
 *   En ese caso, me pegás el error y la ajustamos con Schema::hasColumn().
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {

            // Relaciones base
            if (!Schema::hasColumn('tasks', 'project_id')) {
                $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            }

            // ✅ Estado por FK (no string): permite columnas configurables mañana sin reescribir
            if (!Schema::hasColumn('tasks', 'project_status_id')) {
                $table->foreignId('project_status_id')->nullable()
                    ->constrained('project_statuses')->nullOnDelete();
            }

            // Contenido
            if (!Schema::hasColumn('tasks', 'title')) {
                $table->string('title', 200);
            }
            if (!Schema::hasColumn('tasks', 'description')) {
                $table->longText('description')->nullable();
            }

            // Prioridad (simple)
            if (!Schema::hasColumn('tasks', 'priority')) {
                $table->unsignedTinyInteger('priority')->default(2); // 1=low,2=med,3=high
            }

            // Responsable y auditoría
            if (!Schema::hasColumn('tasks', 'assigned_to')) {
                $table->foreignId('assigned_to')->nullable()
                    ->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('tasks', 'created_by')) {
                $table->foreignId('created_by')->nullable()
                    ->constrained('users')->nullOnDelete();
            }

            // Fechas
            if (!Schema::hasColumn('tasks', 'due_at')) {
                $table->dateTime('due_at')->nullable();
            }
            if (!Schema::hasColumn('tasks', 'started_at')) {
                $table->dateTime('started_at')->nullable();
            }
            if (!Schema::hasColumn('tasks', 'completed_at')) {
                $table->dateTime('completed_at')->nullable();
            }

            // Orden Kanban estable (por columna)
            if (!Schema::hasColumn('tasks', 'position')) {
                $table->integer('position')->default(0);
            }

            // Archivado “soft” (no borrado)
            if (!Schema::hasColumn('tasks', 'archived_at')) {
                $table->dateTime('archived_at')->nullable();
            }

            // Timestamps
            if (!Schema::hasColumn('tasks', 'created_at')) {
                $table->timestamps();
            }

            // Índices recomendados (performance)
            // Nota: si ya existen, MySQL puede quejarse. Si pasa, me pegás el error y los renombramos.
            $table->index(['project_id', 'project_status_id', 'position'], 'idx_tasks_project_status_pos');
            $table->index(['assigned_to', 'project_status_id'], 'idx_tasks_assigned_status');
            $table->index(['due_at'], 'idx_tasks_due_at');
        });
    }

    public function down(): void
    {
        // En MVP no hacemos rollback destructivo por seguridad.
        // Si querés rollback completo luego, lo diseñamos con nombres exactos de índices/columnas.
    }
};
