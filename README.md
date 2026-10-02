<p align="center">
  <img src="public/logo-aula-uas.png" width="40%" alt="Logo de Aula UAS">
</p>

# Aula UAS — API del LMS de la Facultad de Informática Culiacán

API REST del sistema de gestión del aprendizaje (LMS) de la Facultad de Informática Culiacán de la UAS. Atiende a la aplicación web y a la aplicación móvil: inicio de sesión por número de cuenta y NIP, usuarios, cursos, contenido, asistencia, tareas, calificaciones y exámenes.

## Tabla de contenidos

1. [Tecnologías empleadas](#tecnologías-empleadas)
2. [Manual de instalación](#manual-de-instalación)
   - [Requisitos](#requisitos)
   - [Instalación paso a paso](#instalación-paso-a-paso)
   - [Usuarios de prueba](#usuarios-de-prueba)
   - [Pruebas automáticas](#pruebas-automáticas)

## Tecnologías empleadas

| Tecnología | Versión | Uso |
|---|---|---|
| PHP | 8.4 | Lenguaje de la API |
| Laravel | 13 | Framework de la API |
| Laravel Sanctum | 4 | Autenticación por token (`Authorization: Bearer ...`) |
| PostgreSQL | 16 | Base de datos principal (usuarios, cursos, actividades, calificaciones, asistencia) |
| MongoDB | 7 | Contenido de los cursos, preguntas y respuestas de exámenes, y bitácora |
| Redis | 7 | Caché, colas de trabajos en segundo plano y contadores |
| Nginx | 1.27 | Servidor web que atiende las peticiones y las pasa a PHP |
| Mailpit | — | Buzón de correo de pruebas en desarrollo |
| PHPUnit | 12 | Pruebas automáticas |
| Docker y Docker Compose | — | Entorno de desarrollo con todos los servicios |

Los servicios corren en estos contenedores:

| Contenedor | Servicio | Puerto en tu equipo |
|---|---|---|
| `taller_nginx` | Nginx (entrada de la API) | `8000` |
| `taller_app` | PHP-FPM con Laravel | — |
| `taller_queue` | Procesador de la cola de trabajos | — |
| `taller_db` | PostgreSQL | `5432` |
| `taller_mongo` | MongoDB | `27017` |
| `taller_redis` | Redis | — |
| `taller_mailpit` | Mailpit (interfaz web) | `8025` |

## Manual de instalación

### Requisitos

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (incluye Docker Compose)
- Git

No hace falta instalar PHP, Composer ni las bases de datos en tu equipo: todo corre dentro de Docker.

### Instalación paso a paso

**1. Clonar el repositorio**

```bash
git clone https://github.com/JRDM2004/api.git
cd api
```

**2. Crear el archivo de configuración**

```bash
cp .env.example .env
```

Ajusta en `.env` el nombre, usuario y contraseña de la base de datos (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) si quieres usar otros. El archivo `.env` nunca se sube al repositorio.

**3. Construir y levantar los contenedores**

```bash
docker compose up -d --build
```

La primera vez tarda varios minutos porque construye la imagen de PHP.

**4. Instalar las dependencias de PHP**

```bash
docker compose exec app composer install
```

**5. Generar la llave de la aplicación**

```bash
docker compose exec app php artisan key:generate
```

**6. Crear las tablas y cargar los usuarios de prueba**

```bash
docker compose exec app php artisan migrate --seed
```

**7. Comprobar que funciona**

```bash
curl http://localhost:8000/up
```

Debe responder con código 200. La API queda disponible en `http://localhost:8000/api`.

### Usuarios de prueba

El paso 6 crea estos usuarios (solo para desarrollo):

| Número de cuenta | Rol | NIP | Estado |
|---|---|---|---|
| `00000001` | administrador | `ADF001` | activo |
| `00000002` | docente | `D0CE01` | activo |
| `00000003` | alumno | `A1B2C3` | activo |
| `00000004` | alumno | `A1B2C3` | inactivo |

### Pruebas automáticas

```bash
docker compose exec app php artisan test
```

Las pruebas usan una base de datos SQLite en memoria y caché en memoria, así que **no modifican** la base de datos ni el Redis de desarrollo.
