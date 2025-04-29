<?php

namespace Database\Factories;

use App\Models\Cursos;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Leitura>
 */
class LeituraFactory extends Factory
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
        $selectedCourse = fake()->randomElement($cursosId);
        $sequencias = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15];
        $sequence = fake()->randomElement($sequencias);
        while(in_array("$selectedCourse-$sequence", self::$used)){
            $selectedCourse = fake()->randomElement($cursosId);
            $sequence = fake()->randomElement($sequencias);
        }
        self::$used[] = "$selectedCourse-$sequence";

        return [
            'curso_id' => $selectedCourse,
            'sequencia' => $sequence,
            'titulo' => fake()->sentence(),
            'conteudo' => implode("", array_map(fn($paragraph) => "<p>$paragraph</p>",
            fake()->paragraphs(5))),
        ];
    }
}
