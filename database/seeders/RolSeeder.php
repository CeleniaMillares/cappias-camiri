<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['nombre' => 'Administrador', 'descripcion' => 'Acceso total al sistema y configuraciones.'],
            ['nombre' => 'Directiva', 'descripcion' => 'Gestión de productos, ventas, cursos y pagos a socios.'],
            ['nombre' => 'Socio/Instructor', 'descripcion' => 'Consulta sus productos y dicta cursos.'],
            ['nombre' => 'Alumno', 'descripcion' => 'Se inscribe en cursos y consulta su historial.'],
            ['nombre' => 'Cliente', 'descripcion' => 'Compra productos y realiza pagos por QR.'],
        ];

        foreach ($roles as $rol) {
            Rol::updateOrCreate(['nombre' => $rol['nombre']], $rol);
        }
    }
}