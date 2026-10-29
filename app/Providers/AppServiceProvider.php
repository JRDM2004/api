<?php

namespace App\Providers;

use App\Models\Usuario;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ! La documentación describe toda la API: fuera de local no se muestra a nadie.
        // * En local Scramble la abre sin preguntar a este gate.
        Gate::define('viewApiDocs', function (?Usuario $usuario) {
            return false;
        });
    }
}
