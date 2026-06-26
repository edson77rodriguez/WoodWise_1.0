<?php

namespace Database\Seeders;

use App\Models\PrecioMercado;
use Illuminate\Database\Seeder;

class PrecioMercadoSeeder extends Seeder
{
    public function run(): void
    {
        $precios = [
            [
                'especie' => 'pino',
                'precio_por_m3' => 2500.00,
                'moneda' => 'MXN',
                'fuente' => 'Estimacion comercial base',
                'ultima_actualizacion' => now(),
            ],
            [
                'especie' => 'oyamel',
                'precio_por_m3' => 2200.00,
                'moneda' => 'MXN',
                'fuente' => 'Estimacion comercial base',
                'ultima_actualizacion' => now(),
            ],
            [
                'especie' => 'encino',
                'precio_por_m3' => 2800.00,
                'moneda' => 'MXN',
                'fuente' => 'Estimacion comercial base',
                'ultima_actualizacion' => now(),
            ],
        ];

        foreach ($precios as $precio) {
            PrecioMercado::updateOrCreate([
                'especie' => $precio['especie'],
            ], $precio);
        }
    }
}