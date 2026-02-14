<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('tasks', function (Blueprint $table) {
      $table->id();
      $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
      $table->foreignId('status_id')->nullable()->constrained('project_statuses')->nullOnDelete();

      $table->string('title');
      $table->text('description')->nullable();

      $table->unsignedTinyInteger('priority')->default(3); // 1 alta, 5 baja (simple)
      $table->date('due_date')->nullable();

      $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();

      // orden dentro de la columna
      $table->unsignedInteger('position')->default(0);

      $table->timestamps();

      $table->index(['project_id', 'status_id']);
    });
  }
  public function down(): void {
    Schema::dropIfExists('tasks');
  }
};
