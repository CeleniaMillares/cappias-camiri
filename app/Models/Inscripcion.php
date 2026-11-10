<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Inscripcion extends Model {
    use HasUuids;
    protected $table = 'inscripciones';
    protected $primaryKey = 'id_inscripcion';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id_alumno', 'id_horario_curso', 'estado'];

    public function alumno() {
        return $this->belongsTo(Alumno::class, 'id_alumno', 'id_alumno');
    }
    public function horarioCurso() {
        return $this->belongsTo(HorarioCurso::class, 'id_horario_curso', 'id_horario_curso');
    }
    public function pagos() {
        return $this->hasMany(Pago::class, 'id_inscripcion', 'id_inscripcion');
    }
}