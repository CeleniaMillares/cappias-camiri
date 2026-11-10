<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasUuids, SoftDeletes;
    
    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = ['nombre', 'correo', 'contrasena_hash', 'activo', 'fecha_inscripcion'];
    protected $hidden = ['contrasena_hash'];

    public function getAuthPassword()
    {
        return $this->contrasena_hash;
    }

    public function getAuthIdentifierName()
    {
         return 'id_usuario'; 
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

    public function cuotasSocio() {
        return $this->hasOne(Socio::class, 'id_usuario', 'id_usuario');
    }
        // Relación con Socio (si es que tiene perfil de socio)
    public function socio() {
        return $this->hasOne(Socio::class, 'id_usuario', 'id_usuario');
    }
        public function getEmailForPasswordReset()
    {
        return $this->correo;
    }
}