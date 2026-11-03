<?php

namespace App\Http\Controllers\Api;

use App\Enums\EstadoGrupoAlumno;
use App\Enums\Rol;
use App\Http\Controllers\Controller;
use App\Http\Requests\Grupos\AgregarAlumnosRequest;
use App\Models\Grupo;
use App\Models\GrupoAlumno;
use App\Models\Usuario;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;

#[Group('Grupos', weight: 4)]
class GrupoAlumnoController extends Controller
{
    /**
     * Agregar alumnos a un grupo
     *
     * Recibe una lista de ids. Solo se agregan usuarios activos con rol alumno
     * que no estén activos en otro grupo; los demás se omiten sin detener al
     * resto. Un alumno dado de baja de este grupo vuelve a quedar activo.
     */
    public function store(AgregarAlumnosRequest $request, Grupo $grupo): JsonResponse
    {
        // TODO: HU-12 T4 devolver también los omitidos y el motivo.
        $agregados = [];

        foreach ($request->alumno_ids as $alumnoId) {
            $usuario = Usuario::find($alumnoId);

            if ($usuario->rol !== Rol::Alumno || ! $usuario->activo) {
                continue;
            }

            // * Un alumno solo puede estar activo en un grupo a la vez.
            $activoEnOtroGrupo = GrupoAlumno::where('alumno_id', $alumnoId)
                ->where('estado', EstadoGrupoAlumno::Activo)
                ->where('grupo_id', '!=', $grupo->id)
                ->exists();

            if ($activoEnOtroGrupo) {
                continue;
            }

            $fila = GrupoAlumno::where('grupo_id', $grupo->id)
                ->where('alumno_id', $alumnoId)
                ->first();

            if ($fila === null) {
                GrupoAlumno::create([
                    'grupo_id' => $grupo->id,
                    'alumno_id' => $alumnoId,
                ]);
                $agregados[] = $alumnoId;
            } elseif ($fila->estado === EstadoGrupoAlumno::Baja) {
                // * Reingreso: se reusa su fila porque la tabla no permite dos iguales.
                $fila->update([
                    'estado' => EstadoGrupoAlumno::Activo,
                    'baja_en' => null,
                ]);
                $agregados[] = $alumnoId;
            }
        }

        return response()->json([
            'message' => 'Alumnos procesados.',
            'agregados' => $agregados,
        ]);
    }
}
