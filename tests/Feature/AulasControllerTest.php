<?php

namespace Tests\Feature;

use Tymon\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\UserFunction;
use App\Models\User;
use App\Models\Cursos;
use App\Models\Aulas;

class AulasControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Cursos $curso;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);
        $this->token = JWTAuth::fromUser($this->user);
        $functionAdmin = UserFunction::create(['user_id' => $this->user->id, 'function' => 'admin']);
        $this->user->functions()->save($functionAdmin);
        $this->curso = Cursos::factory()->create();
    }

    public function test_index_lista_aulas()
    {
        Aulas::factory(3)->create(['curso_id' => $this->curso->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])
            ->getJson('/api/aulas');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_store_cria_aula()
    {
        $aulaData = [
            'sequencia' => 1,
            'titulo' => 'Aula Teste',
            'duracaoMinutos' => 60,
            'videoUrl' => 'https://exemplo.com/video',
            'curso_id' => $this->curso->id
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])
            ->postJson('/api/aulas/create', $aulaData);

        $response->assertStatus(201)
            ->assertJsonPath('aula.titulo', 'Aula Teste');
    }

    public function test_show_exibe_aula()
    {
        $aula = Aulas::factory()->create(['curso_id' => $this->curso->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])
            ->getJson("/api/aulas/show/{$aula->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['titulo' => $aula->titulo]);
    }

    public function test_marcar_visto()
    {
        $aula = Aulas::factory()->create(['curso_id' => $this->curso->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])
            ->patchJson("/api/aulas/{$aula->id}/visto");

        $response->assertStatus(200)
            ->assertJsonFragment(['msg' => 'Aula marcada como vista.']);
    }
}
