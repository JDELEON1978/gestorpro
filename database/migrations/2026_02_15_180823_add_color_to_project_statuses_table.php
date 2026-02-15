<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega color HEX a los estados por proyecto.
     * Ejemplo: #005F87
     */
    public function up(): void
    {
        Schema::table('project_statuses', function (Blueprint $table) {
            // Nota: 7 chars => #RRGGBB
            $table->string('color', 7)->nullable()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('project_statuses', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
