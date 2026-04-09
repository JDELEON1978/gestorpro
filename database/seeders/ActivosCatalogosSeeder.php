<?php

namespace Database\Seeders;

use App\Models\Catalogo;
use Illuminate\Database\Seeder;

class ActivosCatalogosSeeder extends Seeder
{
    public function run(): void
    {
        $catalogos = [
            'tipo_central_generacion' => [
                'descripcion' => 'Tipos de centrales e instalaciones energéticas.',
                'items' => [
                    ['codigo' => 'HIDROELECTRICA', 'valor' => 'Hidroeléctrica'],
                    ['codigo' => 'TERMICA', 'valor' => 'Térmica'],
                    ['codigo' => 'SOLAR', 'valor' => 'Solar'],
                    ['codigo' => 'EOLICA', 'valor' => 'Eólica'],
                    ['codigo' => 'GEOTERMICA', 'valor' => 'Geotérmica'],
                    ['codigo' => 'BIOMASA', 'valor' => 'Biomasa'],
                    ['codigo' => 'SUBESTACION', 'valor' => 'Subestación'],
                ],
            ],
            'tipo_ubicacion_activo' => [
                'descripcion' => 'Tipos de ubicación física o lógica para activos.',
                'items' => [
                    ['codigo' => 'PLANTA', 'valor' => 'Planta'],
                    ['codigo' => 'AREA', 'valor' => 'Área'],
                    ['codigo' => 'SISTEMA', 'valor' => 'Sistema'],
                    ['codigo' => 'SUBSISTEMA', 'valor' => 'Subsistema'],
                    ['codigo' => 'EDIFICIO', 'valor' => 'Edificio'],
                    ['codigo' => 'NIVEL', 'valor' => 'Nivel'],
                    ['codigo' => 'POSICION', 'valor' => 'Posición'],
                ],
            ],
            'estado_operativo_activo' => [
                'descripcion' => 'Estados operativos para activos de generación eléctrica.',
                'items' => [
                    ['codigo' => 'OPERATIVO', 'valor' => 'Operativo'],
                    ['codigo' => 'RESERVA', 'valor' => 'Reserva'],
                    ['codigo' => 'MANTENIMIENTO', 'valor' => 'Mantenimiento'],
                    ['codigo' => 'FALLA', 'valor' => 'Falla'],
                    ['codigo' => 'RETIRADO', 'valor' => 'Retirado'],
                ],
            ],
            'criticidad_activo' => [
                'descripcion' => 'Niveles de criticidad de un activo.',
                'items' => [
                    ['codigo' => 'BAJA', 'valor' => 'Baja'],
                    ['codigo' => 'MEDIA', 'valor' => 'Media'],
                    ['codigo' => 'ALTA', 'valor' => 'Alta'],
                    ['codigo' => 'CRITICA', 'valor' => 'Crítica'],
                ],
            ],
            'tipo_evento_activo' => [
                'descripcion' => 'Eventos registrados sobre activos.',
                'items' => [
                    ['codigo' => 'INSPECCION', 'valor' => 'Inspección'],
                    ['codigo' => 'MANTENIMIENTO', 'valor' => 'Mantenimiento'],
                    ['codigo' => 'LECTURA', 'valor' => 'Lectura'],
                    ['codigo' => 'FALLA', 'valor' => 'Falla'],
                    ['codigo' => 'PARO', 'valor' => 'Paro'],
                    ['codigo' => 'REPARACION', 'valor' => 'Reparación'],
                    ['codigo' => 'CALIBRACION', 'valor' => 'Calibración'],
                ],
            ],
            'resultado_evento_activo' => [
                'descripcion' => 'Resultados posibles al registrar eventos sobre activos.',
                'items' => [
                    ['codigo' => 'OK', 'valor' => 'OK'],
                    ['codigo' => 'ALERTA', 'valor' => 'Alerta'],
                    ['codigo' => 'FALLA', 'valor' => 'Falla'],
                    ['codigo' => 'PENDIENTE', 'valor' => 'Pendiente'],
                ],
            ],
        ];

        foreach ($catalogos as $nombre => $config) {
            $catalogo = Catalogo::query()->updateOrCreate(
                ['nombre' => $nombre],
                ['descripcion' => $config['descripcion']]
            );

            foreach ($config['items'] as $item) {
                $catalogo->items()->updateOrCreate(
                    ['codigo' => $item['codigo']],
                    ['valor' => $item['valor'], 'activo' => true]
                );
            }
        }
    }
}
