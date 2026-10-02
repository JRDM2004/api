<?php

namespace Database\Seeders;

use App\Enums\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    /**
     * Usuarios de prueba: uno por rol y uno inactivo.
     */
    public function run(): void
    {
        $usuarios = [
            ['numero_cuenta' => '00000001', 'nombre' => 'Administrador de prueba', 'rol' => Rol::Administrador, 'nip' => 'ADF001', 'activo' => true],
            ['numero_cuenta' => '00000002', 'nombre' => 'Docente de prueba', 'rol' => Rol::Docente, 'nip' => 'D0CE01', 'activo' => true],
            ['numero_cuenta' => '00000003', 'nombre' => 'Alumno de prueba', 'rol' => Rol::Alumno, 'nip' => 'A1B2C3', 'activo' => true],
            ['numero_cuenta' => '00000004', 'nombre' => 'Alumno Inactivo', 'rol' => Rol::Alumno, 'nip' => 'A1B2C3', 'activo' => false],
        ];

        foreach ($usuarios as $datos) {
            Usuario::updateOrCreate(
                ['numero_cuenta' => $datos['numero_cuenta']],
                [
                    'nombre' => $datos['nombre'],
                    'rol' => $datos['rol'],
                    'nip_hash' => Hash::make($datos['nip']),
                    'activo' => $datos['activo'],
                ],
            );
        }
    }
}
