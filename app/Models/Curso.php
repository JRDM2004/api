<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Una materia impartida a un grupo. Sus alumnos son los alumnos activos del grupo.
 */
#[Fillable(['materia_id', 'grupo_id', 'docente_id'])]
class Curso extends Model
{
    protected $table = 'cursos';

    /**
     * @return BelongsTo<Materia, $this>
     */
    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class);
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
    public function docente(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'docente_id');
    }
}
