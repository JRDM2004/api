<?php

namespace Database\Factories;

use App\Enums\Rol;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Usuario>
 */
class UsuarioFactory extends Factory
{
    /**
     * NIP por defecto de los usuarios de prueba.
     */
    public const NIP = 'A1B2C3';

    protected static ?string $nipHash;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'numero_cuenta' => fake()->unique()->numerify('########'),
            'nombre' => fake()->name(),
            'correo' => fake()->unique()->safeEmail(),
            'nip_hash' => static::$nipHash ??= Hash::make(self::NIP),
            'rol' => Rol::Alumno,
            'activo' => true,
        ];
    }

    public function docente(): static
    {
        return $this->state(fn (array $attributes) => ['rol' => Rol::Docente]);
    }

    public function administrador(): static
    {
        return $this->state(fn (array $attributes) => ['rol' => Rol::Administrador]);
    }

    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => ['activo' => false]);
    }
}
