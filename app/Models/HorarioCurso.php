<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class HorarioCurso extends Model {
    use HasUuids;
    protected $table = 'horario_cursos';
    protected $primaryKey = 'id_horario_curso';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id_curso', 'dia_semana', 'hora_inicio', 'hora_fin', 'fecha_inicio', 'fecha_fin', 'cupo_maximo'];

    public function curso() {
        return $this->belongsTo(Curso::class, 'id_curso', 'id_curso');
    }
    public function inscripciones() {
        return $this->hasMany(Inscripcion::class, 'id_horario_curso', 'id_horario_curso');
    }
    public function listaEsperas() {
        return $this->hasMany(ListaEspera::class, 'id_horario_curso', 'id_horario_curso');
    }
}