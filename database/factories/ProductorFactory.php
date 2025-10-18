<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Productor;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Productor>
 */
class ProductorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_persona' => \App\Models\Persona::factory()
        ];
    }
}
