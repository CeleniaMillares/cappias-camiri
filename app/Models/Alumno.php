<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alumno extends Model {
    use HasUuids, SoftDeletes;
    protected $table = 'alumnos';
    protected $primaryKey = 'id_alumno';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id_usuario', 'nombres', 'ci', 'telefono', 'edad', 'activo'];

    public function usuario() {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function inscripciones() {
        return $this->hasMany(Inscripcion::class, 'id_alumno', 'id_alumno');
    }
    public function listaEsperas() {
        return $this->hasMany(ListaEspera::class, 'id_alumno', 'id_alumno');
    }
}