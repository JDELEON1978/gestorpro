<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BackfillProjectStatusColorsSeeder extends Seeder
{
    /**
     * Rellena colores default si están NULL.
     * No pisa colores que el usuario ya haya configurado.
     */
    public function run(): void
    {
        $defaults = [
            'todo'        => '#6B7280', // gris
            'doing'       => '#2563EB', // azul
            'done'        => '#16A34A', // verde
            'supervision' => '#F97316', // naranja
            'revision'    => '#A855F7', // morado
        ];

        $rows = DB::table('project_statuses')->get(['id', 'slug', 'color']);

        foreach ($rows as $r) {
            if (!empty($r->color)) {
                continue;
            }

            $slug = strtolower((string)$r->slug);
            $color = $defaults[$slug] ?? '#005F87'; // INDE blue como fallback

            DB::table('project_statuses')
                ->where('id', $r->id)
                ->update(['color' => $color]);
        }
    }
}
