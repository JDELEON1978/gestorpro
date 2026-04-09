<?php

namespace Database\Seeders;

use App\Models\CategoriaActivo;
use App\Models\Workspace;
use Illuminate\Database\Seeder;

class ActivosCategoriasSeeder extends Seeder
{
    public function run(): void
    {
        $estructura = [
            [
                'codigo' => 'GENERACION',
                'nombre' => 'Sistemas de generación',
                'clase_activo' => 'SISTEMA',
                'children' => [
                    ['codigo' => 'TURBINA', 'nombre' => 'Turbinas', 'clase_activo' => 'EQUIPO', 'requiere_serie' => true, 'vida_util_anios' => 30],
                    ['codigo' => 'GENERADOR', 'nombre' => 'Generadores', 'clase_activo' => 'EQUIPO', 'requiere_serie' => true, 'vida_util_anios' => 25],
                    ['codigo' => 'CALDERA', 'nombre' => 'Calderas', 'clase_activo' => 'EQUIPO', 'requiere_serie' => true, 'vida_util_anios' => 20],
                    ['codigo' => 'PANEL-SOLAR', 'nombre' => 'Paneles solares', 'clase_activo' => 'EQUIPO', 'requiere_serie' => true, 'vida_util_anios' => 20],
                    ['codigo' => 'AEROGENERADOR', 'nombre' => 'Aerogeneradores', 'clase_activo' => 'EQUIPO', 'requiere_serie' => true, 'vida_util_anios' => 25],
                ],
            ],
            [
                'codigo' => 'ELECTRICO',
                'nombre' => 'Sistema eléctrico de potencia',
                'clase_activo' => 'SISTEMA',
                'children' => [
                    ['codigo' => 'TRANSFORMADOR', 'nombre' => 'Transformadores', 'clase_activo' => 'EQUIPO', 'requiere_serie' => true, 'vida_util_anios' => 25],
                    ['codigo' => 'INTERRUPTOR', 'nombre' => 'Interruptores de potencia', 'clase_activo' => 'EQUIPO', 'requiere_serie' => true, 'vida_util_anios' => 20],
                    ['codigo' => 'CELDA-MT', 'nombre' => 'Celdas de media tensión', 'clase_activo' => 'EQUIPO', 'requiere_serie' => true, 'vida_util_anios' => 20],
                    ['codigo' => 'BARRA', 'nombre' => 'Barras y conexiones', 'clase_activo' => 'COMPONENTE', 'vida_util_anios' => 20],
                ],
            ],
            [
                'codigo' => 'AUXILIARES',
                'nombre' => 'Servicios auxiliares',
                'clase_activo' => 'SISTEMA',
                'children' => [
                    ['codigo' => 'BOMBA', 'nombre' => 'Bombas', 'clase_activo' => 'EQUIPO', 'requiere_serie' => true, 'vida_util_anios' => 15],
                    ['codigo' => 'COMPRESOR', 'nombre' => 'Compresores', 'clase_activo' => 'EQUIPO', 'requiere_serie' => true, 'vida_util_anios' => 15],
                    ['codigo' => 'UPS', 'nombre' => 'UPS y respaldo', 'clase_activo' => 'EQUIPO', 'requiere_serie' => true, 'vida_util_anios' => 10],
                    ['codigo' => 'ENFRIAMIENTO', 'nombre' => 'Enfriamiento', 'clase_activo' => 'SISTEMA', 'vida_util_anios' => 15],
                ],
            ],
            [
                'codigo' => 'CONTROL',
                'nombre' => 'Control e instrumentación',
                'clase_activo' => 'SISTEMA',
                'children' => [
                    ['codigo' => 'PLC', 'nombre' => 'PLC y RTU', 'clase_activo' => 'EQUIPO', 'requiere_serie' => true, 'vida_util_anios' => 12],
                    ['codigo' => 'SENSOR', 'nombre' => 'Sensores y transmisores', 'clase_activo' => 'COMPONENTE', 'vida_util_anios' => 8],
                    ['codigo' => 'MEDIDOR', 'nombre' => 'Medidores eléctricos', 'clase_activo' => 'EQUIPO', 'requiere_serie' => true, 'vida_util_anios' => 10],
                ],
            ],
            [
                'codigo' => 'INFRAESTRUCTURA',
                'nombre' => 'Infraestructura',
                'clase_activo' => 'INFRAESTRUCTURA',
                'children' => [
                    ['codigo' => 'EDIFICIO', 'nombre' => 'Edificios', 'clase_activo' => 'INFRAESTRUCTURA', 'vida_util_anios' => 40],
                    ['codigo' => 'LINEA', 'nombre' => 'Líneas y canalizaciones', 'clase_activo' => 'INFRAESTRUCTURA', 'vida_util_anios' => 30],
                    ['codigo' => 'TANQUE', 'nombre' => 'Tanques y depósitos', 'clase_activo' => 'EQUIPO', 'vida_util_anios' => 20],
                ],
            ],
        ];

        Workspace::query()->each(function (Workspace $workspace) use ($estructura) {
            foreach ($estructura as $categoriaPadre) {
                $padre = CategoriaActivo::query()->updateOrCreate(
                    [
                        'workspace_id' => $workspace->id,
                        'codigo' => $categoriaPadre['codigo'],
                    ],
                    [
                        'nombre' => $categoriaPadre['nombre'],
                        'clase_activo' => $categoriaPadre['clase_activo'],
                        'requiere_serie' => false,
                        'vida_util_anios' => null,
                        'descripcion' => null,
                        'activo' => true,
                        'parent_id' => null,
                    ]
                );

                foreach ($categoriaPadre['children'] as $hija) {
                    CategoriaActivo::query()->updateOrCreate(
                        [
                            'workspace_id' => $workspace->id,
                            'codigo' => $hija['codigo'],
                        ],
                        [
                            'parent_id' => $padre->id,
                            'nombre' => $hija['nombre'],
                            'clase_activo' => $hija['clase_activo'],
                            'requiere_serie' => $hija['requiere_serie'] ?? false,
                            'vida_util_anios' => $hija['vida_util_anios'] ?? null,
                            'descripcion' => null,
                            'activo' => true,
                        ]
                    );
                }
            }
        });
    }
}
