<?php

namespace App\Models;

use App\Enums\EstadoGrupoAlumno;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * La pertenencia de un alumno a un grupo, activa o dada de baja.
 */
#[Fillable(['grupo_id', 'alumno_id', 'estado', 'baja_en'])]
class GrupoAlumno extends Model
{
    protected $table = 'grupo_alumnos';

    // Igual que el valor por defecto de la columna, para tenerlo también antes de recargar.
    protected $attributes = [
        'estado' => 'activo',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'estado' => EstadoGrupoAlumno::class,
            'baja_en' => 'datetime',
        ];
    }

    /**
     * Deja solo a los alumnos activos. Toda consulta de "los alumnos del grupo"
     * debe usarlo, para que los dados de baja no salgan ni entren a los cursos.
     *
     * @param  Builder<GrupoAlumno>  $query
     */
    public function scopeActivos(Builder $query): void
    {
        // Con el nombre de la tabla sigue funcionando cuando la consulta lleva un join.
        $query->where('grupo_alumnos.estado', EstadoGrupoAlumno::Activo);
    }

    /**
     * @return BelongsTo<Grupo, $this>
     */
    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
    }

    /**
     * @return BelongsTo<Usuario, $this>
     */
    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'alumno_id');
    }
}
