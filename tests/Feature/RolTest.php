<?php

namespace Tests\Feature;

use App\Enums\Rol;
use App\Models\Usuario;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RolTest extends TestCase
{
    use RefreshDatabase;

    public static function roles(): array
    {
        return array_map(fn (Rol $rol) => [$rol], Rol::cases());
    }

    /**
     * Falla si se agrega un rol al enum sin una migración que lo permita en
     * la columna usuarios.rol.
     */
    #[DataProvider('roles')]
    public function test_la_columna_rol_acepta_cada_rol_del_enum(Rol $rol): void
    {
        $usuario = Usuario::factory()->create(['rol' => $rol]);

        $this->assertSame($rol, $usuario->fresh()->rol);
    }

    public function test_la_columna_rol_rechaza_un_rol_que_no_existe(): void
    {
        $usuario = Usuario::factory()->create();

        $this->expectException(QueryException::class);

        DB::table('usuarios')->where('id', $usuario->id)->update(['rol' => 'coordinador']);
    }
}
