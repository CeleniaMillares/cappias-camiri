<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DetalleVenta extends Model {
    use HasUuids;
    protected $table = 'detalle_ventas';
    protected $primaryKey = 'id_detalle_venta';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id_venta', 'id_producto', 'cantidad', 'precio_unitario', 'subtotal'];

    public function venta() {
        return $this->belongsTo(Venta::class, 'id_venta', 'id_venta');
    }
    public function producto() {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}