<?php

namespace Tests\Feature\Auth;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;


    private function logout(?string $token): TestResponse
    {
        $this->app['auth']->forgetGuards();

        return $token
            ? $this->withToken($token)->postJson('/api/logout')
            : $this->postJson('/api/logout');
    }

    public function test_cierra_sesion_y_revoca_el_token(): void
    {
        $token = Usuario::factory()->create()->createToken('api')->plainTextToken;

        $this->logout($token)
            ->assertOk()
            ->assertJsonPath('message', 'Sesión cerrada.');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_un_token_revocado_ya_no_da_acceso(): void
    {
        $token = Usuario::factory()->create()->createToken('api')->plainTextToken;

        $this->logout($token)->assertOk();

        $this->logout($token)
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Tu sesión no es válida o expiró. Inicia sesión de nuevo.');
    }

    public function test_no_cierra_la_sesion_de_otros_dispositivos(): void
    {
        $usuario = Usuario::factory()->create();
        $tokenCelular = $usuario->createToken('api')->plainTextToken;
        $tokenWeb = $usuario->createToken('api')->plainTextToken;

        $this->logout($tokenCelular)->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->logout($tokenWeb)->assertOk();
    }

    public function test_sin_token_responde_401(): void
    {
        $this->logout(null)->assertUnauthorized();
    }

    public function test_sin_encabezado_accept_responde_401_en_json(): void
    {
        $this->post('/api/logout')
            ->assertUnauthorized()
            ->assertJsonStructure(['message']);
    }

    public function test_un_token_vencido_responde_401(): void
    {
        $token = Usuario::factory()->create()
            ->createToken('api', ['*'], now()->subMinute())
            ->plainTextToken;

        $this->logout($token)->assertUnauthorized();
    }
}
