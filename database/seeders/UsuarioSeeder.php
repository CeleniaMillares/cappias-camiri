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
        $admin = Usuario::firstOrCreate(['correo' => 'admin@cappias.com'], [
            'nombre' => 'Administrador General',
            'contrasena_hash' => Hash::make('admin123'),
            'activo' => true,
        ]);
        $admin->roles()->syncWithoutDetaching([$rolAdmin->id_rol]);

        // 2. Usuario Directiva
        $directiva = Usuario::firstOrCreate(['correo' => 'directiva@cappias.com'], [
            'nombre' => 'Directiva CAPPIAS',
            'contrasena_hash' => Hash::make('directiva123'),
            'activo' => true,
        ]);
        $directiva->roles()->syncWithoutDetaching([$rolDirectiva->id_rol]);

        // 3. Usuario Socio/Instructor (AHORA CON PERFIL DE SOCIO)
        $socio = Usuario::firstOrCreate(['correo' => 'socio@cappias.com'], [
            'nombre' => 'Socio Ejemplo',
            'contrasena_hash' => Hash::make('socio123'),
            'activo' => true,
        ]);
        $socio->roles()->syncWithoutDetaching([$rolSocio->id_rol]);

        // 🔥 Creamos su perfil en la tabla socios
        Socio::updateOrCreate(['id_usuario' => $socio->id_usuario], [
            'ci' => '00000000',
            'telefono' => '70000000',
            'direccion' => 'Sede CAPPIAS',
            'fecha_inscripcion' => now()->toDateString(),
            'estado' => 'ACTIVO'
        ]);

        // 4. Usuario Alumno
        $alumno = Usuario::firstOrCreate(['correo' => 'alumno@cappias.com'], [
            'nombre' => 'Alumno Ejemplo',
            'contrasena_hash' => Hash::make('alumno123'),
            'activo' => true,
        ]);
        $alumno->roles()->syncWithoutDetaching([$rolAlumno->id_rol]);
    }
}