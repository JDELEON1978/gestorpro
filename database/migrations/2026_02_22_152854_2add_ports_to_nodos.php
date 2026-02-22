<?php

// database/migrations/2026_02_21_000001_add_ports_to_nodos.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('nodos', function (Blueprint $table) {
      $table->string('in_side', 10)->nullable()->after('pos_y');   // left|right|top|bottom
      $table->unsignedSmallInteger('in_offset')->nullable()->after('in_side');

      $table->string('out_side', 10)->nullable()->after('in_offset');
      $table->unsignedSmallInteger('out_offset')->nullable()->after('out_side');
    });
  }

  public function down(): void {
    Schema::table('nodos', function (Blueprint $table) {
      $table->dropColumn(['in_side','in_offset','out_side','out_offset']);
    });
  }
};
