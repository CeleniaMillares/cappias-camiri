<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PagoSocio extends Model {
    use HasUuids;
    protected $table = 'pagos_socios';
    protected $primaryKey = 'id_pago_socio';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id_usuario_socio',
        'periodo_inicio',
        'periodo_fin',
        'monto_bruto',
        'comision',
        'monto_neto',
        'origen',
        'monto_bruto_ventas',
        'comision_ventas',
        'monto_bruto_cursos',
        'comision_cursos',
        'metodo',
        'estado',
    ];

    public function socio() {
        return $this->belongsTo(Usuario::class, 'id_usuario_socio', 'id_usuario');
    }
}