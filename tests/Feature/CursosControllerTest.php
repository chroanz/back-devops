<?php

namespace Tests\Feature;

use App\Models\Cursos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class CursosControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);
        $this->token = JWTAuth::fromUser($this->user);
    }

    public function test_index_lista_cursos()
    {
        $cursos = Cursos::factory(3)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/cursos');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_store_cria_curso()
    {
        $cursoData = [
            'titulo' => 'Curso Teste',
            'descricao' => 'Descrição teste',
            'categoria' => 'Categoria teste',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])
            ->postJson('/api/cursos/create', $cursoData);

        $response->assertStatus(201)
            ->assertJsonFragment($cursoData);
    }

    public function test_show_exibe_curso()
    {
        $curso = Cursos::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])
            ->getJson("/api/cursos/show/{$curso->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['titulo' => $curso->titulo]);
    }

    public function test_update_atualiza_curso()
    {
        $curso = Cursos::factory()->create();
        $updateData = ['titulo' => 'Novo Título'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])
            ->putJson("/api/cursos/update/{$curso->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment(['titulo' => 'Novo Título']);
    }

    public function test_delete_remove_curso()
    {
        $curso = Cursos::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])
            ->deleteJson("/api/cursos/delete/{$curso->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('cursos', ['id' => $curso->id]);
    }

    public function test_subscribe_matricula_usuario()
    {
        $curso = Cursos::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])
            ->postJson("/api/cursos/subscribe/{$curso->id}");

        $response->assertStatus(201)
            ->assertJsonFragment(['msg' => 'Matrícula realizada com sucesso']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson("/api/cursos/meus_cursos");

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }
}
