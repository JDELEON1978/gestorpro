<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('expediente_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expediente_id')->constrained('expedientes')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('nodo_id')->nullable()->constrained('nodos')->nullOnDelete();

            $table->string('estado', 20)->default('pendiente'); // pendiente|entregado|revisado|rechazado|aprobado
            $table->dateTime('entregado_en')->nullable();
            $table->dateTime('revisado_en')->nullable();

            $table->foreignId('recibido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('revisado_por')->nullable()->constrained('users')->nullOnDelete();

            $table->boolean('aprobado')->default(false);
            $table->foreignId('rechazado_regresar_a_nodo_id')->nullable()->constrained('nodos')->nullOnDelete();
            $table->text('observaciones')->nullable();

            $table->timestamps();

            $table->index(['expediente_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expediente_items');
    }
};
