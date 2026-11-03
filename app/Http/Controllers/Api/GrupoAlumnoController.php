<?php

namespace App\Http\Controllers\Api;

use App\Enums\EstadoGrupoAlumno;
use App\Enums\Rol;
use App\Http\Controllers\Controller;
use App\Http\Requests\Grupos\AgregarAlumnosRequest;
use App\Http\Requests\Grupos\CambiarEstadoAlumnoRequest;
use App\Http\Requests\Grupos\ConsultarAlumnosGrupoRequest;
use App\Http\Resources\GrupoAlumnoResource;
use App\Models\Grupo;
use App\Models\GrupoAlumno;
use App\Models\Usuario;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

#[Group('Grupos', weight: 4)]
class GrupoAlumnoController extends Controller
{
    public const ALUMNOS_POR_PAGINA = 15;

    /**
     * Listar los alumnos de un grupo
     *
     * Paginada de 15 en 15 y ordenada por nombre. Por defecto solo salen los
     * activos. Acepta en la URL: buscar (parte del número de cuenta o del
     * nombre) e incluir_bajas (para ver también a los dados de baja).
     */
    // * La paginación no la detecta sola la documentación, por eso se declara "page".
    #[QueryParameter('page', 'Número de página.', type: 'int', default: 1)]
    public function index(ConsultarAlumnosGrupoRequest $request, Grupo $grupo): AnonymousResourceCollection
    {
        // El join permite buscar y ordenar por los datos del alumno.
        $consulta = GrupoAlumno::query()
            ->join('usuarios', 'usuarios.id', '=', 'grupo_alumnos.alumno_id')
            ->select('grupo_alumnos.*')
            ->where('grupo_alumnos.grupo_id', $grupo->id)
            ->with('alumno');

        if (! $request->boolean('incluir_bajas')) {
            $consulta->activos();
        }

        if ($request->filled('buscar')) {
            $texto = '%'.$request->buscar.'%';

            $consulta->where(function ($condicion) use ($texto) {
                $condicion->whereLike('usuarios.numero_cuenta', $texto)
                    ->orWhereLike('usuarios.nombre', $texto);
            });
        }

        // El id desempata a los alumnos con el mismo nombre para que no cambien de página.
        $alumnos = $consulta->orderBy('usuarios.nombre')
            ->orderBy('grupo_alumnos.id')
            ->paginate(self::ALUMNOS_POR_PAGINA);

        // Los enlaces de las otras páginas conservan la búsqueda y el filtro.
        $alumnos->withQueryString();

        return GrupoAlumnoResource::collection($alumnos);
    }

    /**
     * Agregar alumnos a un grupo
     *
     * Recibe una lista de ids. Solo se agregan usuarios activos con rol alumno
     * que no estén activos en otro grupo; los demás se omiten sin detener al
     * resto. Un alumno dado de baja de este grupo vuelve a quedar activo.
     * Responde con los ids agregados y con los omitidos y su motivo.
     */
    public function store(AgregarAlumnosRequest $request, Grupo $grupo): JsonResponse
    {
        $agregados = [];
        $omitidos = [];

        foreach ($request->alumno_ids as $idRecibido) {
            // La validación también acepta "5" como texto; la respuesta siempre devuelve números.
            $alumnoId = (int) $idRecibido;
            $usuario = Usuario::find($alumnoId);

            $motivo = $this->motivoParaNoActivar($usuario, $grupo);

            if ($motivo !== null) {
                $omitidos[] = ['alumno_id' => $alumnoId, 'motivo' => $motivo];

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
            } else {
                $omitidos[] = ['alumno_id' => $alumnoId, 'motivo' => 'Ya está en este grupo.'];
            }
        }

        return response()->json([
            'message' => 'Alumnos procesados.',
            'agregados' => $agregados,
            'omitidos' => $omitidos,
        ]);
    }

    /**
     * Dar de baja o reingresar a un alumno del grupo
     *
     * estado en baja lo da de baja sin borrar nada; en activo lo reingresa, si
     * sigue siendo un alumno activo y no está activo en otro grupo. Si ya
     * estaba en ese estado no cambia nada. El alumno debe pertenecer al grupo.
     */
    public function cambiarEstado(CambiarEstadoAlumnoRequest $request, Grupo $grupo, Usuario $alumno): JsonResponse
    {
        $fila = GrupoAlumno::where('grupo_id', $grupo->id)
            ->where('alumno_id', $alumno->id)
            ->firstOrFail();

        $estadoNuevo = $request->enum('estado', EstadoGrupoAlumno::class);

        if ($estadoNuevo === EstadoGrupoAlumno::Baja) {
            // * Solo se marca una vez, para conservar la fecha de la primera baja.
            if ($fila->estado === EstadoGrupoAlumno::Activo) {
                $fila->update([
                    'estado' => EstadoGrupoAlumno::Baja,
                    'baja_en' => now(),
                ]);
            }

            $mensaje = 'Alumno dado de baja.';
        } else {
            if ($fila->estado === EstadoGrupoAlumno::Baja) {
                $motivo = $this->motivoParaNoActivar($alumno, $grupo);

                if ($motivo !== null) {
                    throw ValidationException::withMessages(['estado' => [$motivo]]);
                }

                $fila->update([
                    'estado' => EstadoGrupoAlumno::Activo,
                    'baja_en' => null,
                ]);
            }

            $mensaje = 'Alumno reingresado.';
        }

        return response()->json([
            'message' => $mensaje,
            'alumno' => new GrupoAlumnoResource($fila),
        ]);
    }

    /**
     * Dice por qué un usuario no puede quedar activo en el grupo, o null si sí puede.
     * Agregar y reingresar usan las mismas reglas.
     */
    private function motivoParaNoActivar(Usuario $usuario, Grupo $grupo): ?string
    {
        if ($usuario->rol !== Rol::Alumno) {
            return 'No tiene rol de alumno.';
        }

        if (! $usuario->activo) {
            return 'Su cuenta no está activa.';
        }

        // * Un alumno solo puede estar activo en un grupo a la vez.
        $filaEnOtroGrupo = GrupoAlumno::activos()
            ->where('alumno_id', $usuario->id)
            ->where('grupo_id', '!=', $grupo->id)
            ->first();

        if ($filaEnOtroGrupo !== null) {
            return 'Ya está activo en el grupo '.$filaEnOtroGrupo->grupo->nombre.'.';
        }

        return null;
    }
}
