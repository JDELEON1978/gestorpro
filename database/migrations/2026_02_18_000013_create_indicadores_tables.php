<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('indicadores', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 80)->unique();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->text('formula')->nullable();
            $table->string('frecuencia', 20)->nullable(); // diaria|semanal|mensual
            $table->timestamps();
        });

        Schema::create('indicador_valores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indicador_id')->constrained('indicadores')->cascadeOnDelete();
            $table->foreignId('expediente_id')->nullable()->constrained('expedientes')->nullOnDelete();
            $table->date('fecha');
            $table->string('valor', 255)->nullable(); // flexible: num o texto
            $table->timestamps();

            $table->index(['indicador_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indicador_valores');
        Schema::dropIfExists('indicadores');
    }
};
