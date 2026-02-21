<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nodo_relaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proceso_id')->constrained('procesos')->cascadeOnDelete();
            $table->foreignId('nodo_origen_id')->constrained('nodos')->cascadeOnDelete();
            $table->foreignId('nodo_destino_id')->constrained('nodos')->cascadeOnDelete();
            $table->string('condicion', 255)->nullable(); // ej: "OK 100%"
            $table->unsignedInteger('prioridad')->default(0);
            $table->timestamps();

            $table->index(['proceso_id', 'nodo_origen_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nodo_relaciones');
    }
};
