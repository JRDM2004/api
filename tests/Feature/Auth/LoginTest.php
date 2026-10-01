<?php

namespace Tests\Feature\Auth;

use App\Enums\Rol;
use App\Models\Usuario;
use Database\Factories\UsuarioFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public static function roles(): array
    {
        return array_map(fn (Rol $rol) => [$rol], Rol::cases());
    }

    #[DataProvider('roles')]
    public function test_inicia_sesion_con_credenciales_validas(Rol $rol): void
    {
        $usuario = Usuario::factory()->create(['rol' => $rol]);

        $respuesta = $this->postJson('/api/login', [
            'numero_cuenta' => $usuario->numero_cuenta,
            'nip' => UsuarioFactory::NIP,
        ]);

        $respuesta->assertOk()
            ->assertJsonStructure(['token', 'token_type', 'expires_at', 'user' => ['id', 'numero_cuenta', 'nombre', 'rol']])
            ->assertJsonPath('user.rol', $rol->value)
            ->assertJsonMissingPath('user.nip_hash');

        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_acepta_el_nip_en_minusculas(): void
    {
        $usuario = Usuario::factory()->create();

        $this->postJson('/api/login', [
            'numero_cuenta' => $usuario->numero_cuenta,
            'nip' => strtolower(UsuarioFactory::NIP),
        ])->assertOk();
    }

    public function test_nip_incorrecto_y_cuenta_inexistente_dan_el_mismo_mensaje(): void
    {
        $usuario = Usuario::factory()->create();

        $this->postJson('/api/login', [
            'numero_cuenta' => $usuario->numero_cuenta,
            'nip' => 'FFFFFF',
        ])->assertUnauthorized()
            ->assertJsonPath('message', 'Número de cuenta o NIP incorrectos.');

        $this->postJson('/api/login', [
            'numero_cuenta' => '99999999',
            'nip' => UsuarioFactory::NIP,
        ])->assertUnauthorized()
            ->assertJsonPath('message', 'Número de cuenta o NIP incorrectos.');
    }

    public function test_usuario_inactivo_no_puede_iniciar_sesion(): void
    {
        $usuario = Usuario::factory()->inactivo()->create();

        $this->postJson('/api/login', [
            'numero_cuenta' => $usuario->numero_cuenta,
            'nip' => UsuarioFactory::NIP,
        ])->assertForbidden()
            ->assertJsonPath('message', 'Tu cuenta está desactivada. Contacta a soporte.');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_valida_los_campos_obligatorios(): void
    {
        $this->postJson('/api/login', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['numero_cuenta', 'nip']);
    }
}
