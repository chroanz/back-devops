<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Aulas;

class AulaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        # Libras
        Aulas::create([
            'curso_id' => 1,
            'sequencia' => 1,
            'titulo' => 'Como aprender libras sozinho',
            'duracaoMinutos' => 17,
            'videoUrl' => 'https://www.youtube.com/watch?v=-ZDkdbPqUZg'
        ]);
        Aulas::create([
            'curso_id' => 1,
            'sequencia' => 2,
            'titulo' => 'Cumprimentos em Libras',
            'duracaoMinutos' => 7,
            'videoUrl' => 'https://www.youtube.com/watch?v=udWSfcwMgH4'
        ]);
        Aulas::create([
            'curso_id' => 1,
            'sequencia' => 3,
            'titulo' => '35 Sinais de libras básicos',
            'duracaoMinutos' => 24,
            'videoUrl' => 'https://www.youtube.com/watch?v=qkSfTWwds8c'
        ]);
        Aulas::create([
            'curso_id' => 1,
            'sequencia' => 4,
            'titulo' => '5 frases em libras para iniciantes',
            'duracaoMinutos' => 16,
            'videoUrl' => 'https://www.youtube.com/watch?v=6zjYw96B2Lc'
        ]);
        Aulas::create([
            'curso_id' => 1,
            'sequencia' => 5,
            'titulo' => 'Sinais de pronomes em libras',
            'duracaoMinutos' => 15,
            'videoUrl' => 'https://www.youtube.com/watch?v=rSNb4zxoqvo'
        ]);
        Aulas::create([
            'curso_id' => 1,
            'sequencia' => 6,
            'titulo' => 'Expressões faciais na libras',
            'duracaoMinutos' => 15,
            'videoUrl' => 'https://www.youtube.com/watch?v=0LF6vgqDcVM'
        ]);

        #Comunicação para surdocegos
        Aulas::create([
            'curso_id' => 2,
            'sequencia' => 1,
            'titulo' => 'Surdocegueira e comunicação',
            'duracaoMinutos' => 1,
            'videoUrl' => 'https://www.youtube.com/watch?v=hyDuZFCECgY'
        ]);
        Aulas::create([
            'curso_id' => 2,
            'sequencia' => 2,
            'titulo' => 'Formas de comunicação usadas por pessoas com surdocegueira',
            'duracaoMinutos' => 15,
            'videoUrl' => 'https://www.youtube.com/watch?v=UEo9-lLGKV4'
        ]);
        Aulas::create([
            'curso_id' => 2,
            'sequencia' => 3,
            'titulo' => 'Alfabeto manual',
            'duracaoMinutos' => 8,
            'videoUrl' => 'https://www.youtube.com/watch?v=U3fTGgSC1u4'
        ]);
        Aulas::create([
            'curso_id' => 2,
            'sequencia' => 4,
            'titulo' => 'Olhar através do outro (surdocegueira)',
            'duracaoMinutos' => 10,
            'videoUrl' => 'https://www.youtube.com/watch?v=_PVZqLREAG4'
        ]);
        Aulas::create([
            'curso_id' => 2,
            'sequencia' => 5,
            'titulo' => 'Surdocegueira',
            'duracaoMinutos' => 31,
            'videoUrl' => 'https://www.youtube.com/watch?v=5pS27Fakbkk'
        ]);


        #Tecnologias assistivas
        Aulas::create([
            'curso_id' => 3,
            'sequencia' => 1,
            'titulo' => 'O que é tecnologia assistiva?',
            'duracaoMinutos' => 3,
            'videoUrl' => 'https://www.youtube.com/watch?v=a-5d73f4W-o'
        ]);
        Aulas::create([
            'curso_id' => 3,
            'sequencia' => 2,
            'titulo' => 'Tecnologia permite cegos lerem livros',
            'duracaoMinutos' => 6,
            'videoUrl' => 'https://www.youtube.com/watch?v=VFOO0vXNSjQ'
        ]);
        Aulas::create([
            'curso_id' => 3,
            'sequencia' => 3,
            'titulo' => 'Impacto da tecnologia na educação',
            'duracaoMinutos' => 10,
            'videoUrl' => 'https://www.youtube.com/watch?v=TWgGHojXuB'
        ]);
        Aulas::create([
            'curso_id' => 3,
            'sequencia' => 4,
            'titulo' => 'Tecnologias assistivas',
            'duracaoMinutos' => 13,
            'videoUrl' => 'https://www.youtube.com/watch?v=PQj0OXd23no'
        ]);
        Aulas::create([
            'curso_id' => 3,
            'sequencia' => 5,
            'titulo' => 'AEE e Tecnologia Assistiva',
            'duracaoMinutos' => 18,
            'videoUrl' => 'https://www.youtube.com/watch?v=1qhGjO6N450'
        ]);

    }
}
