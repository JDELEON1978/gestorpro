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
        Schema::create('task_file_review_comments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('task_file_review_id')->constrained('task_file_reviews')->onDelete('cascade');
        $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
        $table->string('type', 20)->default('COMMENT');
        $table->json('payload')->nullable(); // page, rect coords, text, etc.
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_file_review_comments', function (Blueprint $table) {
            //
        });
    }
};
