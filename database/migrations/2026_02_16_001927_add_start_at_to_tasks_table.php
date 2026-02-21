<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Fecha inicio (nullable). La colocamos cerca de due_at si existe.
            if (!Schema::hasColumn('tasks', 'start_at')) {
                $table->date('start_at')->nullable()->after('priority');
                $table->index('start_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'start_at')) {
                $table->dropIndex(['start_at']);
                $table->dropColumn('start_at');
            }
        });
    }
};
