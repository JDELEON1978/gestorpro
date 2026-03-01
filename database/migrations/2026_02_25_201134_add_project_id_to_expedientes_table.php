<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('expedientes', function (Blueprint $table) {
        $table->unsignedBigInteger('project_id')->nullable()->after('proceso_id');
        $table->index(['project_id']);
        $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
    });
}

public function down(): void
{
    Schema::table('expedientes', function (Blueprint $table) {
        $table->dropForeign(['project_id']);
        $table->dropIndex(['project_id']);
        $table->dropColumn('project_id');
    });
}
};
