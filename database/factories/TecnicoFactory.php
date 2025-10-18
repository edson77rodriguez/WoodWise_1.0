<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Tecnico;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tecnico>
 */
class TecnicoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_persona' => \App\Models\Persona::factory(),
            'cedula_p' => $this->faker->randomNumber(8),
            'clave_tecnico' => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(8))
        ];
    }
}
