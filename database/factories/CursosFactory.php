<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cursos>
 */
class CursosFactory extends Factory
{
    private static array $used = [];
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'titulo' => $this->faker->sentence(),
            'descricao' => $this->faker->paragraph(),
            'categoria' => $this->faker->word(),
            'capa' => $this->faker->imageUrl()
        ];
    }
}
