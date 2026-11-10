<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model {
    use HasUuids, SoftDeletes;
    protected $table = 'productos';
    protected $primaryKey = 'id_producto';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id_categoria', 'id_usuario_socio', 'nombre', 'descripcion', 'precio', 'materiales', 'tecnica', 'fotografia_url', 'stock_actual', 'stock_minimo', 'activo'];

    public function categoria() {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }
    public function socio() {
        return $this->belongsTo(Usuario::class, 'id_usuario_socio', 'id_usuario');
    }
}