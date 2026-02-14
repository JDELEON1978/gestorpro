<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('task_activities', function (Blueprint $table) {
      $table->id();
      $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
      $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

      $table->string('event'); // created, moved, commented, file_added
      $table->json('meta')->nullable();

      $table->timestamps();
    });
  }
  public function down(): void {
    Schema::dropIfExists('task_activities');
  }
};
