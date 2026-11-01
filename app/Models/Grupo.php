<?php

namespace App\Models;

use App\Enums\Turno;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un grupo (salón) que cursa un semestre.
 */
#[Fillable(['semestre_id', 'nombre', 'turno'])]
class Grupo extends Model
{
    protected $table = 'grupos';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'turno' => Turno::class,
        ];
    }

    /**
     * @return BelongsTo<Semestre, $this>
     */
    public function semestre(): BelongsTo
    {
        return $this->belongsTo(Semestre::class);
    }
}
