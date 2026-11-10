<?php
namespace App\Listeners;
use App\Models\Venta;
use App\Models\Producto;
use App\Events\VentaConfirmada;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DescontarStockVenta
{
    public function handle(VentaConfirmada $event): void
    {
        DB::transaction(function () use ($event) {
            $venta = Venta::whereKey($event->venta->id_venta)
                ->lockForUpdate()
                ->firstOrFail();

            if ($venta->stock_procesado) {
                Log::info("Stock ya procesado para Venta {$venta->id_venta}");
                return;
            }

            $cantidadesPorProducto = $venta->detalles()
                ->get()
                ->groupBy('id_producto')
                ->map(fn ($detalles) => $detalles->sum('cantidad'))
                ->sortKeys();

            foreach ($cantidadesPorProducto as $idProducto => $cantidad) {
                $producto = Producto::whereKey($idProducto)
                    ->lockForUpdate()
                    ->firstOrFail();
                if ($producto->stock_actual < $cantidad) {
                    throw ValidationException::withMessages([
                        'productos' => "Stock insuficiente para: {$producto->nombre}",
                    ]);
                }

                $producto->decrement('stock_actual', $cantidad);
            }

            $venta->update(['stock_procesado' => true]);
            Log::info("Stock descontado para Venta {$venta->id_venta}");
        });
    }
}