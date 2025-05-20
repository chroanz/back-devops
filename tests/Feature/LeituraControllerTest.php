<?php

namespace Tests\Feature;

use App\Models\Leitura;
use App\Models\Cursos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class LeituraControllerTest extends TestCase
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
        $this->user->functions()->create(['function' => 'admin']);
        $this->token = JWTAuth::fromUser($this->user);
        $this->curso = Cursos::factory()->create();
    }

    public function test_index_lista_leituras()
    {
        Leitura::factory(3)->create(['curso_id' => $this->curso->id]);

        $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->token
            ])->getJson("/api/leituras?curso_id={$this->curso->id}");

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_store_cria_leitura()
    {
        $leituraData = [
            'curso_id' => $this->curso->id,
            'sequencia' => 1,
            'titulo' => 'Leitura Teste',
            'conteudo' => 'Conteúdo teste'
        ];

        $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->token
            ])->postJson('/api/leituras', $leituraData);

        $response->assertStatus(201)
            ->assertJsonFragment(['titulo' => 'Leitura Teste']);
    }

    public function test_show_exibe_leitura()
    {
        $leitura = Leitura::factory()->create(['curso_id' => $this->curso->id]);

        $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->token
            ])->getJson("/api/leituras/{$leitura->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['titulo' => $leitura->titulo]);
    }

    public function test_marcar_visto()
    {
        $leitura = Leitura::factory()->create(['curso_id' => $this->curso->id]);

        $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->token
            ])->patchJson("/api/leituras/{$leitura->id}/visto");

        $response->assertStatus(200)
            ->assertJsonFragment(['msg' => 'Leitura marcada como vista.']);
    }
}
