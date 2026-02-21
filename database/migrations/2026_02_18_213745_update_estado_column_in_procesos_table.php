<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE procesos
            MODIFY estado VARCHAR(30) NOT NULL DEFAULT 'activo'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE procesos
            MODIFY estado INT NOT NULL DEFAULT 1
        ");
    }
};
