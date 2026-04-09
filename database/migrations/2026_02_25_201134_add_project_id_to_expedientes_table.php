<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('expedientes', 'project_id')) {
            Schema::table('expedientes', function (Blueprint $table) {
                $table->unsignedBigInteger('project_id')->nullable()->after('proceso_id');
            });
        }

        $database = DB::getDatabaseName();

        $foreignKeyExists = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', $database)
            ->where('TABLE_NAME', 'expedientes')
            ->where('COLUMN_NAME', 'project_id')
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->exists();

        if (!$foreignKeyExists) {
            Schema::table('expedientes', function (Blueprint $table) {
                $table->foreign('project_id')
                    ->references('id')
                    ->on('projects')
                    ->onDelete('cascade');
            });
        }

        $indexExists = DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', $database)
            ->where('TABLE_NAME', 'expedientes')
            ->where('INDEX_NAME', 'expedientes_project_id_index')
            ->exists();

        if (!$indexExists) {
            Schema::table('expedientes', function (Blueprint $table) {
                $table->index(['project_id']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('expedientes', 'project_id')) {
            $database = DB::getDatabaseName();

            $foreignKeyName = DB::table('information_schema.KEY_COLUMN_USAGE')
                ->where('TABLE_SCHEMA', $database)
                ->where('TABLE_NAME', 'expedientes')
                ->where('COLUMN_NAME', 'project_id')
                ->whereNotNull('REFERENCED_TABLE_NAME')
                ->value('CONSTRAINT_NAME');

            if ($foreignKeyName) {
                Schema::table('expedientes', function (Blueprint $table) use ($foreignKeyName) {
                    $table->dropForeign($foreignKeyName);
                });
            }

            $indexExists = DB::table('information_schema.STATISTICS')
                ->where('TABLE_SCHEMA', $database)
                ->where('TABLE_NAME', 'expedientes')
                ->where('INDEX_NAME', 'expedientes_project_id_index')
                ->exists();

            if ($indexExists) {
                Schema::table('expedientes', function (Blueprint $table) {
                    $table->dropIndex('expedientes_project_id_index');
                });
            }

            Schema::table('expedientes', function (Blueprint $table) {
                $table->dropColumn('project_id');
            });
        }
    }
};