<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nodo_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nodo_id')->constrained('nodos')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->boolean('obligatorio')->default(true);
            $table->timestamps();

            $table->unique(['nodo_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nodo_items');
    }
};
