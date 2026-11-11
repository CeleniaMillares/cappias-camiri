<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    use HasUuids;

    protected $table = 'auditorias';
    protected $primaryKey = 'id_auditoria';
    public $incrementing = false;
    protected $keyType = 'string';
    
    // Campos para registrar el evento
    protected $fillable = ['id_usuario', 'nombre_usuario', 'accion', 'entidad'];

    /**
     * Relación: Una auditoría pertenece a un usuario.
     * Permite consultar: $auditoria->usuario->nombre
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}