<?php

namespace Tests\Feature;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\UserFunction;
use App\Models\User;
use App\Models\Cursos;
use App\Models\Aulas;

class AulasCrudTest extends TestCase
{
    use RefreshDatabase;
    private User $userDefault;
    private User $userAdmin;
    private Cursos $curso;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userAdmin = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);
        $functionAdmin = UserFunction::create(['user_id' => $this->userAdmin->id, 'function' => 'admin']);
        $this->userAdmin->functions()->save($functionAdmin);

        $this->userDefault = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('password')
        ]);
        $functionDefault = UserFunction::create(['user_id' => $this->userDefault->id, 'function' => 'default']);
        $this->userDefault->functions()->save($functionDefault);

        $this->curso = Cursos::factory()->create();
    }

    #[Test]
    public function aula_deve_ser_criada_com_dados_validos()
    {

        // Dados válidos para a aula
        $data = [
            'sequencia' => 1,
            'titulo' => 'Aula de Teste',
            'duracaoMinutos' => 30,
            'videoUrl' => 'https://www.exemplo.com/video',
            'curso_id' => $this->curso->id,
        ];

        $response = $this->actingAs($this->userAdmin, 'api')->postJson('/api/aulas/create', $data);

        $response->assertStatus(201); // Criado com sucesso
        $this->assertDatabaseHas('aulas', $data); // Verifica se a aula foi inserida na base
    }

    #[Test]
    public function aula_deve_falhar_sem_ter_curso_vinculado()
    {
        $data = [
            'sequencia' => 1,
            'titulo' => 'Aula sem curso',
            'duracaoMinutos' => 30,
            'videoUrl' => 'https://www.exemplo.com/video',
            'curso_id' => null, // Curso não fornecido
        ];

        $response = $this->actingAs($this->userAdmin, 'api')->postJson('/api/aulas/create', $data);

        $response->assertStatus(422); // Falha na validação
        $response->assertJsonValidationErrors('curso_id'); // Verifica erro na validação do campo curso_id
    }

    #[Test]
    public function aula_deve_falhar_com_sequencial_repetido_no_mesmo_curso()
    {
        // Criação de uma aula inicial
        Aulas::create([
            'sequencia' => 1,
            'titulo' => 'Aula 1',
            'duracaoMinutos' => 30,
            'videoUrl' => 'https://www.exemplo.com/video',
            'curso_id' => $this->curso->id,
        ]);

        // Tentando criar uma aula com a mesma sequência
        $data = [
            'sequencia' => 1,
            'titulo' => 'Aula 2',
            'duracaoMinutos' => 40,
            'videoUrl' => 'https://www.exemplo.com/video2',
            'curso_id' => $this->curso->id,
        ];

        $response = $this->actingAs($this->userAdmin, 'api')->postJson('/api/aulas/create', $data);

        $response->assertStatus(422); // Falha na validação
        $response->assertJsonValidationErrors('sequencia'); // Verifica erro na validação do campo sequencia
    }

    #[Test]
    public function aula_deve_ser_atualizada_com_dados_validos()
    {
        // Criação de um curso e aula
        $aula = Aulas::create([
            'sequencia' => 1,
            'titulo' => 'Aula Original',
            'duracaoMinutos' => 30,
            'videoUrl' => 'https://www.exemplo.com/video',
            'curso_id' => $this->curso->id,
        ]);

        $data = [
            'sequencia' => 2,
            'titulo' => 'Aula Atualizada',
            'duracaoMinutos' => 45,
            'videoUrl' => 'https://www.exemplo.com/video-atualizado',
            'curso_id' => $this->curso->id,
        ];

        $response = $this->actingAs($this->userAdmin, 'api')->putJson("/api/aulas/update/{$aula->id}", $data);

        $response->assertStatus(200); // Atualizado com sucesso
        $this->assertDatabaseHas('aulas', $data); // Verifica se a aula foi atualizada na base
    }

    #[Test]
    public function apenas_administradores_podem_criar_aulas()
    {
        $data = [
            'sequencia' => 1,
            'titulo' => 'Aula Teste',
            'duracaoMinutos' => 30,
            'videoUrl' => 'https://www.exemplo.com/video',
            'curso_id' => $this->curso->id,
        ];

        $response = $this->actingAs($this->userDefault, 'api')->postJson('/api/aulas/create', $data);

        $response->assertStatus(403); // Acesso negado para usuário não admin
    }

    #[Test]
    public function apenas_administradores_podem_atualizar_aulas()
    {
        $aula = Aulas::create([
            'sequencia' => 1,
            'titulo' => 'Aula Teste',
            'duracaoMinutos' => 30,
            'videoUrl' => 'https://www.exemplo.com/video',
            'curso_id' => $this->curso->id,
        ]);

        $data = [
            'sequencia' => 2,
            'titulo' => 'Aula Atualizada',
            'duracaoMinutos' => 45,
            'videoUrl' => 'https://www.exemplo.com/video-atualizado',
            'curso_id' => $this->curso->id,
        ];

        $response = $this->actingAs($this->userDefault, 'api')->putJson("/api/aulas/update/{$aula->id}", $data);

        $response->assertStatus(403); // Acesso negado para usuário não admin
    }

    #[Test]
    public function apenas_administradores_podem_deletar_aulas()
    {
        $aula = Aulas::create([
            'sequencia' => 1,
            'titulo' => 'Aula Teste',
            'duracaoMinutos' => 30,
            'videoUrl' => 'https://www.exemplo.com/video',
            'curso_id' => $this->curso->id,
        ]);

        $response = $this->actingAs($this->userDefault, 'api')->deleteJson("/api/aulas/delete/{$aula->id}");

        $response->assertStatus(403); // Acesso negado para usuário não admin
    }
    #[Test]
    public function aula_deve_ser_deletada_com_sucesso()
    {
        $aula = Aulas::create([
            'sequencia' => 1,
            'titulo' => 'Aula para Deletar',
            'duracaoMinutos' => 30,
            'videoUrl' => 'https://www.exemplo.com/video',
            'curso_id' => $this->curso->id,
        ]);

        $response = $this->actingAs($this->userAdmin, 'api')->deleteJson("/api/aulas/delete/{$aula->id}");

        $response->assertStatus(200); // Deletado com sucesso
        $this->assertDatabaseMissing('aulas', ['id' => $aula->id]); // Verifica se a aula foi removida da base
    }

    #[Test]
    public function aula_deve_ser_exibida_com_sucesso()
    {
        // Criação de um curso e aula
        $aula = Aulas::create([
            'sequencia' => 1,
            'titulo' => 'Aula para Exibir',
            'duracaoMinutos' => 30,
            'videoUrl' => 'https://www.exemplo.com/video',
            'curso_id' => $this->curso->id,
        ]);

        $response = $this->getJson("/api/aulas/show/{$aula->id}");

        $response->assertStatus(200); // Exibido com sucesso
        $response->assertJsonFragment(['titulo' => 'Aula para Exibir']); // Verifica se o título está na resposta
    }

    #[Test]
    public function aula_deve_ser_listada_com_sucesso()
    {
        // Criação de um curso e aulas
        Aulas::factory(3)->create(['curso_id' => $this->curso->id]);

        $response = $this->getJson('/api/aulas');

        $response->assertStatus(200); // Listado com sucesso
        $response->assertJsonCount(3); // Verifica se 3 aulas foram retornadas
    }

    #[Test]
    public function aula_deve_ser_procurada_com_sucesso()
    {
        // Criação de um curso e aulas
        $curso = Cursos::factory()->create();
        Aulas::factory()->count(3)->create(['curso_id' => $curso->id]);

        $response = $this->getJson('/api/aulas/search/Aula');

        $response->assertStatus(200); // Procurado com sucesso
        $response->assertJsonCount(3); // Verifica se 3 aulas foram retornadas
    }

    #[Test]
    public function aula_deve_ser_marcada_como_visto()
    {
        // Criação de um curso e aula
        $aula = Aulas::create([
            'sequencia' => 1,
            'titulo' => 'Aula para Marcar Visto',
            'duracaoMinutos' => 30,
            'videoUrl' => 'https://www.exemplo.com/video',
            'curso_id' => $this->curso->id,
        ]);

        $response = $this->actingAs($this->userDefault, 'api')->patchJson("/api/aulas/{$aula->id}/visto");

        $response->assertStatus(200); // Marcado como visto com sucesso
        $this->assertDatabaseHas('aulas', ['id' => $aula->id, 'visto' => true]); // Verifica se a aula foi marcada como vista
    }

    // #[Test]
    // public function aula_deve_ser_marcada_como_nao_visto()
    // {
    //     // Criação de um curso e aula
    //     $curso = Cursos::factory()->create();
    //     $aula = Aulas::create([
    //         'sequencia' => 1,
    //         'titulo' => 'Aula para Marcar Não Visto',
    //         'duracaoMinutos' => 30,
    //         'videoUrl' => 'https://www.exemplo.com/video',
    //         'curso_id' => $curso->id,
    //     ]);

    //     $response = $this->patchJson("/api/aulas/{$aula->id}/visto");

    //     $response->assertStatus(200); // Marcado como não visto com sucesso
    //     $this->assertDatabaseMissing('aulas', ['id' => $aula->id, 'visto' => true]); // Verifica se a aula foi marcada como não vista
    // }
}
