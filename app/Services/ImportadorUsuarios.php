<?php

namespace App\Services;

use App\Enums\Rol;
use App\Models\Usuario;
use App\Rules\Nip;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;

/**
 * Importa usuarios desde un archivo CSV con las columnas
 * numero_cuenta, nombre, rol, nip y correo (opcional).
 *
 * Una fila con error no detiene la importación, y los números de cuenta que
 * ya existen se omiten, así que el mismo archivo se puede procesar varias veces.
 */
class ImportadorUsuarios
{
    public const ENCABEZADOS = ['numero_cuenta', 'nombre', 'rol', 'nip', 'correo'];

    private array $reporte;

    /**
     * Devuelve el reporte: creados, omitidos y errores (con su número de línea).
     */
    public function importar(string $ruta): array
    {
        if (! is_readable($ruta)) {
            throw new RuntimeException('No se puede leer el archivo de importación.');
        }

        $this->reporte = ['creados' => 0, 'omitidos' => 0, 'errores' => []];

        $archivo = fopen($ruta, 'r');

        // * escape: '' sigue el estándar CSV (RFC 4180), que es como Excel escapa las comillas: ""
        $encabezados = fgetcsv($archivo, escape: '');

        if (! $this->encabezadosValidos($encabezados)) {
            $this->reporte['errores'][] = 'Línea 1: el encabezado debe ser '.implode(',', self::ENCABEZADOS).'.';
            fclose($archivo);

            return $this->reporte;
        }

        $linea = 1;

        while (($fila = fgetcsv($archivo, escape: '')) !== false) {
            $linea++;

            // Excel suele dejar renglones vacíos al final del archivo.
            if ($fila === [null]) {
                continue;
            }

            $this->importarFila($fila, $linea);
        }

        fclose($archivo);

        return $this->reporte;
    }

    private function encabezadosValidos(array|false $encabezados): bool
    {
        if ($encabezados === false || $encabezados === [null]) {
            return false;
        }

        // Excel agrega una marca invisible (BOM) al inicio de los CSV en UTF-8.
        $encabezados[0] = preg_replace('/^\xEF\xBB\xBF/', '', $encabezados[0]);

        return array_map('trim', $encabezados) === self::ENCABEZADOS;
    }

    private function importarFila(array $fila, int $linea): void
    {
        if (count($fila) !== count(self::ENCABEZADOS)) {
            $this->reporte['errores'][] = "Línea {$linea}: número de columnas incorrecto.";

            return;
        }

        $datos = array_combine(self::ENCABEZADOS, array_map('trim', $fila));

        // * Se revisa antes de validar: una cuenta existente se omite aunque su fila tenga otro error
        if (Usuario::where('numero_cuenta', $datos['numero_cuenta'])->exists()) {
            $this->reporte['omitidos']++;

            return;
        }

        $validador = Validator::make($datos, [
            'numero_cuenta' => ['required', 'string', 'max:20'],
            'nombre' => ['required', 'string', 'max:100'],
            'rol' => ['required', Rule::enum(Rol::class)],
            'nip' => ['required', new Nip],
            'correo' => ['nullable', 'email', 'max:150'],
        ], [
            'required' => 'Falta el campo :attribute.',
            'max' => 'El campo :attribute supera :max caracteres.',
            'rol.enum' => 'El rol debe ser alumno, docente o administrador.',
            'correo.email' => 'El correo no es válido.',
        ]);

        if ($validador->fails()) {
            $this->reporte['errores'][] = "Línea {$linea}: ".implode(' ', $validador->errors()->all());

            return;
        }

        Usuario::create([
            'numero_cuenta' => $datos['numero_cuenta'],
            'nombre' => $datos['nombre'],
            'rol' => $datos['rol'],
            'nip_hash' => $datos['nip'],
            'correo' => $datos['correo'] ?: null,
        ]);

        $this->reporte['creados']++;
    }
}
