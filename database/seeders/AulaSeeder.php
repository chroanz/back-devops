<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Aulas;
use App\Models\Cursos;

class AulaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all courses
        $cursos = Cursos::all();
        
        // Create aulas for each curso with sequential numbering
        foreach ($cursos as $curso) {
            // Create 5 aulas for each curso
            for ($i = 1; $i <= 5; $i++) {
                Aulas::factory()->create([
                    'curso_id' => $curso->id,
                    'sequencia' => $i, // Ensures sequencia is unique for each curso
                ]);
            }
        }
    }
}