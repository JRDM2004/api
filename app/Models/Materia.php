<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Una materia del plan de estudios, dentro de su semestre.
 */
#[Fillable(['semestre_id', 'clave', 'nombre', 'activa'])]
class Materia extends Model
{
    protected $table = 'materias';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'activa' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Semestre, $this>
     */
    public function semestre(): BelongsTo
    {
        return $this->belongsTo(Semestre::class);
    }

    /**
     * @return HasMany<Curso, $this>
     */
    public function cursos(): HasMany
    {
        return $this->hasMany(Curso::class);
    }
}
