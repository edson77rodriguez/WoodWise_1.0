<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Persona;
use App\Models\Rol;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Persona>
 */
class PersonaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => $this->faker->firstName,
        'ap' => $this->faker->lastName,
        'am' => $this->faker->lastName,
        'telefono' => $this->faker->phoneNumber,
        'correo' => $this->faker->unique()->safeEmail,
        'contrasena' => bcrypt('password'),
        'id_rol' => Rol::factory(),
        'cedula' => $this->faker->randomNumber(8)
        ];
    }
}
