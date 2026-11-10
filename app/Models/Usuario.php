<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable; 
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasUuids, SoftDeletes, Notifiable;
    
    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';
    public $incrementing = false;
    protected $keyType = 'string';
    
    // 🔥 QUITAMOS 'fecha_inscripcion' de aquí. Solo dejamos los datos de login.
    protected $fillable = ['nombre', 'correo', 'contrasena_hash', 'activo'];
    protected $hidden = ['contrasena_hash'];

    public function getAuthPassword()
    {
        return $this->contrasena_hash;
    }

    public function getAuthIdentifierName()
    {
         return 'id_usuario'; 
    }

    public function getEmailForPasswordReset()
    {
        return $this->correo;
    }

    public function hasRole(string $roleName)
    {
        return $this->roles()->where('nombre', $roleName)->exists();
    }

    // Relaciones
    public function roles() {
        return $this->belongsToMany(Rol::class, 'rol_usuario', 'id_usuario', 'id_rol');
    }
    
    public function productosComoSocio() {
        return $this->hasMany(Producto::class, 'id_usuario_socio', 'id_usuario');
    }
    
    public function ventasComoDirectiva() {
        return $this->hasMany(Venta::class, 'id_usuario_directiva', 'id_usuario');
    }
    
    public function cursosComoInstructor() {
        return $this->hasMany(Curso::class, 'id_usuario_instructor', 'id_usuario');
    }

    public function alumno() {
        return $this->hasOne(Alumno::class, 'id_usuario', 'id_usuario');
    }

    // 🔥 RELACIÓN ÚNICA CON SOCIO (Eliminamos la duplicada)
    public function socio() {
        return $this->hasOne(Socio::class, 'id_usuario', 'id_usuario');
    }
}