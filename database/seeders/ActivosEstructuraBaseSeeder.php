<?php

namespace Database\Seeders;

use App\Models\CentralGeneracion;
use App\Models\UbicacionActivo;
use App\Models\Workspace;
use Illuminate\Database\Seeder;

class ActivosEstructuraBaseSeeder extends Seeder
{
    public function run(): void
    {
        Workspace::query()->each(function (Workspace $workspace) {
            $central = CentralGeneracion::query()->firstOrCreate(
                [
                    'workspace_id' => $workspace->id,
                    'codigo' => 'CENTRAL-BASE',
                ],
                [
                    'nombre' => 'Central Base',
                    'tipo_central' => 'HIDROELECTRICA',
                    'capacidad_mw' => null,
                    'empresa_operadora' => null,
                    'ubicacion_referencia' => null,
                    'descripcion' => 'Central inicial creada por el seeder de activos.',
                    'activo' => true,
                ]
            );

            UbicacionActivo::query()->firstOrCreate(
                [
                    'workspace_id' => $workspace->id,
                    'central_id' => $central->id,
                    'codigo' => 'GENERAL',
                ],
                [
                    'parent_id' => null,
                    'nombre' => 'Área General',
                    'tipo_ubicacion' => 'AREA',
                    'descripcion' => 'Ubicación inicial para registrar activos.',
                    'activo' => true,
                ]
            );
        });
    }
}
