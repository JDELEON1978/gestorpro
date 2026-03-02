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
            Schema::table('project_statuses', function (Blueprint $table) {
                $table->enum('estado', [
                    'INICIADO',
                    'EN PROCESO',
                    'APROBADO',
                    'RECHAZADO'
                ])
                ->default('INICIADO')
                ->after('is_default');
            });

            // Forzar valor por defecto en registros existentes
            DB::table('project_statuses')
                ->whereNull('estado')
                ->update(['estado' => 'INICIADO']);
        }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_statuses', function (Blueprint $table) {
            //
        });
    }
};
