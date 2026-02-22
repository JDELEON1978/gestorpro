<?php

// database/migrations/2026_02_21_000002_add_ports_to_nodo_relaciones.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('nodo_relaciones', function (Blueprint $table) {
      $table->string('out_side', 10)->nullable()->after('prioridad');
      $table->unsignedSmallInteger('out_offset')->nullable()->after('out_side');
    });
  }

  public function down(): void {
    Schema::table('nodo_relaciones', function (Blueprint $table) {
      $table->dropColumn(['out_side','out_offset']);
    });
  }
};