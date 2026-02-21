<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('entidad', 80); // ej: expediente_items
            $table->unsignedBigInteger('entidad_id')->nullable();
            $table->string('accion', 80);  // CREAR|ACTUALIZAR|APROBAR|RECHAZAR|SUBIR_EVIDENCIA
            $table->json('antes_json')->nullable();
            $table->json('despues_json')->nullable();
            $table->string('ip', 64)->nullable();
            $table->timestamps();

            $table->index(['entidad', 'entidad_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
