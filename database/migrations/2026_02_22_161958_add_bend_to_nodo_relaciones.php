<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('nodo_relaciones', function (Blueprint $table) {
            // Punto de control para manipular la curva manualmente
            $table->integer('bend_x')->nullable()->comment('Control X de la curva (canvas coords)');
            $table->integer('bend_y')->nullable()->comment('Control Y de la curva (canvas coords)');
        });
    }

    public function down(): void
    {
        Schema::table('nodo_relaciones', function (Blueprint $table) {
            $table->dropColumn(['bend_x', 'bend_y']);
        });
    }
};