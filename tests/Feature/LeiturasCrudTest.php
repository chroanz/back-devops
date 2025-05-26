<?php

namespace Tests\Feature;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Leitura;
use App\Models\Cursos;
use App\Models\UserFunction;

class LeiturasCrudTest extends TestCase
{

    use RefreshDatabase;

    private User $userDefault;
    private User $userAdmin;
    private Cursos $curso;

    public function setUp(): void
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

    use RefreshDatabase;

    #[Test]
    public function leitura_deve_ser_criada_com_dados_validos()
    {

        $response = $this->actingAs($this->userAdmin, 'api')->postJson('/api/leituras', [
            'titulo' => 'Leitura Para Teste',
            'conteudo' => 'Descrição da leitura',
            'sequencia' => 1,
            'curso_id' => $this->curso->id
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('leituras', ['titulo' => 'Leitura Para Teste']);
    }

    #[Test]
    public function leitura_deve_falhar_sem_vinculo_de_curso()
    {
        $this->actingAs($this->userAdmin, 'api');

        $response = $this->postJson('/api/leituras', [
            'titulo' => 'Leitura sem curso',
            'descricao' => 'Sem curso',
            'sequencia' => 1,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('curso_id');
    }

    #[Test]
    public function leitura_deve_falhar_com_sequencial_repetido_no_mesmo_curso()
    {
        $this->actingAs($this->userAdmin, 'api');

        Leitura::factory()->create([
            'curso_id' => $this->curso->id,
            'sequencia' => 1,
        ]);

        $response = $this->postJson('/api/leituras', [
            'titulo' => 'Titulo Duplicado',
            'conteudo' => 'Mesmo sequencial',
            'sequencia' => 1,
            'curso_id' => $this->curso->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('sequencia');
    }

    #[Test]
    public function apenas_administradores_podem_criar_leituras()
    {

        $response = $this->actingAs($this->userDefault, 'api')->postJson('/api/leituras', [
            'titulo' => 'Leitura de Teste',
            'conteudo' => 'Conteudo de teste de leitura',
            'sequencia' => 1,
            'curso_id' => $this->curso->id,
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function apenas_administradores_podem_atualizar_leituras()
    {
        $leitura = Leitura::factory()->create();

        $response = $this->actingAs($this->userDefault, 'api')->putJson("/api/leituras/{$leitura->id}", [
            'titulo' => 'Atualizado',
            'descricao' => 'Desc Atualizada',
            'sequencia' => 99,
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function apenas_administradores_podem_apagar_leituras()
    {
        $leitura = Leitura::factory()->create();
        $response = $this->actingAs($this->userDefault, 'api')->deleteJson("/api/leituras/{$leitura->id}");

        $response->assertStatus(403);
    }

    #[Test]
    public function leitura_deve_ser_atualizada_com_dados_validos()
    {
        $this->actingAs($this->userAdmin, 'api');

        $leitura = Leitura::factory()->create();

        $response = $this->putJson("/api/leituras/{$leitura->id}", [
            'titulo' => 'Atualizada',
            'descricao' => 'Nova descrição',
            'sequencia' => 2,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('leituras', ['id' => $leitura->id, 'titulo' => 'Atualizada']);
    }

    #[Test]
    public function leitura_deve_ser_deletada_com_sucesso()
    {
        $this->actingAs($this->userAdmin, 'api');

        $leitura = Leitura::factory()->create();

        $response = $this->deleteJson("/api/leituras/{$leitura->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('leituras', ['id' => $leitura->id]);
    }

    #[Test]
    public function conteudo_leitura_so_pode_ser_acessada_por_usuario_matriculado(){
        $leitura = Leitura::factory()->create();

        $curso_id = $leitura->curso->id;
        
        $this->actingAs($this->userDefault, 'api')->postJson("/api/cursos/subscribe/{$curso_id}");
        
        $response = $this->actingAs($this->userAdmin, 'api')->getJson("/api/leituras/{$leitura->id}");
        
        $response->assertStatus(403);
    }

    

}
