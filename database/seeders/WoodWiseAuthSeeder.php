<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class WoodWiseAuthSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $password = 'Password123!';
        $hashedPassword = Hash::make($password);

        $personas = [
            [
                'nom' => 'Admin',
                'ap' => 'Sistema',
                'am' => 'Principal',
                'telefono' => '5550000001',
                'correo' => 'admin@woodwise.test',
                'contrasena' => $hashedPassword,
                'id_rol' => 1,
                'is_producer' => 0,
            ],
            [
                'nom' => 'Ana',
                'ap' => 'Ramírez',
                'am' => 'López',
                'telefono' => '5550000002',
                'correo' => 'tecnico1@woodwise.test',
                'contrasena' => $hashedPassword,
                'id_rol' => 2,
                'is_producer' => 0,
            ],
            [
                'nom' => 'Luis',
                'ap' => 'Hernández',
                'am' => 'Cruz',
                'telefono' => '5550000003',
                'correo' => 'tecnico2@woodwise.test',
                'contrasena' => $hashedPassword,
                'id_rol' => 2,
                'is_producer' => 0,
            ],
            [
                'nom' => 'María',
                'ap' => 'Gómez',
                'am' => 'Santos',
                'telefono' => '5550000004',
                'correo' => 'productor1@woodwise.test',
                'contrasena' => $hashedPassword,
                'id_rol' => 3,
                'is_producer' => 1,
            ],
            [
                'nom' => 'Carlos',
                'ap' => 'Pérez',
                'am' => 'Nava',
                'telefono' => '5550000005',
                'correo' => 'productor2@woodwise.test',
                'contrasena' => $hashedPassword,
                'id_rol' => 3,
                'is_producer' => 1,
            ],
        ];

        $personaIdsByEmail = [];
        foreach ($personas as $persona) {
            $personaId = $this->upsertPersona($persona, $now);
            $personaIdsByEmail[$persona['correo']] = $personaId;
        }

        $users = [
            ['name' => 'Administrador', 'email' => 'admin@woodwise.test', 'password' => $hashedPassword, 'id_persona' => $personaIdsByEmail['admin@woodwise.test']],
            ['name' => 'Técnico 1', 'email' => 'tecnico1@woodwise.test', 'password' => $hashedPassword, 'id_persona' => $personaIdsByEmail['tecnico1@woodwise.test']],
            ['name' => 'Técnico 2', 'email' => 'tecnico2@woodwise.test', 'password' => $hashedPassword, 'id_persona' => $personaIdsByEmail['tecnico2@woodwise.test']],
            ['name' => 'Productor 1', 'email' => 'productor1@woodwise.test', 'password' => $hashedPassword, 'id_persona' => $personaIdsByEmail['productor1@woodwise.test']],
            ['name' => 'Productor 2', 'email' => 'productor2@woodwise.test', 'password' => $hashedPassword, 'id_persona' => $personaIdsByEmail['productor2@woodwise.test']],
        ];

        foreach ($users as $user) {
            $this->upsertUser($user, $now);
        }

        // Perfiles: técnicos y productores (idempotentes por id_persona)
        DB::table('tecnicos')->updateOrInsert(
            ['id_persona' => $personaIdsByEmail['tecnico1@woodwise.test']],
            ['cedula_p' => 'TEC-0001', 'updated_at' => $now, 'created_at' => $now]
        );

        DB::table('tecnicos')->updateOrInsert(
            ['id_persona' => $personaIdsByEmail['tecnico2@woodwise.test']],
            ['cedula_p' => 'TEC-0002', 'updated_at' => $now, 'created_at' => $now]
        );

        DB::table('productores')->updateOrInsert(
            ['id_persona' => $personaIdsByEmail['productor1@woodwise.test']],
            ['updated_at' => $now, 'created_at' => $now]
        );

        DB::table('productores')->updateOrInsert(
            ['id_persona' => $personaIdsByEmail['productor2@woodwise.test']],
            ['updated_at' => $now, 'created_at' => $now]
        );

        $this->command?->info('Usuarios demo creados. Password para todos: ' . $password);
    }

    private function upsertPersona(array $persona, Carbon $now): int
    {
        $existing = DB::table('personas')->where('correo', $persona['correo'])->first();

        if ($existing) {
            DB::table('personas')->where('id_persona', $existing->id_persona)->update([
                ...$persona,
                'updated_at' => $now,
            ]);

            return (int) $existing->id_persona;
        }

        return (int) DB::table('personas')->insertGetId([
            ...$persona,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function upsertUser(array $user, Carbon $now): int
    {
        $existing = DB::table('users')->where('email', $user['email'])->first();

        if ($existing) {
            DB::table('users')->where('id', $existing->id)->update([
                ...$user,
                'updated_at' => $now,
            ]);

            return (int) $existing->id;
        }

        return (int) DB::table('users')->insertGetId([
            ...$user,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
