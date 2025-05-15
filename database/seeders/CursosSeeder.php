<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CursosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('cursos')->insert([
            [
                'titulo' => 'Curso de Libras',
                'capa' => 'imagens/cursos/capas/curso_libras.png',
                'descricao' => 'Aprenda a Língua Brasileira de Sinais para comunicação inclusiva.',
                'categoria' => 'Inclusão',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'titulo' => 'Curso de Comunicação para Surdocegos',
                'capa' => 'imagens/cursos/capas/recebendo_surdos.png',
                'descricao' => 'Aprenda técnicas de comunicação tátil para pessoas com surdocegueira.',
                'categoria' => 'Inclusão',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'titulo' => 'Curso de Tecnologia Assistiva',
                'capa' => 'imagens/cursos/capas/Cegueira.png',
                'descricao' => 'Descubra ferramentas tecnológicas para inclusão de pessoas com deficiência.',
                'categoria' => 'Inclusão',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
