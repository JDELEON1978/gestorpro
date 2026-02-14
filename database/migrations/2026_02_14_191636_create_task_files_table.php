<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('task_files', function (Blueprint $table) {
      $table->id();
      $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
      $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

      $table->string('original_name');
      $table->string('path');      // storage path
      $table->string('mime', 120)->nullable();
      $table->unsignedBigInteger('size_bytes')->nullable();

      $table->timestamps();
    });
  }
  public function down(): void {
    Schema::dropIfExists('task_files');
  }
};

