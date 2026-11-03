<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Grupos\AgregarAlumnosRequest;
use App\Models\Grupo;
use App\Models\GrupoAlumno;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;

#[Group('Grupos', weight: 4)]
class GrupoAlumnoController extends Controller
{
    /**
     * Agregar alumnos a un grupo
     *
     * Recibe una lista de ids de alumnos. Quien ya está en el grupo no se
     * vuelve a agregar.
     */
    public function store(AgregarAlumnosRequest $request, Grupo $grupo): JsonResponse
    {
        // TODO: HU-12 T4 devolver también los omitidos y el motivo.
        $agregados = [];

        foreach ($request->alumno_ids as $alumnoId) {
            // * firstOrCreate evita repetir al alumno: la tabla no permite dos filas iguales.
            $fila = GrupoAlumno::firstOrCreate([
                'grupo_id' => $grupo->id,
                'alumno_id' => $alumnoId,
            ]);

            if ($fila->wasRecentlyCreated) {
                $agregados[] = $alumnoId;
            }
        }

        return response()->json([
            'message' => 'Alumnos procesados.',
            'agregados' => $agregados,
        ]);
    }
}
