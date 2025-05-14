<?php

namespace Tests\Feature;

use App\Models\Aulas;
use App\Models\Cursos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AulasCrudTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function aula_deve_ser_criada_com_dados_validos()
    {
        // Criação de um curso
        $curso = Cursos::factory()->create();

        // Dados válidos para a aula
        $data = [
            'sequencia' => 1,
            'titulo' => 'Aula de Teste',
            'duracaoMinutos' => 30,
            'videoUrl' => 'https://www.exemplo.com/video',
            'curso_id' => $curso->id,
        ];

        $response = $this->postJson('/api/aulas/create', $data);

        $response->assertStatus(201); // Criado com sucesso
        $this->assertDatabaseHas('aulas', $data); // Verifica se a aula foi inserida na base
    }

    /** @test */
    public function aula_deve_falhar_sem_ter_curso_vinculado()
    {
        $data = [
            'sequencia' => 1,
            'titulo' => 'Aula sem curso',
            'duracaoMinutos' => 30,
            'videoUrl' => 'https://www.exemplo.com/video',
            'curso_id' => null, // Curso não fornecido
        ];

        $response = $this->postJson('/api/aulas/create', $data);

        $response->assertStatus(422); // Falha na validação
        $response->assertJsonValidationErrors('curso_id'); // Verifica erro na validação do campo curso_id
    }

    /** @test */
    public function aula_deve_falhar_com_sequencial_repetido_no_mesmo_curso()
    {
        // Criação de um curso
        $curso = Cursos::factory()->create();

        // Criação de uma aula inicial
        Aulas::create([
            'sequencia' => 1,
            'titulo' => 'Aula 1',
            'duracaoMinutos' => 30,
            'videoUrl' => 'https://www.exemplo.com/video',
            'curso_id' => $curso->id,
        ]);

        // Tentando criar uma aula com a mesma sequência
        $data = [
            'sequencia' => 1,
            'titulo' => 'Aula 2',
            'duracaoMinutos' => 40,
            'videoUrl' => 'https://www.exemplo.com/video2',
            'curso_id' => $curso->id,
        ];

        $response = $this->postJson('/api/aulas/create', $data);

        $response->assertStatus(422); // Falha na validação
        $response->assertJsonValidationErrors('sequencia'); // Verifica erro na validação do campo sequencia
    }

    /** @test */
    public function aula_deve_ser_atualizada_com_dados_validos()
    {
        // Criação de um curso e aula
        $curso = Cursos::factory()->create();
        $aula = Aulas::create([
            'sequencia' => 1,
            'titulo' => 'Aula Original',
            'duracaoMinutos' => 30,
            'videoUrl' => 'https://www.exemplo.com/video',
            'curso_id' => $curso->id,
        ]);

        $data = [
            'sequencia' => 2,
            'titulo' => 'Aula Atualizada',
            'duracaoMinutos' => 45,
            'videoUrl' => 'https://www.exemplo.com/video-atualizado',
            'curso_id' => $curso->id,
        ];

        $response = $this->putJson("/api/aulas/update/{$aula->id}", $data);

        $response->assertStatus(200); // Atualizado com sucesso
        $this->assertDatabaseHas('aulas', $data); // Verifica se a aula foi atualizada na base
    }

    /** @test */
public function apenas_administradores_podem_criar_aulas()
{
    $curso = Cursos::factory()->create();
    $user = User::factory()->create(); // Usuário não admin
    $data = [
        'sequencia' => 1,
        'titulo' => 'Aula Teste',
        'duracaoMinutos' => 30,
        'videoUrl' => 'https://www.exemplo.com/video',
        'curso_id' => $curso->id,
    ];

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/aulas/create', $data);

    $response->assertStatus(403); // Acesso negado para usuário não admin
}

/** @test */
public function apenas_administradores_podem_atualizar_aulas()
{
    $curso = Cursos::factory()->create();
    $user = User::factory()->create(); // Usuário não admin
    $aula = Aulas::create([
        'sequencia' => 1,
        'titulo' => 'Aula Teste',
        'duracaoMinutos' => 30,
        'videoUrl' => 'https://www.exemplo.com/video',
        'curso_id' => $curso->id,
    ]);

    $data = [
        'sequencia' => 2,
        'titulo' => 'Aula Atualizada',
        'duracaoMinutos' => 45,
        'videoUrl' => 'https://www.exemplo.com/video-atualizado',
        'curso_id' => $curso->id,
    ];

    $response = $this->actingAs($user, 'sanctum')->putJson("/api/aulas/update/{$aula->id}", $data);

    $response->assertStatus(403); // Acesso negado para usuário não admin
}

/** @test */
public function apenas_administradores_podem_deletar_aulas()
{
    $curso = Cursos::factory()->create();
    $user = User::factory()->create(); // Usuário não admin
    $aula = Aulas::create([
        'sequencia' => 1,
        'titulo' => 'Aula Teste',
        'duracaoMinutos' => 30,
        'videoUrl' => 'https://www.exemplo.com/video',
        'curso_id' => $curso->id,
    ]);

    $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/aulas/delete/{$aula->id}");

    $response->assertStatus(403); // Acesso negado para usuário não admin
}
    /** @test */
    public function aula_deve_ser_deletada_com_sucesso()
    {
        // Criação de um curso e aula
        $curso = Cursos::factory()->create();
        $aula = Aulas::create([
            'sequencia' => 1,
            'titulo' => 'Aula para Deletar',
            'duracaoMinutos' => 30,
            'videoUrl' => 'https://www.exemplo.com/video',
            'curso_id' => $curso->id,
        ]);

        $response = $this->deleteJson("/api/aulas/delete/{$aula->id}");

        $response->assertStatus(200); // Deletado com sucesso
        $this->assertDatabaseMissing('aulas', ['id' => $aula->id]); // Verifica se a aula foi removida da base
    }
    /** @test */
    public function aula_deve_ser_exibida_com_sucesso()
    {
        // Criação de um curso e aula
        $curso = Cursos::factory()->create();
        $aula = Aulas::create([
            'sequencia' => 1,
            'titulo' => 'Aula para Exibir',
            'duracaoMinutos' => 30,
            'videoUrl' => 'https://www.exemplo.com/video',
            'curso_id' => $curso->id,
        ]);

        $response = $this->getJson("/api/aulas/show/{$aula->id}");

        $response->assertStatus(200); // Exibido com sucesso
        $response->assertJsonFragment(['titulo' => 'Aula para Exibir']); // Verifica se o título está na resposta
    }
    /** @test */
    public function aula_deve_ser_listada_com_sucesso()
    {
        // Criação de um curso e aulas
        $curso = Cursos::factory()->create();
        Aulas::factory()->count(3)->create(['curso_id' => $curso->id]);

        $response = $this->getJson('/api/aulas');

        $response->assertStatus(200); // Listado com sucesso
        $response->assertJsonCount(3); // Verifica se 3 aulas foram retornadas
    }
    /** @test */
    public function aula_deve_ser_procurada_com_sucesso()
    {
        // Criação de um curso e aulas
        $curso = Cursos::factory()->create();
        Aulas::factory()->count(3)->create(['curso_id' => $curso->id]);

        $response = $this->getJson('/api/aulas/search/Aula');

        $response->assertStatus(200); // Procurado com sucesso
        $response->assertJsonCount(3); // Verifica se 3 aulas foram retornadas
    }
    /** @test */
    public function aula_deve_ser_marcada_como_visto()
    {
        // Criação de um curso e aula
        $curso = Cursos::factory()->create();
        $aula = Aulas::create([
            'sequencia' => 1,
            'titulo' => 'Aula para Marcar Visto',
            'duracaoMinutos' => 30,
            'videoUrl' => 'https://www.exemplo.com/video',
            'curso_id' => $curso->id,
        ]);

        $response = $this->patchJson("/api/aulas/{$aula->id}/visto");

        $response->assertStatus(200); // Marcado como visto com sucesso
        $this->assertDatabaseHas('aulas', ['id' => $aula->id, 'visto' => true]); // Verifica se a aula foi marcada como vista
    }
    /** @test */
    public function aula_deve_ser_marcada_como_nao_visto()
    {
        // Criação de um curso e aula
        $curso = Cursos::factory()->create();
        $aula = Aulas::create([
            'sequencia' => 1,
            'titulo' => 'Aula para Marcar Não Visto',
            'duracaoMinutos' => 30,
            'videoUrl' => 'https://www.exemplo.com/video',
            'curso_id' => $curso->id,
        ]);

        $response = $this->patchJson("/api/aulas/{$aula->id}/visto");

        $response->assertStatus(200); // Marcado como não visto com sucesso
        $this->assertDatabaseMissing('aulas', ['id' => $aula->id, 'visto' => true]); // Verifica se a aula foi marcada como não vista
    }

}
