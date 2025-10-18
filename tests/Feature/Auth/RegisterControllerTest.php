<?php

namespace Tests\Feature\Auth;

use App\Models\Rol;
use App\Models\Persona;
use App\Models\User;
use App\Models\Tecnico;
use App\Models\Productor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RegisterControllerTest extends TestCase
{
    use DatabaseTransactions; // Evita afectar la BD real

    /** @test */
    public function muestra_formulario_de_registro_con_roles()
    {
        // Crear roles de prueba (excepto "Administrador")
        Rol::factory()->create(['nom_rol' => 'Técnico']);
        Rol::factory()->create(['nom_rol' => 'Productor']);

        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Técnico');
        $response->assertSee('Productor')o;
        $response->assertDontSee('Administrador'); // Asegura que no aparece
    }

    /** @test */
    public function registra_usuario_tecnico_correctamente()
    {
        $rol = Rol::factory()->create(['nom_rol' => 'Técnico']);
    
        $response = $this->post('/register', [
            'nom' => 'Juan',
            'ap' => 'Pérez',
            'am' => 'Gómez',
            'telefono' => '1234567890',
            'email' => 'juan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'cedula' => 'TEC12345',
            'id_rol' => $rol->id_rol,
        ]);
    
        $response->assertRedirect('/dashboard1');
        
        $this->assertDatabaseHas('personas', [
            'correo' => 'juan@example.com' // Asegúrate que el campo se llama 'correo' no 'email'
        ]);
    }
     /** @test */
    public function registra_usuario_productor_correctamente()
    {
        $rol = Rol::factory()->create(['nom_rol' => 'Productor']);
    
        $response = $this->post('/register', [
            'nom' => 'Ana',
            'ap' => 'López',
            'am' => 'Martínez',
            'telefono' => '9876543210',
            'email' => 'ana@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'id_rol' => $rol->id_rol,
        ]);
    
        $response->assertRedirect('/dashboard1');
        
        $persona = Persona::where('correo', 'ana@example.com')->first();
        $this->assertNotNull($persona);
    }

   
}