<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Liquidacion extends Model {
    use HasUuids;
    protected $table = 'liquidaciones';
    protected $primaryKey = 'id_liquidacion';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id_usuario_socio', 'periodo_inicio', 'periodo_fin', 'monto_bruto', 'comision', 'monto_neto', 'metodo', 'estado'];

    public function socio() {
        return $this->belongsTo(Usuario::class, 'id_usuario_socio', 'id_usuario');
    }
}