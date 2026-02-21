<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tipos_item', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('categoria', 20); // DOCUMENTO|FORMULARIO|OPERACION
            $table->timestamps();

            $table->index(['categoria', 'nombre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_item');
    }
};
