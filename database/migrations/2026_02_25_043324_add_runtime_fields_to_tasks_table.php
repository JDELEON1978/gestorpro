<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('expediente_id')
                ->nullable()
                ->after('project_id')
                ->constrained('expedientes')
                ->nullOnDelete();

            $table->foreignId('nodo_id')
                ->nullable()
                ->after('expediente_id')
                ->constrained('nodos')
                ->nullOnDelete();

            $table->index(['expediente_id','nodo_id'], 'tasks_exp_nodo_idx');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('tasks_exp_nodo_idx');
            $table->dropForeign(['nodo_id']);
            $table->dropForeign(['expediente_id']);
            $table->dropColumn(['nodo_id','expediente_id']);
        });
    }
};