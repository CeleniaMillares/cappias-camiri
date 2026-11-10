<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ListaEspera extends Model {
    use HasUuids;
    protected $table = 'lista_esperas';
    protected $primaryKey = 'id_lista_espera';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id_alumno', 'id_horario_curso'];

    public function alumno() {
        return $this->belongsTo(Alumno::class, 'id_alumno', 'id_alumno');
    }
    public function horarioCurso() {
        return $this->belongsTo(HorarioCurso::class, 'id_horario_curso', 'id_horario_curso');
    }
}