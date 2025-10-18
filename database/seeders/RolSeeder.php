<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insert([
            [
                'id_rol' => 1,
                'nom_rol' => 'Administrador',
                'desc_rol' => 'Administra toda la información del Sistema',
            ],
            [
                'id_rol' => 2,
                'nom_rol' => 'Tecnico',
                'desc_rol' => 'Administra toda las estimaciones de una parcela',
            ],
            [
                'id_rol' => 3,
                'nom_rol' => 'Productor',
                'desc_rol' => 'Dueño de una o muchas parcelas',
            ],
        ]);
    }
}
