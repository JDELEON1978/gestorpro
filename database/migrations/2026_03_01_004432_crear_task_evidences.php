<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('task_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('nodo_item_id')->constrained('nodo_items')->cascadeOnDelete();

            $table->enum('estado', ['PENDIENTE','SUBIDO','EN_REVISION','APROBADO','RECHAZADO'])
                    ->default('PENDIENTE');

            $table->string('disk')->default('public');
            $table->string('path')->nullable();          // storage path
            $table->string('original_name')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();

            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['task_id','nodo_item_id']); // 1 archivo por ítem por tarea
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_evidences', function (Blueprint $table) {
            //
        });
    }
};
