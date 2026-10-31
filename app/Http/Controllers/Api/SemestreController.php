<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Semestres\ConsultarSemestresRequest;
use App\Http\Resources\SemestreResource;
use App\Models\Semestre;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

#[Group('Materias', weight: 3)]
class SemestreController extends Controller
{
    /**
     * Listar semestres con sus materias
     *
     * Devuelve todos los semestres ordenados por número, cada uno con sus
     * materias ordenadas por nombre (activas e inactivas). Acepta en la URL:
     * buscar (parte del nombre o de la clave de la materia). La búsqueda solo
     * filtra las materias: los semestres salen siempre, aunque queden sin
     * materias.
     */
    public function index(ConsultarSemestresRequest $request): AnonymousResourceCollection
    {
        // * La búsqueda filtra las materias de cada semestre, no los semestres.
        $semestres = Semestre::query()
            ->with(['materias' => function ($consulta) use ($request) {
                if ($request->filled('buscar')) {
                    $texto = '%'.$request->buscar.'%';

                    $consulta->where(function ($grupo) use ($texto) {
                        $grupo->whereLike('nombre', $texto)
                            ->orWhereLike('clave', $texto);
                    });
                }

                $consulta->orderBy('nombre');
            }])
            ->orderBy('numero')
            ->get();

        return SemestreResource::collection($semestres);
    }
}
