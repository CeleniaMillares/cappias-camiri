<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model {
    use HasUuids;
    protected $table = 'pagos';
    protected $primaryKey = 'id_pago';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id_venta', 'id_inscripcion', 'monto', 'metodo', 'comprobante_url', 'estado'];

    public function venta() {
        return $this->belongsTo(Venta::class, 'id_venta', 'id_venta');
    }
    public function inscripcion() {
        return $this->belongsTo(Inscripcion::class, 'id_inscripcion', 'id_inscripcion');
    }
}