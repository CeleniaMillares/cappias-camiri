<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model {
    use HasUuids;
    protected $table = 'roles';
    protected $primaryKey = 'id_rol';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['nombre', 'descripcion'];

    public function usuarios() {
        return $this->belongsToMany(Usuario::class, 'rol_usuario', 'id_rol', 'id_usuario');
    }
}