<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Feria extends Model
{
    use HasUuids;

    protected $table = 'ferias';
    protected $primaryKey = 'id_feria';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['nombre', 'fecha', 'lugar', 'imagen_url'];
    // Relación: Una feria tiene muchas ventas
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'id_feria', 'id_feria');
    }
}