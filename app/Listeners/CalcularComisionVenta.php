<?php
namespace App\Listeners;
use App\Events\VentaConfirmada;
use App\Models\Parametro;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CalcularComisionVenta
{
    public function handle(VentaConfirmada $event): void
    {
        DB::transaction(function () use ($event) {
            $venta = Venta::whereKey($event->venta->id_venta)
                ->lockForUpdate()
                ->firstOrFail();

            if ($venta->comision_calculada) {
                Log::info("Comisión ya calculada para Venta {$venta->id_venta}");
                return;
            }

            $porcentaje = (float) (Parametro::where('clave', 'comision_venta')->value('valor') ?? 5);
            $comision = round(((float) $venta->total * $porcentaje) / 100, 2);

            $venta->update([
                'comision_cappias' => $comision,
                'comision_calculada' => true,
            ]);

            Log::info("Comisión calculada para Venta {$venta->id_venta}: Bs. {$comision}");
        });
    }
}