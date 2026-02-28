<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WoodWiseMeasurementsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Limpieza acotada: solo para las parcelas demo
        $demoParcelas = [
            $this->getParcelaIdByNombre('Parcela Demo A'),
            $this->getParcelaIdByNombre('Parcela Demo B'),
            $this->getParcelaIdByNombre('Parcela Demo C'),
        ];

        // Borrar estimaciones dependientes primero
        DB::table('estimaciones')->whereIn('id_troza', function ($q) use ($demoParcelas) {
            $q->select('id_troza')->from('trozas')->whereIn('id_parcela', $demoParcelas);
        })->delete();

        DB::table('estimaciones1')->whereIn('id_arbol', function ($q) use ($demoParcelas) {
            $q->select('id_arbol')->from('arboles')->whereIn('id_parcela', $demoParcelas);
        })->delete();

        DB::table('trozas')->whereIn('id_parcela', $demoParcelas)->delete();
        DB::table('arboles')->whereIn('id_parcela', $demoParcelas)->delete();

        [$idParcelaA, $idParcelaB, $idParcelaC] = $demoParcelas;

        // =====================================================================
        // TROZAS - Todas las medidas en METROS
        // Rangos válidos: longitud 0 < L <= 100, diámetros 0 < d <= 5, densidad 0 < d <= 2 (ton/m³)
        // =====================================================================
        $trozasToCreate = [
            // Parcela A - Pinus pseudostrobus y Pinus montezumae
            ['id_parcela' => $idParcelaA, 'id_especie' => 1, 'longitud' => 3.20, 'diametro' => 0.40, 'diametro_otro_extremo' => 0.36, 'diametro_medio' => 0.38, 'densidad' => 0.55],
            ['id_parcela' => $idParcelaA, 'id_especie' => 3, 'longitud' => 4.10, 'diametro' => 0.48, 'diametro_otro_extremo' => 0.43, 'diametro_medio' => 0.46, 'densidad' => 0.60],

            // Parcela B - Quercus rugosa y Quercus crassifolia
            ['id_parcela' => $idParcelaB, 'id_especie' => 2, 'longitud' => 2.80, 'diametro' => 0.35, 'diametro_otro_extremo' => 0.31, 'diametro_medio' => 0.33, 'densidad' => 0.70],
            ['id_parcela' => $idParcelaB, 'id_especie' => 4, 'longitud' => 5.50, 'diametro' => 0.52, 'diametro_otro_extremo' => 0.45, 'diametro_medio' => 0.49, 'densidad' => 0.75],

            // Parcela C - mezcla
            ['id_parcela' => $idParcelaC, 'id_especie' => 1, 'longitud' => 3.70, 'diametro' => 0.44, 'diametro_otro_extremo' => 0.39, 'diametro_medio' => 0.42, 'densidad' => 0.58],
            ['id_parcela' => $idParcelaC, 'id_especie' => 2, 'longitud' => 4.60, 'diametro' => 0.50, 'diametro_otro_extremo' => 0.47, 'diametro_medio' => 0.49, 'densidad' => 0.72],
        ];

        $trozaIds = [];
        foreach ($trozasToCreate as $troza) {
            $trozaIds[] = (int) DB::table('trozas')->insertGetId([
                ...$troza,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // =====================================================================
        // ESTIMACIONES de trozas - El TRIGGER calcula calculo/biomasa/carbono
        // Solo necesitamos: id_tipo_e (1=Volumen Maderable), id_formula (1-4), id_troza
        // =====================================================================
        $estimacionesTrozas = [
            ['id_troza' => $trozaIds[0], 'id_formula' => 1], // Huber
            ['id_troza' => $trozaIds[1], 'id_formula' => 2], // Smalian
            ['id_troza' => $trozaIds[2], 'id_formula' => 3], // Tronco Cono
            ['id_troza' => $trozaIds[3], 'id_formula' => 4], // Newton
            ['id_troza' => $trozaIds[4], 'id_formula' => 1], // Huber
            ['id_troza' => $trozaIds[5], 'id_formula' => 2], // Smalian
        ];

        foreach ($estimacionesTrozas as $est) {
            // El trigger BEFORE INSERT (calcular_todo_estimacion) calcula calculo, biomasa, carbono
            DB::table('estimaciones')->insert([
                'id_tipo_e' => 1, // Volumen Maderable
                'id_formula' => $est['id_formula'],
                'calculo' => 0, // El trigger lo sobrescribe
                'biomasa' => 0, // El trigger lo sobrescribe
                'carbono' => 0, // El trigger lo sobrescribe
                'id_troza' => $est['id_troza'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // =====================================================================
        // ÁRBOLES - Todas las medidas en METROS
        // diametro_pecho en metros (ej: 0.38m = 38cm)
        // altura_total en metros
        // El trigger AFTER INSERT (calcular_estimaciones_arbol) crea automáticamente
        // las estimaciones1 (Volumen Maderable, Biomasa, Carbono)
        // =====================================================================
        $arbolesToCreate = [
            // Parcela A - Pinus pseudostrobus y Pinus montezumae
            ['id_parcela' => $idParcelaA, 'id_especie' => 1, 'altura_total' => 18.5, 'diametro_pecho' => 0.38],
            ['id_parcela' => $idParcelaA, 'id_especie' => 3, 'altura_total' => 22.0, 'diametro_pecho' => 0.425],

            // Parcela B - Quercus rugosa y Quercus crassifolia
            ['id_parcela' => $idParcelaB, 'id_especie' => 2, 'altura_total' => 16.2, 'diametro_pecho' => 0.34],
            ['id_parcela' => $idParcelaB, 'id_especie' => 4, 'altura_total' => 19.8, 'diametro_pecho' => 0.40],

            // Parcela C - mezcla
            ['id_parcela' => $idParcelaC, 'id_especie' => 1, 'altura_total' => 21.1, 'diametro_pecho' => 0.41],
            ['id_parcela' => $idParcelaC, 'id_especie' => 2, 'altura_total' => 17.4, 'diametro_pecho' => 0.36],
        ];

        foreach ($arbolesToCreate as $arbolData) {
            // El trigger BEFORE INSERT (validar_arbol) valida los datos
            // El trigger AFTER INSERT (calcular_estimaciones_arbol) crea automáticamente
            // las estimaciones de Volumen Maderable, Biomasa y Carbono
            DB::table('arboles')->insert([
                ...$arbolData,
                // No ponemos created_at/updated_at porque el trigger validar_arbol los asigna
            ]);
        }

        $this->command?->info('Mediciones demo insertadas. Los triggers calcularon los valores automaticamente.');
    }

    private function getParcelaIdByNombre(string $nombre): int
    {
        $id = DB::table('parcelas')->where('nom_parcela', $nombre)->value('id_parcela');

        if (!$id) {
            throw new \RuntimeException('No se encontro parcela: ' . $nombre);
        }

        return (int) $id;
    }
}
