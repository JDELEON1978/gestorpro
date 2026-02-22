<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('nodos', function (Blueprint $table) {
      $table->unsignedBigInteger('responsable_rol_id')->nullable()->after('tipo_nodo');
      $table->text('descripcion')->nullable()->after('responsable_rol_id');

      // Ajusta el nombre de tabla/PK si tu tabla roles se llama diferente
      $table->foreign('responsable_rol_id')
            ->references('id')->on('roles')
            ->nullOnDelete();
    });
  }

  public function down(): void
  {
    Schema::table('nodos', function (Blueprint $table) {
      $table->dropForeign(['responsable_rol_id']);
      $table->dropColumn(['responsable_rol_id', 'descripcion']);
    });
  }
};
