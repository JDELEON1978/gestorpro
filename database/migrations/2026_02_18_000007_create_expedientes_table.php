<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('expedientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proceso_id')->constrained('procesos')->cascadeOnDelete();
            $table->string('correlativo', 60);
            $table->string('titulo');
            $table->string('estado', 20)->default('abierto'); // abierto|en_proceso|cerrado|anulado
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['proceso_id', 'correlativo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expedientes');
    }
};
