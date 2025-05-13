<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Curso;
use App\Models\Leitura;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeiturasCrudTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function leitura_deve_ser_criada_com_dados_validos()
    {
        $this->actingAs(User::factory()->admin()->create(), 'sanctum');

        $curso = Curso::factory()->create();

        $response = $this->postJson('/api/leituras', [
            'titulo' => 'Leitura A',
            'descricao' => 'Descrição da leitura',
            'sequencia' => 1,
            'curso_id' => $curso->id
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('leituras', ['titulo' => 'Leitura A']);
    }

    /** @test */
    public function leitura_deve_falhar_sem_vinculo_de_curso()
    {
        $this->actingAs(User::factory()->admin()->create(), 'sanctum');

        $response = $this->postJson('/api/leituras', [
            'titulo' => 'Leitura sem curso',
            'descricao' => 'Sem curso',
            'sequencia' => 1,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('curso_id');
    }

    /** @test */
    public function leitura_deve_falhar_com_sequencial_repetido_no_mesmo_curso()
    {
        $this->actingAs(User::factory()->admin()->create(), 'sanctum');

        $curso = Curso::factory()->create();

        Leitura::factory()->create([
            'curso_id' => $curso->id,
            'sequencia' => 1,
        ]);

        $response = $this->postJson('/api/leituras', [
            'titulo' => 'Duplicado',
            'descricao' => 'Mesmo sequencial',
            'sequencia' => 1,
            'curso_id' => $curso->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('sequencia');
    }

    /** @test */
    public function apenas_administradores_podem_criar_leituras()
    {
        $user = User::factory()->create(); // Não admin
        $curso = Curso::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/leituras', [
            'titulo' => 'Leitura de Teste',
            'descricao' => 'Teste',
            'sequencia' => 1,
            'curso_id' => $curso->id,
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function apenas_administradores_podem_atualizar_leituras()
    {
        $user = User::factory()->create(); // Não admin
        $leitura = Leitura::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->putJson("/api/leituras/{$leitura->id}", [
            'titulo' => 'Atualizado',
            'descricao' => 'Desc Atualizada',
            'sequencia' => 99,
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function apenas_administradores_podem_apagar_leituras()
    {
        $user = User::factory()->create(); // Não admin
        $leitura = Leitura::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/leituras/{$leitura->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function leitura_deve_ser_atualizada_com_dados_validos()
    {
        $this->actingAs(User::factory()->admin()->create(), 'sanctum');

        $leitura = Leitura::factory()->create();

        $response = $this->putJson("/api/leituras/{$leitura->id}", [
            'titulo' => 'Atualizada',
            'descricao' => 'Nova descrição',
            'sequencia' => 2,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('leituras', ['id' => $leitura->id, 'titulo' => 'Atualizada']);
    }

    /** @test */
    public function leitura_deve_ser_deletada_com_sucesso()
    {
        $this->actingAs(User::factory()->admin()->create(), 'sanctum');

        $leitura = Leitura::factory()->create();

        $response = $this->deleteJson("/api/leituras/{$leitura->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('leituras', ['id' => $leitura->id]);
    }
}