<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CuotaSocio extends Model
{
    use HasUuids;
    protected $table = 'cuotas_socios';
    protected $primaryKey = 'id_cuota';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = ['id_socio', 'tipo', 'metodo', 'estado', 'periodo', 'monto', 'monto_efectivo', 'fecha_pago'];
    public function socio() {
        return $this->belongsTo(Socio::class, 'id_socio', 'id_socio');
    }
}