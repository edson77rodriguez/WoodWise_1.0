<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // 1. Crear o obtener el rol de Administrador
        $adminRol = Rol::firstOrCreate(
            ['nom_rol' => 'Administrador'], // Condición para verificar si ya existe
            ['desc_rol' => 'Rol con acceso total al sistema'] // Si no existe, se crea con esta descripción
        );

        // 2. Crear la persona administradora
        $personaAdmin = Persona::create([
            'nom' => 'Admin',
            'ap' => 'Sistema',
            'am' => 'Principal',
            'telefono' => '0000000000',
            'correo' => 'admin@empresa.com',
            'contrasena' => Hash::make('AdminPassword123!'), // Se encripta correctamente con Bcrypt
            'id_rol' => $adminRol->id_rol, // Asocia el rol de administrador
            'is_producer' => 0 // Establece que no es productor
        ]);

        // 3. Crear el usuario de autenticación
        User::create([
            'name' => 'Administrador del Sistema',
            'email' => 'admin@empresa.com',
            'password' => Hash::make('AdminPassword123!'), // Se encripta correctamente con Bcrypt
            'id_persona' => $personaAdmin->id_persona // Asocia el usuario con la persona administradora
        ]);

        // Mensajes informativos para el comando de la terminal
        $this->command->info('✅ Usuario administrador creado exitosamente!');
        $this->command->info('📧 Email: admin@empresa.com');
        $this->command->info('🔑 Contraseña: AdminPassword123!');
    }
}
