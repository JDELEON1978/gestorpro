<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('expedientes', function (Blueprint $table) {
            if (!Schema::hasColumn('expedientes', 'nodo_actual_id')) {
                $table->foreignId('nodo_actual_id')
                    ->nullable()
                    ->after('proceso_id')
                    ->constrained('nodos')
                    ->nullOnDelete();

                $table->index(['proceso_id', 'nodo_actual_id'], 'expedientes_proc_nodo_actual_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('expedientes', function (Blueprint $table) {
            if (Schema::hasColumn('expedientes', 'nodo_actual_id')) {
                $table->dropIndex('expedientes_proc_nodo_actual_idx');
                $table->dropConstrainedForeignId('nodo_actual_id');
            }
        });
    }
};