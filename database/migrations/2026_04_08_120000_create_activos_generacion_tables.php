<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('centrales_generacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->string('codigo', 50);
            $table->string('nombre');
            $table->string('tipo_central', 30); // HIDROELECTRICA | TERMICA | SOLAR | EOLICA | GEOTERMICA | BIOMASA | SUBESTACION
            $table->decimal('capacidad_mw', 12, 2)->nullable();
            $table->string('empresa_operadora')->nullable();
            $table->string('ubicacion_referencia')->nullable();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['workspace_id', 'codigo'], 'uq_centrales_workspace_codigo');
            $table->index(['workspace_id', 'tipo_central', 'activo'], 'idx_centrales_workspace_tipo_activo');
        });

        Schema::create('categorias_activos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('categorias_activos')->nullOnDelete();
            $table->string('codigo', 50);
            $table->string('nombre');
            $table->string('clase_activo', 30)->default('EQUIPO'); // EQUIPO | COMPONENTE | SISTEMA | HERRAMIENTA | INFRAESTRUCTURA
            $table->boolean('requiere_serie')->default(false);
            $table->unsignedSmallInteger('vida_util_anios')->nullable();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['workspace_id', 'codigo'], 'uq_categorias_workspace_codigo');
            $table->index(['workspace_id', 'clase_activo', 'activo'], 'idx_categorias_workspace_clase_activo');
        });

        Schema::create('ubicaciones_activos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->foreignId('central_id')->constrained('centrales_generacion')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('ubicaciones_activos')->nullOnDelete();
            $table->string('codigo', 50);
            $table->string('nombre');
            $table->string('tipo_ubicacion', 30)->default('AREA'); // PLANTA | AREA | SISTEMA | SUBSISTEMA | EDIFICIO | NIVEL | POSICION
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['central_id', 'codigo'], 'uq_ubicaciones_central_codigo');
            $table->index(['workspace_id', 'central_id', 'tipo_ubicacion'], 'idx_ubicaciones_workspace_central_tipo');
        });

        Schema::create('activos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->foreignId('central_id')->constrained('centrales_generacion')->cascadeOnDelete();
            $table->foreignId('ubicacion_id')->nullable()->constrained('ubicaciones_activos')->nullOnDelete();
            $table->foreignId('categoria_id')->constrained('categorias_activos')->restrictOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('activos')->nullOnDelete();
            $table->foreignId('responsable_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('codigo', 60);
            $table->string('nombre');
            $table->string('tag', 80)->nullable();
            $table->string('estado_operativo', 30)->default('OPERATIVO'); // OPERATIVO | RESERVA | MANTENIMIENTO | FALLA | RETIRADO
            $table->string('criticidad', 15)->default('MEDIA'); // BAJA | MEDIA | ALTA | CRITICA
            $table->string('fabricante')->nullable();
            $table->string('modelo')->nullable();
            $table->string('numero_serie')->nullable();
            $table->string('tipo_combustible', 30)->nullable();
            $table->date('fecha_fabricacion')->nullable();
            $table->date('fecha_instalacion')->nullable();
            $table->date('fecha_puesta_servicio')->nullable();
            $table->decimal('potencia_nominal_kw', 14, 2)->nullable();
            $table->decimal('voltaje_nominal_v', 14, 2)->nullable();
            $table->decimal('corriente_nominal_a', 14, 2)->nullable();
            $table->decimal('horas_operacion', 14, 2)->default(0);
            $table->decimal('costo_adquisicion', 14, 2)->nullable();
            $table->decimal('valor_libros', 14, 2)->nullable();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['workspace_id', 'codigo'], 'uq_activos_workspace_codigo');
            $table->unique(['workspace_id', 'tag'], 'uq_activos_workspace_tag');
            $table->index(['central_id', 'estado_operativo', 'criticidad'], 'idx_activos_central_estado_criticidad');
            $table->index(['categoria_id', 'activo'], 'idx_activos_categoria_activo');
        });

        Schema::create('activo_eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activo_id')->constrained('activos')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipo_evento', 30); // INSPECCION | MANTENIMIENTO | LECTURA | FALLA | PARO | REPARACION | CALIBRACION
            $table->dateTime('fecha_evento');
            $table->string('resultado', 30)->nullable(); // OK | ALERTA | FALLA | PENDIENTE
            $table->decimal('horas_operacion', 14, 2)->nullable();
            $table->decimal('valor_medicion', 14, 4)->nullable();
            $table->string('unidad_medicion', 20)->nullable();
            $table->decimal('costo', 14, 2)->nullable();
            $table->date('proximo_evento_programado')->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamps();

            $table->index(['activo_id', 'tipo_evento', 'fecha_evento'], 'idx_eventos_activo_tipo_fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activo_eventos');
        Schema::dropIfExists('activos');
        Schema::dropIfExists('ubicaciones_activos');
        Schema::dropIfExists('categorias_activos');
        Schema::dropIfExists('centrales_generacion');
    }
};
