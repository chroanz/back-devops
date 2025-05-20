<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Cursos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CursosControllerTest extends TestCase
{
    use RefreshDatabase;
    
    #[\PHPUnit\Framework\Attributes\Test]
    public function usuario_autenticado_pode_ver_um_curso_completo()
    {
        $usuario = User::factory()->create();
        $curso = Cursos::factory()->create();

        $response = $this->actingAs($usuario, 'api')->getJson("/api/cursos/show/{$curso->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $curso->id]);
    }
    public function usuario_autenticado_pode_cadastrar_novo_curso()
    {
        Storage::fake('s3');
        $usuario = User::factory()->create();
        $usuario->functions()->create(['function' => 'admin']);

        $base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8Xw8AApMBgRL9JP0AAAAASUVORK5CYII=';

        $dados = [
            'titulo' => 'Curso Teste',
            'descricao' => 'Descrição do curso teste',
            'categoria' => 'Categoria X',
            'capa' => $base64Image,
        ];

        $response = $this->actingAs($usuario, 'api')->postJson('/api/cursos/create', $dados);

        $response->assertStatus(201);

        $this->assertDatabaseHas('cursos', ['titulo' => 'Curso Teste']);

        $curso = $response->json();

        $this->assertArrayHasKey('capa', $curso);
        $this->assertArrayHasKey('capa_url', $curso);
        $this->assertArrayHasKey('capa_expiration', $curso);

        Storage::disk('s3')->assertExists($curso['capa']);
    }


    #[\PHPUnit\Framework\Attributes\Test]
    public function usuario_autenticado_pode_atualizar_um_curso()
    {
        $usuario = User::factory()->create();
        // Criar função admin para o usuário
        $usuario->functions()->create(['function' => 'admin']);

        $curso = Cursos::factory()->create([
            'titulo' => 'Curso Antigo'
        ]);

        $response = $this->actingAs($usuario, 'api')->putJson("/api/cursos/update/{$curso->id}", [
            'titulo' => 'Curso Atualizado'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('cursos', ['titulo' => 'Curso Atualizado']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function usuario_autenticado_pode_deletar_um_curso()
    {
        $usuario = User::factory()->create();
        // Criar função admin para o usuário
        $usuario->functions()->create(['function' => 'admin']);

        $curso = Cursos::factory()->create();

        $response = $this->actingAs($usuario, 'api')->deleteJson("/api/cursos/delete/{$curso->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('cursos', ['id' => $curso->id]);
    }


    #[\PHPUnit\Framework\Attributes\Test]
    public function usuario_pode_se_matricular_em_um_curso()
    {
        $usuario = User::factory()->create();
        $curso = Cursos::factory()->create();

        $response = $this->actingAs($usuario, 'api')->postJson("/api/cursos/subscribe/{$curso->id}");

        $response->assertStatus(201);
        $response->assertJsonFragment(['msg' => 'Matrícula realizada com sucesso']);
        $this->assertTrue($usuario->cursos()->where('cursos_id', $curso->id)->exists());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function usuario_pode_listar_seus_cursos_matriculados()
    {
        $usuario = User::factory()->create();
        $curso = Cursos::factory()->create();
        $usuario->cursos()->attach($curso->id);

        $response = $this->actingAs($usuario, 'api')->getJson("/api/cursos/meus_cursos");

        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $curso->id]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function usuario_pode_buscar_cursos_por_palavra_chave()
    {
        $usuario = User::factory()->create();
        Cursos::factory()->create(['titulo' => 'Curso de Laravel']);

        $response = $this->actingAs($usuario, 'api')->getJson('/api/cursos/search/Laravel');

        $response->assertStatus(200);
        $response->assertJsonFragment(['titulo' => 'Curso de Laravel']);
    }

}
