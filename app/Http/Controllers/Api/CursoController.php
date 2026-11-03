<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cursos\RegistrarCursoRequest;
use App\Http\Resources\CursoResource;
use App\Models\Curso;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;

#[Group('Cursos', weight: 5)]
class CursoController extends Controller
{
    /**
     * Registrar un curso
     *
     * Une una materia con un grupo. Una materia solo puede tener un curso por
     * grupo. El docente es opcional. El nombre y la clave salen de la materia.
     */
    public function store(RegistrarCursoRequest $request): JsonResponse
    {
        $curso = Curso::create([
            'materia_id' => $request->materia_id,
            'grupo_id' => $request->grupo_id,
            'docente_id' => $request->docente_id,
        ]);

        return response()->json([
            'message' => 'Curso registrado.',
            'curso' => new CursoResource($curso),
        ], 201);
    }
}
