<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('task_file_reviews', function (Blueprint $table) {
        $table->id();
        $table->foreignId('task_file_id')->constrained('task_files')->onDelete('cascade');
        $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
        $table->string('status', 20)->default('EN_REVISION');
        $table->text('summary')->nullable();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
