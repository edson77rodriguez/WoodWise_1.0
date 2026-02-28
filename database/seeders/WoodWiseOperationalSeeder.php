<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WoodWiseOperationalSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $idProductor1 = $this->getProductorIdByEmail('productor1@woodwise.test');
        $idProductor2 = $this->getProductorIdByEmail('productor2@woodwise.test');

        // Parcelas (nombres únicos para re-ejecutar el seeder sin duplicados)
        $parcelas = [
            [
                'nom_parcela' => 'Parcela Demo A',
                'ubicacion' => 'Michoacán, MX',
                'id_productor' => $idProductor1,
                'extension' => '12.50',
                'direccion' => 'Camino rural s/n, Ejido La Esperanza',
                'CP' => 58000,
            ],
            [
                'nom_parcela' => 'Parcela Demo B',
                'ubicacion' => 'Michoacán, MX',
                'id_productor' => $idProductor1,
                'extension' => '8.00',
                'direccion' => 'Brecha forestal km 3',
                'CP' => 58110,
            ],
            [
                'nom_parcela' => 'Parcela Demo C',
                'ubicacion' => 'Estado de México, MX',
                'id_productor' => $idProductor2,
                'extension' => '20.00',
                'direccion' => 'Predio El Encinar, carretera estatal',
                'CP' => 50000,
            ],
        ];

        foreach ($parcelas as $parcela) {
            DB::table('parcelas')->updateOrInsert(
                ['nom_parcela' => $parcela['nom_parcela']],
                [...$parcela, 'updated_at' => $now, 'created_at' => $now]
            );
        }

        $idParcelaA = $this->getParcelaIdByNombre('Parcela Demo A');
        $idParcelaB = $this->getParcelaIdByNombre('Parcela Demo B');
        $idParcelaC = $this->getParcelaIdByNombre('Parcela Demo C');

        // Turnos de corta
        $turnos = [
            [
                'codigo_corta' => 'CORTA-DEMO-001',
                'id_parcela' => $idParcelaA,
                'fecha_corta' => Carbon::now()->subDays(20)->toDateString(),
                'fecha_fin' => Carbon::now()->addDays(10)->toDateString(),
            ],
            [
                'codigo_corta' => 'CORTA-DEMO-002',
                'id_parcela' => $idParcelaB,
                'fecha_corta' => Carbon::now()->subDays(10)->toDateString(),
                'fecha_fin' => Carbon::now()->addDays(20)->toDateString(),
            ],
            [
                'codigo_corta' => 'CORTA-DEMO-003',
                'id_parcela' => $idParcelaC,
                'fecha_corta' => Carbon::now()->subDays(5)->toDateString(),
                'fecha_fin' => Carbon::now()->addDays(25)->toDateString(),
            ],
        ];

        foreach ($turnos as $turno) {
            DB::table('turno_cortas')->updateOrInsert(
                ['codigo_corta' => $turno['codigo_corta']],
                [...$turno, 'updated_at' => $now, 'created_at' => $now]
            );
        }

        // Asignación de parcelas a técnicos
        $idTecnico1 = $this->getTecnicoIdByEmail('tecnico1@woodwise.test');
        $idTecnico2 = $this->getTecnicoIdByEmail('tecnico2@woodwise.test');

        $asignaciones = [
            ['id_tecnico' => $idTecnico1, 'id_parcela' => $idParcelaA],
            ['id_tecnico' => $idTecnico1, 'id_parcela' => $idParcelaB],
            ['id_tecnico' => $idTecnico2, 'id_parcela' => $idParcelaC],
        ];

        foreach ($asignaciones as $asigna) {
            DB::table('asigna_parcelas')->updateOrInsert(
                ['id_tecnico' => $asigna['id_tecnico'], 'id_parcela' => $asigna['id_parcela']],
                [...$asigna, 'updated_at' => $now, 'created_at' => $now]
            );
        }
    }

    private function getProductorIdByEmail(string $email): int
    {
        $id = DB::table('productores')
            ->join('personas', 'productores.id_persona', '=', 'personas.id_persona')
            ->where('personas.correo', $email)
            ->value('productores.id_productor');

        if (!$id) {
            throw new \RuntimeException('No se encontró productor para: ' . $email);
        }

        return (int) $id;
    }

    private function getTecnicoIdByEmail(string $email): int
    {
        $id = DB::table('tecnicos')
            ->join('personas', 'tecnicos.id_persona', '=', 'personas.id_persona')
            ->where('personas.correo', $email)
            ->value('tecnicos.id_tecnico');

        if (!$id) {
            throw new \RuntimeException('No se encontró técnico para: ' . $email);
        }

        return (int) $id;
    }

    private function getParcelaIdByNombre(string $nombre): int
    {
        $id = DB::table('parcelas')->where('nom_parcela', $nombre)->value('id_parcela');

        if (!$id) {
            throw new \RuntimeException('No se encontró parcela: ' . $nombre);
        }

        return (int) $id;
    }
}
