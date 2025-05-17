<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function usuario_pode_ser_criado_com_dados_validos()
    {
        $response = $this->postJson('/api/user', [
            'name' => 'Lucas',
            'email' => 'lucas@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'email' => 'lucas@example.com',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function nao_pode_criar_usuario_com_email_ja_existente()
    {
        User::factory()->create([
            'email' => 'lucas@example.com',
        ]);

        $response = $this->postJson('/api/user', [
            'name' => 'Outro Lucas',
            'email' => 'lucas@example.com', // email duplicado
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

  #[\PHPUnit\Framework\Attributes\Test]
    public function listar_todos_os_usuarios_padrao()
    {
        $usuarios = User::factory()
            ->count(3)
            ->create();

        foreach ($usuarios as $usuario) {
            $usuario->functions()->create([
                'function' => 'default',
            ]);
        }

        $response = $this->getJson('/api/user');

        $response->assertStatus(200);

        $response->assertJsonCount(3);

        foreach ($usuarios as $usuario) {
            $response->assertJsonFragment([
                'id' => $usuario->id,
                'name' => $usuario->name,
                'email' => $usuario->email,
            ]);
        }
    }

   

    #[\PHPUnit\Framework\Attributes\Test]
    public function ver_detalhes_de_um_usuario()
    {
        $usuario = User::factory()->create();

        $response = $this->getJson("/api/user/{$usuario->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $usuario->id,
            'name' => $usuario->name,
            'email' => $usuario->email,
        ]);
    }


    #[\PHPUnit\Framework\Attributes\Test]
    public function usuario_pode_ser_atualizado_com_dados_validos(): void
    {
        $usuario = User::factory()->create();

        $response = $this->putJson("/api/user/{$usuario->id}", [
            'name' => 'Lucas Atualizado',
            'email' => 'lucasatualizado@example.com',
            'password' => 'novasenha123',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $usuario->id,
            'name' => 'Lucas Atualizado',
            'email' => 'lucasatualizado@example.com',
        ]);
    }
    #[\PHPUnit\Framework\Attributes\Test]
    public function nao_pode_atualizar_usuario_com_email_ja_existente(): void
    {
        $usuario1 = User::factory()->create([
            'email' => 'email1@example.com',
        ]);

        $usuario2 = User::factory()->create([
            'email' => 'email2@example.com',
        ]);

        $response = $this->putJson("/api/user/{$usuario2->id}", [
            'name' => 'Novo Nome',
            'email' => 'email1@example.com', // email já existente
            'password' => 'novasenha123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }



    #[\PHPUnit\Framework\Attributes\Test]
    public function usuario_pode_ser_deletado()
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/user/{$user->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function nao_deve_deletar_usuario_inexistente()
    {
        $response = $this->deleteJson("/api/user/999999");

        $response->assertStatus(404);
    }

    #[\PHPUnit\Framework\Attributes\Test]

    public function test_mostrar_dados_usuario_autenticado()
    {
        $user = User::factory()->create();

        $token = auth('api')->login($user);

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->getJson('/api/user/me');

        $response->assertStatus(200)
                ->assertJson([
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name,
                ]);
    }


    #[\PHPUnit\Framework\Attributes\Test]

    public function test_nao_criar_usuario_com_campos_obrigatorios_vazios()
    {
        $response = $this->postJson('/api/user', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'email', 'password']);
    }




}
