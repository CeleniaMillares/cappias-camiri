<?php

namespace Database\Seeders;

use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Socio; // 🔥 Importante importar el modelo Socio
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $rolAdmin = Rol::where('nombre', 'Administrador')->first();
        $rolDirectiva = Rol::where('nombre', 'Directiva')->first();
        $rolSocio = Rol::where('nombre', 'Socio/Instructor')->first();
        $rolAlumno = Rol::where('nombre', 'Alumno')->first();

        // 1. Usuario Administrador
        $admin = Usuario::create([
            'nombre' => 'Administrador General',
            'correo' => 'admin@cappias.com',
            'contrasena_hash' => Hash::make('admin123'),
            'activo' => true,
        ]);
        $admin->roles()->attach($rolAdmin->id_rol);

        // 2. Usuario Directiva
        $directiva = Usuario::create([
            'nombre' => 'Directiva CAPPIAS',
            'correo' => 'directiva@cappias.com',
            'contrasena_hash' => Hash::make('directiva123'),
            'activo' => true,
        ]);
        $directiva->roles()->attach($rolDirectiva->id_rol);

        // 3. Usuario Socio/Instructor (AHORA CON PERFIL DE SOCIO)
        $socio = Usuario::create([
            'nombre' => 'Socio Ejemplo',
            'correo' => 'socio@cappias.com',
            'contrasena_hash' => Hash::make('socio123'),
            'activo' => true,
        ]);
        $socio->roles()->attach($rolSocio->id_rol);

        // 🔥 Creamos su perfil en la tabla socios
        Socio::create([
            'id_usuario' => $socio->id_usuario,
            'ci' => '00000000',
            'telefono' => '70000000',
            'direccion' => 'Sede CAPPIAS',
            'fecha_inscripcion' => now()->toDateString(),
            'estado' => 'ACTIVO'
        ]);

        // 4. Usuario Alumno
        $alumno = Usuario::create([
            'nombre' => 'Alumno Ejemplo',
            'correo' => 'alumno@cappias.com',
            'contrasena_hash' => Hash::make('alumno123'),
            'activo' => true,
        ]);
        $alumno->roles()->attach($rolAlumno->id_rol);
    }
}