<?php
namespace App\Listeners;
use App\Events\VentaConfirmada;
use App\Models\Parametro;
use Illuminate\Support\Facades\Log;

class CalcularComisionVenta
{
    public function handle(VentaConfirmada $event): void
    {
        // Obtenemos el 5% desde la base de datos (Fuente de verdad)
        $porcentaje = Parametro::where('clave', 'comision_venta')->value('valor') ?? 5;
        
        $venta = $event->venta;
        $comision = ($venta->total * $porcentaje) / 100;

        // Lo guardamos en el log para probar que funciona
        Log::info("Comisión calculada para Venta {$venta->id_venta}: {$comision} Bs.");
    }
}