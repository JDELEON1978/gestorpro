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
        Schema::table('task_files', function (Blueprint $table) {
            $table->string('status', 20)->default('SUBIDO')->after('size_bytes');
            $table->index(['task_id','item_id','status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_files', function (Blueprint $table) {
            //
        });
    }
};
