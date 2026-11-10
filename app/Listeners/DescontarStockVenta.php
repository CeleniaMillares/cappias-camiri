<?php
namespace App\Listeners;
use App\Models\Producto;
use App\Events\VentaConfirmada;
use Illuminate\Validation\ValidationException;

class DescontarStockVenta
{
    public function handle(VentaConfirmada $event): void
    {
        $venta = $event->venta;
        
        // Recorremos los productos de la venta y descontamos
        foreach ($venta->detalles as $detalle) {
            $producto = Producto::whereKey($detalle->id_producto)
                ->lockForUpdate()
                ->firstOrFail();
            if ($producto->stock_actual < $detalle->cantidad) {
                throw ValidationException::withMessages([
                    'productos' => "Stock insuficiente para: {$producto->nombre}",
                ]);
            }

            $producto->stock_actual -= $detalle->cantidad;
            $producto->save();
        }
    }
}