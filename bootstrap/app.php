<?php

use App\Http\Middleware\VerificarRol;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // La API no tiene pantalla de login a la cual redirigir: responde 401.
        $middleware->redirectGuestsTo(
            fn (Request $request) => $request->is('api/*') ? null : '/',
        );

        // Nombre corto para usarlo en las rutas: ->middleware('rol:administrador').
        $middleware->alias([
            'rol' => VerificarRol::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Token vencido o inválido: responde 401 con mensaje de sesión expirada.   
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Tu sesión no es válida o expiró. Inicia sesión de nuevo.',
                ], 401);
            }
        });

        // * Cubre las policies (authorize() y Gate::authorize) y el middleware de rol.
        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'No tienes permiso para realizar esta acción.',
                ], 403);
            }
        });

        // * Cubre a un registro que no existe y a una ruta que no existe.
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'No se encontró el recurso.',
                ], 404);
            }
        });
    })->create();
