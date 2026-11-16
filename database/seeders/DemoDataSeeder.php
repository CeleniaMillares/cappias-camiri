<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\Socio;
use App\Models\Rol;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Curso;
use App\Models\HorarioCurso;
use App\Models\Alumno;
use App\Models\Inscripcion;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Pago;
use App\Models\PagoSocio;
use App\Models\Feria;
use App\Models\CuotaSocio; // 🔥 Cambiado
use App\Models\Parametro;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $rolSocio = Rol::where('nombre', 'Socio/Instructor')->first();
        $rolDirectiva = Rol::where('nombre', 'Directiva')->first();
        
        $catCeramica = Categoria::where('nombre', 'Cerámica')->first();
        $catTejidos = Categoria::where('nombre', 'Tejidos')->first();

        $montoInscripcion = Parametro::where('clave', 'monto_inscripcion')->value('valor') ?? 100;
        $montoMensualidad = Parametro::where('clave', 'monto_mensualidad')->value('valor') ?? 50;

        // 1. Socio María
        $userSocio1 = Usuario::create([
            'nombre' => 'María Artesana',
            'correo' => 'maria@cappias.com',
            'contrasena_hash' => Hash::make('123456'),
            'activo' => true
        ]);
        $userSocio1->roles()->attach($rolSocio->id_rol);

        $socio1 = Socio::create([
            'id_usuario' => $userSocio1->id_usuario,
            'ci' => '87654321',
            'telefono' => '70000001',
            'direccion' => 'Barrio Central',
            'fecha_inscripcion' => now()->subMonths(2)->toDateString(),
            'estado' => 'ACTIVO'
        ]);

        CuotaSocio::create(['id_socio' => $socio1->id_socio, 'tipo' => 'MATRICULA', 'metodo' => 'EFECTIVO', 'estado' => 'CONFIRMADO', 'monto' => $montoInscripcion, 'fecha_pago' => now()->subMonths(2)]);
        CuotaSocio::create(['id_socio' => $socio1->id_socio, 'tipo' => 'MENSUALIDAD', 'metodo' => 'EFECTIVO', 'estado' => 'CONFIRMADO', 'periodo' => now()->subMonth()->format('Y-m'), 'monto' => $montoMensualidad, 'fecha_pago' => now()->subMonth()]);

        // 2. Socio Pedro
        $userSocio2 = Usuario::create([
            'nombre' => 'Pedro Tejedor',
            'correo' => 'pedro@cappias.com',
            'contrasena_hash' => Hash::make('123456'),
            'activo' => true
        ]);
        $userSocio2->roles()->attach($rolSocio->id_rol);

        $socio2 = Socio::create([
            'id_usuario' => $userSocio2->id_usuario,
            'ci' => '12345678',
            'telefono' => '70000002',
            'direccion' => 'Zona Sur',
            'fecha_inscripcion' => now()->subDays(10)->toDateString(),
            'estado' => 'ACTIVO'
        ]);

        CuotaSocio::create(['id_socio' => $socio2->id_socio, 'tipo' => 'MATRICULA', 'metodo' => 'QR', 'estado' => 'PENDIENTE', 'monto' => $montoInscripcion, 'fecha_pago' => now()->subDays(10)]);

        // 3. Productos
        $prod1 = Producto::create([
            'id_categoria' => $catCeramica->id_categoria,
            'id_usuario_socio' => $userSocio1->id_usuario,
            'nombre' => 'Juego de Tazas de Barro',
            'descripcion' => 'Set de 4 tazas hechas a mano.',
            'precio' => 120.00,
            'stock_actual' => 10,
            'stock_minimo' => 3,
            'fotografia_url' => 'https://images.unsplash.com/photo-1610701596007-11502861dcfa?q=80&w=400&auto=format&fit=crop',
            'activo' => true
        ]);

        $prod2 = Producto::create([
            'id_categoria' => $catTejidos->id_categoria,
            'id_usuario_socio' => $userSocio2->id_usuario,
            'nombre' => 'Chalina de Lana de Oveja',
            'descripcion' => 'Tejida a mano, colores naturales.',
            'precio' => 80.50,
            'stock_actual' => 5,
            'stock_minimo' => 2,
            'fotografia_url' => 'https://images.unsplash.com/photo-1606502973862-0a7d3621504a?q=80&w=400&auto=format&fit=crop',
            'activo' => true
        ]);

        // 4. Ferias y Cursos
        Feria::create([
            'nombre' => 'Feria de la Primavera',
            'fecha' => now()->addDays(10)->toDateString(),
            'lugar' => 'Plaza 24 de Septiembre',
            'imagen_url' => 'https://images.unsplash.com/photo-1531058020387-3be344556be6?q=80&w=600&auto=format&fit=crop'
        ]);

        $curso = Curso::create([
            'id_usuario_instructor' => $userSocio1->id_usuario,
            'nombre' => 'Taller de Cerámica Básica',
            'descripcion' => 'Aprende a moldear el barro desde cero.',
            'costo' => 250.00,
            'imagen_url' => 'https://images.unsplash.com/photo-1565193566173-7a0ee3dbe261?q=80&w=600&auto=format&fit=crop',
            'activo' => true
        ]);

        $horario = HorarioCurso::create([
            'id_curso' => $curso->id_curso,
            'dia_semana' => 'Sábados',
            'hora_inicio' => '09:00',
            'hora_fin' => '12:00',
            'fecha_inicio' => now()->addDays(5)->toDateString(),
            'fecha_fin' => now()->addDays(30)->toDateString(),
            'cupo_maximo' => 15,
        ]);

        $alumno1 = Alumno::create([
            'nombres' => 'Alumno Ejemplo',
            'ci' => '11223344',
            'telefono' => '70000003',
            'edad' => 20
        ]);

        Inscripcion::create([
            'id_alumno' => $alumno1->id_alumno,
            'id_horario_curso' => $horario->id_horario_curso,
            'estado' => 'CONFIRMADA'
        ]);

        // 5. Ventas
        $directivaUser = Usuario::whereHas('roles', fn($q) => $q->where('nombre', 'Directiva'))->first();

        $venta1 = Venta::create([
            'id_usuario_directiva' => $directivaUser->id_usuario,
            'total' => 120.00,
            'estado' => 'PAGADA',
            'created_at' => now()->subHours(2)
        ]);
        DetalleVenta::create([
            'id_venta' => $venta1->id_venta,
            'id_producto' => $prod1->id_producto,
            'cantidad' => 1,
            'precio_unitario' => 120.00,
            'subtotal' => 120.00
        ]);
        Pago::create([
            'id_venta' => $venta1->id_venta,
            'monto' => 120.00,
            'metodo' => 'EFECTIVO',
            'estado' => 'CONFIRMADO'
        ]);
        $prod1->decrement('stock_actual', 1);

        $venta2 = Venta::create([
            'id_usuario_directiva' => $directivaUser->id_usuario,
            'total' => 161.00,
            'estado' => 'PENDIENTE_PAGO',
            'created_at' => now()->subMinutes(30)
        ]);
        DetalleVenta::create([
            'id_venta' => $venta2->id_venta,
            'id_producto' => $prod2->id_producto,
            'cantidad' => 2,
            'precio_unitario' => 80.50,
            'subtotal' => 161.00
        ]);

        PagoSocio::create([
            'id_usuario_socio' => $userSocio1->id_usuario,
            'periodo_inicio' => now()->startOfMonth(),
            'periodo_fin' => now(),
            'monto_bruto' => 500.00,
            'comision' => 25.00,
            'monto_neto' => 475.00,
            'origen' => 'VENTAS',
            'metodo' => 'EFECTIVO',
            'estado' => 'PAGADA'
        ]);
    }
}