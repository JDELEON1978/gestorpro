<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'SOLICITANTE',
            'ADMINISTRADOR_CONTRATO',
            'SUPERVISOR_CONTRATO',
            'JEFE_DAF',
            'ENCARGADO_FINANCIAMIENTO',
            'DIVISION_FINANCIERA',
            'CONTABILIDAD',
            'CONTROL_FINANCIERO',
            'TESORERIA',
        ];

        foreach ($roles as $rol) {
            Rol::firstOrCreate([
                'nombre' => $rol
            ]);
        }
    }
}