<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nodos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proceso_id')->constrained('procesos')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('tipo_nodo', 30)->default('actividad'); // inicio|actividad|decision|fin|conector
            $table->unsignedInteger('orden')->default(0);
            $table->unsignedInteger('sla_horas')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['proceso_id', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nodos');
    }
};
