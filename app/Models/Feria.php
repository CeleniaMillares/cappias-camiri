<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasTitleCase;

class Feria extends Model
{
    use HasUuids, HasTitleCase;

    protected $table = 'ferias';
    protected $primaryKey = 'id_feria';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['nombre', 'fecha', 'lugar', 'imagen_url'];

    protected function titleCaseAttributes(): array
    {
        return ['nombre'];
    }

    // Relación: Una feria tiene muchas ventas
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'id_feria', 'id_feria');
    }

    public function socios()
    {
        return $this->belongsToMany(
            Socio::class,
            'feria_socio',
            'id_feria',
            'id_socio',
            'id_feria',
            'id_socio'
        )->withPivot('estado')->withTimestamps();
    }
}