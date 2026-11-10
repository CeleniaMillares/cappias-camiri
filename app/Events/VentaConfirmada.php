<?php
namespace App\Events;
use App\Models\Venta;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VentaConfirmada
{
    use Dispatchable, SerializesModels;

    public $venta;
    public function __construct(Venta $venta) {
        $this->venta = $venta;
    }
}