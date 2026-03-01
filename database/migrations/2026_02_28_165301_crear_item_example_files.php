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
        Schema::create('item_example_files', function (Blueprint $table) {
        $table->id();
        $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
        $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
        $table->string('original_name');
        $table->string('path');
        $table->string('mime', 120)->nullable();
        $table->unsignedBigInteger('size_bytes')->nullable();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_example_files', function (Blueprint $table) {
            //
        });
    }
};
