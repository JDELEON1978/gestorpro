<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('project_statuses', function (Blueprint $table) {
      $table->id();
      $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
      $table->string('name');                 // To do, Doing, Done
      $table->string('slug');                 // todo, doing, done (estable)
      $table->unsignedInteger('position')->default(0); // orden en kanban
      $table->boolean('is_default')->default(false);
      $table->timestamps();

      $table->unique(['project_id', 'slug']);
    });
  }
  public function down(): void {
    Schema::dropIfExists('project_statuses');
  }
};
