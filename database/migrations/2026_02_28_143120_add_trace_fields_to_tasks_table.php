<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_task_id')->nullable()->index()->after('id');
            $table->unsignedBigInteger('from_nodo_id')->nullable()->index()->after('nodo_id');

            // OPCIONAL (auditoría más exacta):
             $table->unsignedBigInteger('from_nodo_relacion_id')->nullable()->index()->after('from_nodo_id');

             //Opcional: FK (si ya tienes integridad bien armada)
             $table->foreign('parent_task_id')->references('id')->on('tasks')->nullOnDelete();
             $table->foreign('from_nodo_id')->references('id')->on('nodos')->nullOnDelete();
             $table->foreign('from_nodo_relacion_id')->references('id')->on('nodo_relaciones')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Si creaste FKs, primero dropearlas.
             $table->dropForeign(['parent_task_id']);
             $table->dropForeign(['from_nodo_id']);
             $table->dropForeign(['from_nodo_relacion_id']);

             $table->dropColumn(['parent_task_id','from_nodo_id','from_nodo_relacion_id']);
            $table->dropColumn(['parent_task_id','from_nodo_id']);
        });
    }
};