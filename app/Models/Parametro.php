<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Parametro extends Model
{
    use HasUuids;

    protected $table = 'parametros';
    protected $primaryKey = 'id_parametro';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = ['clave', 'valor', 'descripcion'];
}