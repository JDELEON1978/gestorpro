<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('activos', function (Blueprint $table) {
            $table->string('proveedor_instalador')->nullable()->after('tipo_combustible');
        });

        Schema::create('activo_contactos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activo_id')->constrained('activos')->cascadeOnDelete();
            $table->string('tipo_contacto', 30)->default('GENERAL');
            $table->string('nombre');
            $table->string('cargo')->nullable();
            $table->string('empresa')->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('email')->nullable();
            $table->text('notas')->nullable();
            $table->boolean('principal')->default(false);
            $table->timestamps();

            $table->index(['activo_id', 'tipo_contacto'], 'idx_activo_contactos_tipo');
        });

        Schema::create('activo_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activo_id')->constrained('activos')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipo_documento', 30); // IMAGEN | ESQUEMA | MAPA_VARIABLES | DATASHEET | INSTRUCCIONES | OTRO
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime', 120)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamps();

            $table->index(['activo_id', 'tipo_documento'], 'idx_activo_documentos_tipo');
        });

        Schema::create('activo_evento_evidencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activo_evento_id')->constrained('activo_eventos')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime', 120)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activo_evento_evidencias');
        Schema::dropIfExists('activo_documentos');
        Schema::dropIfExists('activo_contactos');

        Schema::table('activos', function (Blueprint $table) {
            $table->dropColumn('proveedor_instalador');
        });
    }
};
