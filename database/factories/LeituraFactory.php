<?php

namespace Database\Factories;

use App\Models\Cursos;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Leitura>
 */
class LeituraFactory extends Factory
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
            'conteudo' => fake()->randomHtml(1,7),
        ];
    }
}
