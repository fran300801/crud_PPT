<?php

namespace Database\Factories;

use App\Models\Jugador;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Jugador>
 */
class JugadorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'jugadas' => 0,
            'ganadas' => 0,
        ];
    }
}
