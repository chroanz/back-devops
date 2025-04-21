<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Cursos;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\aulas>
 */
class AulasFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cursosId = Cursos::all()->pluck('id');
        return [
            'curso_id' => fake()->randomElement($cursosId),
            'sequencia' => fake()->randomDigitNotZero(),
            'titulo' => fake()->sentence(),
            'duracaoMinutos' => fake()->randomNumber(2),
            'videoUrl' => fake()->url(),
        ];
    }
}
