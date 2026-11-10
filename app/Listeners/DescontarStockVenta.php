<?php
namespace App\Listeners;
use App\Events\VentaConfirmada;

class DescontarStockVenta
{
    public function handle(VentaConfirmada $event): void
    {
        $venta = $event->venta;
        
        // Recorremos los productos de la venta y descontamos
        foreach ($venta->detalles as $detalle) {
            $producto = $detalle->producto;
            $producto->stock_actual -= $detalle->cantidad;
            $producto->save();
        }
    }
}