<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('productos_servicios', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 15); // PRODUCTO|SERVICIO
            $table->string('nombre');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['tipo', 'activo']);
        });

        Schema::create('expediente_productos_servicios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expediente_id')->constrained('expedientes')->cascadeOnDelete();
            $table->foreignId('producto_servicio_id')->constrained('productos_servicios')->cascadeOnDelete();
            $table->decimal('cantidad', 14, 4)->default(1);
            $table->decimal('precio', 14, 4)->nullable();
            $table->timestamps();

           $table->unique(['expediente_id', 'producto_servicio_id'], 'uq_eps_exp_prod');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expediente_productos_servicios');
        Schema::dropIfExists('productos_servicios');
    }
};
