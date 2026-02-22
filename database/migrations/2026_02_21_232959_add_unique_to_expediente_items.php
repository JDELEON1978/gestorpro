<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('expediente_items', function (Blueprint $table) {
            // Evita filas duplicadas del mismo item para el mismo nodo y expediente
            $table->unique(['expediente_id','nodo_id','item_id'], 'exp_item_unique');
        });
    }

    public function down(): void
    {
        Schema::table('expediente_items', function (Blueprint $table) {
            $table->dropUnique('exp_item_unique');
        });
    }
};