<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Curso extends Model {
    use HasUuids;
    protected $table = 'cursos';
    protected $primaryKey = 'id_curso';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id_usuario_instructor', 'nombre', 'descripcion', 'duracion', 'costo', 'imagen_url', 'activo'];

    public function instructor() {
        return $this->belongsTo(Usuario::class, 'id_usuario_instructor', 'id_usuario');
    }
    public function horarios() {
        return $this->hasMany(HorarioCurso::class, 'id_curso', 'id_curso');
    }
}