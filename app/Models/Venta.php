<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasUuids;

    protected $table = 'ventas';
    protected $primaryKey = 'id_venta';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_feria',
        'id_usuario_directiva',
        'total',
        'comision_cappias',
        'comision_calculada',
        'stock_procesado',
        'estado',
    ];

    protected $casts = [
        'comision_cappias' => 'decimal:2',
        'comision_calculada' => 'boolean',
        'stock_procesado' => 'boolean',
    ];

    // Relaciones
    public function feria()
    {
        return $this->belongsTo(Feria::class, 'id_feria', 'id_feria');
    }

    public function directiva()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_directiva', 'id_usuario');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'id_venta', 'id_venta');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_venta', 'id_venta');
    }
}