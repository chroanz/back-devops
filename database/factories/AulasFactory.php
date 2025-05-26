<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Cursos;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\aulas>
 */
class AulasFactory extends Factory
{
    private static array $used = [];
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cursosId = Cursos::all()->pluck('id');
        $sequencias = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15];
        $selectedCourse = fake()->randomElement($cursosId);
        $sequence = fake()->randomElement($sequencias);
        while (in_array("$selectedCourse-$sequence", self::$used)) {
            $selectedCourse = fake()->randomElement($cursosId);
            $sequence = fake()->randomElement($sequencias);
        }
        self::$used[] = "$selectedCourse-$sequence";
        return [
            'curso_id' => $selectedCourse,
            'sequencia' => $sequence,
            'titulo' => 'Aula ' . fake()->words(2, true), 
            'duracaoMinutos' => fake()->randomNumber(2),
            'videoUrl' => fake()->url(),
        ];
    }
}
