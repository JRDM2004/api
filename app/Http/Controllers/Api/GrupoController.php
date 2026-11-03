<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Grupos\ActualizarGrupoRequest;
use App\Http\Requests\Grupos\ConsultarGruposRequest;
use App\Http\Requests\Grupos\RegistrarGrupoRequest;
use App\Http\Resources\GrupoResource;
use App\Models\Grupo;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

#[Group('Grupos', weight: 4)]
class GrupoController extends Controller
{
    /**
     * Listar grupos
     *
     * Devuelve todos los grupos ordenados por nombre, cada uno con su
     * semestre y cuántos alumnos activos tiene. Acepta en la URL: semestre_id
     * (para ver solo los grupos de ese semestre).
     */
    public function index(ConsultarGruposRequest $request): AnonymousResourceCollection
    {
        // TODO: HU-13 agregar el conteo de cursos de cada grupo.
        // * Los dados de baja no cuentan.
        $consulta = Grupo::query()
            ->with('semestre')
            ->withCount(['grupoAlumnos as alumnos_activos_count' => function ($filas) {
                $filas->activos();
            }]);

        if ($request->filled('semestre_id')) {
            $consulta->where('semestre_id', $request->semestre_id);
        }

        $grupos = $consulta->orderBy('nombre')->get();

        return GrupoResource::collection($grupos);
    }

    /**
     * Registrar un grupo
     *
     * El nombre (por ejemplo 3-2) no se puede repetir. El turno es opcional.
     */
    public function store(RegistrarGrupoRequest $request): JsonResponse
    {
        $grupo = Grupo::create([
            'semestre_id' => $request->semestre_id,
            'nombre' => $request->nombre,
            'turno' => $request->turno,
        ]);

        return response()->json([
            'message' => 'Grupo registrado.',
            'grupo' => new GrupoResource($grupo),
        ], 201);
    }

    /**
     * Editar un grupo
     *
     * Cambia semestre, nombre y turno. Mandar el turno en null lo quita. Un
     * grupo con cursos no puede cambiar de semestre.
     */
    public function update(ActualizarGrupoRequest $request, Grupo $grupo): JsonResponse
    {
        $grupo->update([
            'semestre_id' => $request->semestre_id,
            'nombre' => $request->nombre,
            'turno' => $request->turno,
        ]);

        return response()->json([
            'message' => 'Grupo actualizado.',
            'grupo' => new GrupoResource($grupo),
        ]);
    }
}
