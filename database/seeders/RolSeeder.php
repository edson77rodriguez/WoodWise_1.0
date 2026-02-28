<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $roles = [
            ['id_rol' => 1, 'nom_rol' => 'Administrador'],
            ['id_rol' => 2, 'nom_rol' => 'Tecnico'],
            ['id_rol' => 3, 'nom_rol' => 'Productor'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['id_rol' => $role['id_rol']],
                [...$role, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }
}
