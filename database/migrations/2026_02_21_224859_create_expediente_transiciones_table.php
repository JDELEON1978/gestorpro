<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('expediente_transiciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expediente_id')->constrained('expedientes')->cascadeOnDelete();
            $table->foreignId('from_nodo_id')->nullable()->constrained('nodos')->nullOnDelete();
            $table->foreignId('to_nodo_id')->constrained('nodos')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('motivo', 255)->nullable();
            $table->timestamps();

            $table->index(['expediente_id', 'created_at'], 'exp_trans_exp_fecha_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expediente_transiciones');
    }
};