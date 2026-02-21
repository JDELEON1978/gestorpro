<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('catalogos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('catalogo_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalogo_id')->constrained('catalogos')->cascadeOnDelete();
            $table->string('codigo', 80)->nullable();
            $table->string('valor');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['catalogo_id', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogo_items');
        Schema::dropIfExists('catalogos');
    }
};
