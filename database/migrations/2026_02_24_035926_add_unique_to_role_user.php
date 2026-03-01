<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('role_user', function (Blueprint $table) {
            // Si no existen aún:
             //$table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
             $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->unique(['role_id', 'user_id'], 'role_user_unique');
            $table->index(['user_id'], 'role_user_user_idx');
            $table->index(['role_id'], 'role_user_role_idx');
        });
    }

    public function down(): void
    {
        Schema::table('role_user', function (Blueprint $table) {
            $table->dropUnique('role_user_unique');
            $table->dropIndex('role_user_user_idx');
            $table->dropIndex('role_user_role_idx');
        });
    }
};