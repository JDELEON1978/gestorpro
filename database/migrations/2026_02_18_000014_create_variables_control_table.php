<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('variables_control', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 120)->unique();
            $table->text('valor')->nullable();
            $table->string('tipo', 20)->default('string'); // string|int|bool|json
            $table->string('scope', 20)->default('GLOBAL'); // GLOBAL|PROCESO
            $table->foreignId('proceso_id')->nullable()->constrained('procesos')->nullOnDelete();
            $table->timestamps();

            $table->index(['scope', 'proceso_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variables_control');
    }
};
