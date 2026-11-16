<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Socio extends Model
{
    use HasUuids, SoftDeletes;
    protected $table = 'socios';
    protected $primaryKey = 'id_socio';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id_usuario', 'ci', 'telefono', 'direccion', 'fecha_inscripcion', 'estado'];

    public function usuario() {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

   public function cuotas() {
        return $this->hasMany(CuotaSocio::class, 'id_socio', 'id_socio');
    }

    public function ferias()
    {
        return $this->belongsToMany(
            Feria::class,
            'feria_socio',
            'id_socio',
            'id_feria',
            'id_socio',
            'id_feria'
        )->withPivot('estado')->withTimestamps();
    }
}