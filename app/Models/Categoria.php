<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categoria extends Model {
    use HasUuids, SoftDeletes;
    protected $table = 'categorias';
    protected $primaryKey = 'id_categoria';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['nombre', 'descripcion', 'activo'];

    public function productos() {
        return $this->hasMany(Producto::class, 'id_categoria', 'id_categoria');
    }
}