# Importar usuarios desde un archivo CSV

Guía para el administrador y para el equipo web (pantalla de importación, HU-03a).

## Plantilla

Descárgala desde la API:

```
GET /plantillas/plantilla_usuarios.csv
```

## Instrucciones para el administrador

Este texto se puede mostrar tal cual en la pantalla de importación.

1. Descarga la plantilla y ábrela en Excel.
2. Llena una fila por usuario. No cambies los encabezados.
3. Guarda el archivo como **CSV UTF-8 (delimitado por comas)**.
4. Súbelo aquí. Puede pesar hasta 2 MB.
5. Espera el reporte: verás cuántos usuarios se crearon, cuántos ya existían y
   qué filas tienen error.
6. Si hay errores, corrige esas filas y vuelve a subir el archivo completo.
   Los usuarios que ya se crearon no se duplican.
7. Al terminar, **borra el archivo** de tu computadora y de la memoria USB:
   contiene los NIP.

| Columna | Obligatoria | Qué poner |
|---|---|---|
| `numero_cuenta` | Sí | Hasta 20 caracteres. No se puede repetir. |
| `nombre` | Sí | Nombre completo, hasta 100 caracteres. |
| `rol` | Sí | `alumno`, `docente` o `administrador`. |
| `nip` | Sí | De 4 a 6 letras o números, sin espacios ni símbolos. |
| `correo` | No | Correo válido. Puede ir vacío. |

Ejemplo:

```csv
numero_cuenta,nombre,rol,nip,correo
20990001,Ana López Hernández,alumno,AB12,a.lopez@info.uas.edu.mx
20990002,José Luis Pérez Martínez,docente,XY99,
```

## Endpoints para el equipo web

Todos piden el token del administrador (`Authorization: Bearer ...`).

### Subir el archivo

```
POST /api/usuarios/importaciones
Content-Type: multipart/form-data
archivo: (el archivo .csv)
```

Respuesta `202`:

```json
{ "id": "6b9efd6f-6fe9-4210-847d-9a99846748b4", "estado": "en_proceso" }
```

Errores: `401` sin sesión, `403` si no es administrador y `422` si falta el
archivo, no es `.csv` o pesa más de 2 MB.

### Consultar el estado

```
GET /api/usuarios/importaciones/{id}
```

Consúltalo cada pocos segundos hasta que el estado deje de ser `en_proceso`.

Terminada (`200`):

```json
{
  "id": "6b9efd6f-6fe9-4210-847d-9a99846748b4",
  "estado": "terminada",
  "creados": 1,
  "omitidos": 0,
  "errores": ["Línea 3: El rol debe ser alumno, docente o administrador."]
}
```

Fallida (`200`):

```json
{
  "id": "6b9efd6f-6fe9-4210-847d-9a99846748b4",
  "estado": "fallida",
  "mensaje": "No se pudo procesar el archivo. Intenta subirlo de nuevo."
}
```

Si el `id` no existe o ya pasaron 24 horas, responde `404`.

Tiempo aproximado: unos 50 segundos por cada 300 usuarios.
