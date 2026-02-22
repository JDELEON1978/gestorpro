<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::table('nodo_items', function (Blueprint $table) {
        $table->foreign('nodo_id')->references('id')->on('nodos')->onDelete('cascade');
        $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
    });
}

public function down(): void
{
    Schema::table('nodo_items', function (Blueprint $table) {
        $table->dropForeign(['nodo_id']);
        $table->dropForeign(['item_id']);
    });
}
};
