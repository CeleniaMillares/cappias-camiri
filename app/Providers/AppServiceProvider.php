<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\Usuario;
use App\Models\Categoria;
use App\Models\Socio;      
use App\Models\CuotaSocio; 
use App\Observers\AuditoriaObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Registramos los modelos que queremos auditar
        Producto::observe(AuditoriaObserver::class);
        Venta::observe(AuditoriaObserver::class);
        Usuario::observe(AuditoriaObserver::class);
        Categoria::observe(AuditoriaObserver::class);
        
        //  Agregamos los nuevos
        Socio::observe(AuditoriaObserver::class);
        CuotaSocio::observe(AuditoriaObserver::class);
    }
}