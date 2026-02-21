<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proceso_id')->constrained('procesos')->cascadeOnDelete();
            $table->string('nombre'); // ej: DOC02636 - Orden de Compra
            $table->string('categoria', 20); // DOCUMENTO|FORMULARIO|OPERACION
            $table->foreignId('tipo_id')->nullable()->constrained('tipos_item')->nullOnDelete();
            $table->boolean('requiere_evidencia')->default(true);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['proceso_id', 'categoria']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
