<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WoodWiseReferenceSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Solo 3 tipos de estimación
        $tipos = [
            ['id_tipo_e' => 1, 'desc_estimacion' => 'Volumen Maderable'],
            ['id_tipo_e' => 2, 'desc_estimacion' => 'Biomasa'],
            ['id_tipo_e' => 3, 'desc_estimacion' => 'Carbono'],
        ];

        foreach ($tipos as $tipo) {
            DB::table('tipo_estimaciones')->updateOrInsert(
                ['id_tipo_e' => $tipo['id_tipo_e']],
                [...$tipo, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        // Catálogos
        $catalogos = [
            ['id_cat' => 1, 'nom_cat' => 'Trozas'],
            ['id_cat' => 2, 'nom_cat' => 'Árboles'],
        ];

        foreach ($catalogos as $cat) {
            DB::table('catalogos')->updateOrInsert(
                ['id_cat' => $cat['id_cat']],
                [...$cat, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        // Especies (según tu captura)
        $especies = [
            ['id_especie' => 1, 'nom_cientifico' => 'Pinus pseudostrobus', 'nom_comun' => 'Pino lacio', 'imagen' => null],
            ['id_especie' => 2, 'nom_cientifico' => 'Quercus rugosa', 'nom_comun' => 'Encino blanco', 'imagen' => null],
            ['id_especie' => 3, 'nom_cientifico' => 'Pinus montezumae', 'nom_comun' => 'Pino Moctezuma', 'imagen' => null],
            ['id_especie' => 4, 'nom_cientifico' => 'Quercus crassifolia', 'nom_comun' => 'Encino avellano', 'imagen' => null],
        ];

        foreach ($especies as $esp) {
            DB::table('especies')->updateOrInsert(
                ['id_especie' => $esp['id_especie']],
                [...$esp, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        // Fórmulas con IDs estables (importante por tus triggers)
        // Todas las medidas en metros; D = diámetro pecho (m), H = altura (m), L = longitud (m)
        $formulas = [
            // Trozas (volumen) - id_tipo_e = 1 (Volumen Maderable)
            ['id_formula' => 1, 'nom_formula' => 'Formula de Huber', 'expresion' => 'V = (L / (4π)) * d_m²', 'id_tipo_e' => 1, 'id_cat' => 1],
            ['id_formula' => 2, 'nom_formula' => 'Formula de Smalian', 'expresion' => 'V = (L / (4π)) * ((d_0² + d_1²)/2)', 'id_tipo_e' => 1, 'id_cat' => 1],
            ['id_formula' => 3, 'nom_formula' => 'Formula Tronco Cono', 'expresion' => 'V = (L / (12π)) * (d_0² + d_1² + d_0·d_1)', 'id_tipo_e' => 1, 'id_cat' => 1],
            ['id_formula' => 4, 'nom_formula' => 'Formula Newton', 'expresion' => 'V = (L / (24π)) * (d_0² + d_1² + 4·d_m²)', 'id_tipo_e' => 1, 'id_cat' => 1],

            // Árboles (biomasa por especie) - id_tipo_e = 2 (Biomasa)
            // Nota: Fórmulas esperan D en cm, triggers convierten m→cm (*100)
            ['id_formula' => 5, 'nom_formula' => 'Biomasa Pinus montezumae', 'expresion' => 'B = 0.006 × D^3.038 (D en cm)', 'id_tipo_e' => 2, 'id_cat' => 2],
            ['id_formula' => 6, 'nom_formula' => 'Biomasa Quercus crassifolia', 'expresion' => 'B = 0.283 × (D²·H)^0.807 (D en cm)', 'id_tipo_e' => 2, 'id_cat' => 2],
            ['id_formula' => 7, 'nom_formula' => 'Biomasa Quercus rugosa', 'expresion' => 'B = 0.0192 × D^2.7569 (D en cm)', 'id_tipo_e' => 2, 'id_cat' => 2],
            ['id_formula' => 8, 'nom_formula' => 'Biomasa Pinus pseudostrobus', 'expresion' => 'B = 0.3553 × D^2.2245 (D en cm)', 'id_tipo_e' => 2, 'id_cat' => 2],
        ];

        foreach ($formulas as $formula) {
            DB::table('formulas')->updateOrInsert(
                ['id_formula' => $formula['id_formula']],
                [...$formula, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }
}
