<?php

namespace Database\Seeders;

use App\Models\Parametro;
use Illuminate\Database\Seeder;

class ParametroSeeder extends Seeder
{
    public function run(): void
    {
        $parametros = [
            ['clave' => 'comision_venta', 'valor' => '5', 'descripcion' => 'Porcentaje de comisión para CAPPIAS en ventas.'],
            ['clave' => 'comision_curso', 'valor' => '20', 'descripcion' => 'Porcentaje de comisión para CAPPIAS en inscripciones.'],
            ['clave' => 'comision_excepcion', 'valor' => '0', 'descripcion' => 'Comisión para socios de comunidades rurales.'],
            ['clave' => 'cupo_minimo', 'valor' => '6', 'descripcion' => 'Cantidad mínima de alumnos por curso.'],
            ['clave' => 'cupo_maximo', 'valor' => '15', 'descripcion' => 'Cantidad máxima de alumnos por curso.'],
            ['clave' => 'stock_minimo', 'valor' => '5', 'descripcion' => 'Stock mínimo antes de generar alerta.'],
            ['clave' => 'monto_inscripcion', 'valor' => '100', 'descripcion' => 'Monto fijo de inscripción (matrícula) para nuevos socios.'],
            ['clave' => 'monto_mensualidad', 'valor' => '50', 'descripcion' => 'Monto fijo de la mensualidad de los socios.'],
        ];

        foreach ($parametros as $parametro) {
            Parametro::create($parametro);
        }
    }
}