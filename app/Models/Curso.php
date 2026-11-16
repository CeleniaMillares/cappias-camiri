<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasTitleCase;

class Curso extends Model {
    use HasUuids, HasTitleCase;
    protected $table = 'cursos';
    protected $primaryKey = 'id_curso';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id_usuario_instructor', 'nombre', 'descripcion', 'costo', 'imagen_url', 'activo'];

    protected function titleCaseAttributes(): array
    {
        return ['nombre'];
    }

    public function instructor() {
        return $this->belongsTo(Usuario::class, 'id_usuario_instructor', 'id_usuario');
    }
    public function horarios() {
        return $this->hasMany(HorarioCurso::class, 'id_curso', 'id_curso');
    }
}