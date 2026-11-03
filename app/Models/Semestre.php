<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Un semestre del plan de estudios (del 1 al 8).
 */
#[Fillable(['numero', 'nombre'])]
class Semestre extends Model
{
    protected $table = 'semestres';

    // Vienen precargados y no cambian, así que no llevan created_at ni updated_at.
    public $timestamps = false;

    /**
     * @return HasMany<Materia, $this>
     */
    public function materias(): HasMany
    {
        return $this->hasMany(Materia::class);
    }

    /**
     * @return HasMany<Grupo, $this>
     */
    public function grupos(): HasMany
    {
        return $this->hasMany(Grupo::class);
    }
}
