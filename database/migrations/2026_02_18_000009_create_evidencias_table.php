<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('evidencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expediente_item_id')->constrained('expediente_items')->cascadeOnDelete();
            $table->string('archivo_path');
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('tamano_bytes')->nullable();
            $table->string('hash_sha256', 64)->nullable();
            $table->foreignId('subido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['expediente_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidencias');
    }
};
