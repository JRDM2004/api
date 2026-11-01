<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Materias\ActualizarMateriaRequest;
use App\Http\Requests\Materias\CambiarEstadoMateriaRequest;
use App\Http\Requests\Materias\RegistrarMateriaRequest;
use App\Http\Resources\MateriaResource;
use App\Models\Materia;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;

#[Group('Materias', weight: 3)]
class MateriaController extends Controller
{
    /**
     * Registrar una materia
     *
     * La materia queda activa dentro del semestre indicado. La clave no se
     * puede repetir.
     */
    public function store(RegistrarMateriaRequest $request): JsonResponse
    {
        $materia = Materia::create([
            'semestre_id' => $request->semestre_id,
            'clave' => $request->clave,
            'nombre' => $request->nombre,
            'activa' => true,
        ]);

        return response()->json([
            'message' => 'Materia registrada.',
            'materia' => new MateriaResource($materia),
        ], 201);
    }

    /**
     * Editar una materia
     *
     * Cambia semestre, clave y nombre. El estado no se cambia aquí: se usa
     * el endpoint de estado.
     */
    public function update(ActualizarMateriaRequest $request, Materia $materia): JsonResponse
    {
        $materia->update([
            'semestre_id' => $request->semestre_id,
            'clave' => $request->clave,
            'nombre' => $request->nombre,
        ]);

        return response()->json([
            'message' => 'Materia actualizada.',
            'materia' => new MateriaResource($materia),
        ]);
    }

    /**
     * Activar o desactivar una materia
     *
     * activa en true activa la materia y en false la desactiva. Las materias
     * no se borran: una desactivada no se usa en cursos nuevos, pero los
     * cursos que ya existen se conservan. El mensaje dice "Materia activada."
     * o "Materia desactivada.".
     */
    public function cambiarEstado(CambiarEstadoMateriaRequest $request, Materia $materia): JsonResponse
    {
        $activa = $request->boolean('activa');

        $materia->update(['activa' => $activa]);

        if ($activa) {
            $mensaje = 'Materia activada.';
        } else {
            $mensaje = 'Materia desactivada.';
        }

        return response()->json([
            'message' => $mensaje,
            'materia' => new MateriaResource($materia),
        ]);
    }
}
