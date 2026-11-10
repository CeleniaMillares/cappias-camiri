<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Categoria;
use App\Models\CuotaSocio;
use App\Models\Curso;
use App\Models\DetalleVenta;
use App\Models\HorarioCurso;
use App\Models\Inscripcion;
use App\Models\Parametro;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Socio;
use App\Models\Usuario;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReporteDashboardWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_exports_include_all_report_sections_and_persisted_commissions(): void
    {
        [$directiva] = $this->createFixtures();

        $this->actingAs($directiva)
            ->get('/admin/reportes?fecha_inicio='.today()->toDateString().'&fecha_fin='.today()->toDateString())
            ->assertOk()
            ->assertSee('Bs. 12.00')
            ->assertSee('Bs. 8.00')
            ->assertSee('Bs. 150.00');

        $csvResponse = $this->actingAs($directiva)
            ->get('/admin/reportes/excel?fecha_inicio='.today()->toDateString().'&fecha_fin='.today()->toDateString())
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csv = $csvResponse->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('RESUMEN FINANCIERO', $csv);
        $this->assertStringContainsString('DETALLE DE VENTAS', $csv);
        $this->assertStringContainsString('DETALLE DE CURSOS', $csv);
        $this->assertStringContainsString('DETALLE DE MEMBRESÍAS', $csv);
        $this->assertStringContainsString('"Comisión de ventas CAPPIAS",12', $csv);
        $this->assertStringContainsString('"Comisión de cursos CAPPIAS",8', $csv);

        $this->actingAs($directiva)
            ->get('/admin/reportes/pdf?fecha_inicio='.today()->toDateString().'&fecha_fin='.today()->toDateString())
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_report_rejects_reversed_date_range_and_renders_empty_state(): void
    {
        $directiva = $this->createDirectiva();
        $today = today()->toDateString();
        $yesterday = today()->subDay()->toDateString();

        $this->actingAs($directiva)
            ->from('/admin/reportes')
            ->get("/admin/reportes?fecha_inicio={$today}&fecha_fin={$yesterday}")
            ->assertRedirect('/admin/reportes')
            ->assertSessionHasErrors('fecha_fin');

        $this->actingAs($directiva)
            ->get('/admin/reportes?fecha_inicio=2020-01-01&fecha_fin=2020-01-02')
            ->assertOk()
            ->assertSee('No hay datos en el periodo seleccionado');
    }

    public function test_dashboard_charts_use_paid_sales_and_persisted_commissions(): void
    {
        [$directiva, , , $venta] = $this->createFixtures();
        $venta->update(['created_at' => now()]);
        Venta::create([
            'id_usuario_directiva' => $directiva->id_usuario,
            'total' => 999,
            'comision_cappias' => 999,
            'estado' => 'PENDIENTE_PAGO',
            'created_at' => now(),
        ]);

        $this->actingAs($directiva)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('graficoComisiones')
            ->assertSee('Ventas de los Últimos 7 Días')
            ->assertSee('Comisión de ventas')
            ->assertSee('12')
            ->assertSee('8')
            ->assertSee('150')
            ->assertDontSee('999');
    }

    private function createFixtures(): array
    {
        $directivaRole = Rol::create(['nombre' => 'Directiva']);
        $socioRole = Rol::create(['nombre' => 'Socio/Instructor']);
        $directiva = $this->createUser('directiva@example.test');
        $directiva->roles()->attach($directivaRole->id_rol);
        $socioUser = $this->createUser('socio@example.test');
        $socioUser->roles()->attach($socioRole->id_rol);
        $socio = Socio::create([
            'id_usuario' => $socioUser->id_usuario,
            'ci' => '12345678',
            'telefono' => '70000000',
            'fecha_inscripcion' => today()->toDateString(),
            'estado' => 'ACTIVO',
        ]);

        $categoria = Categoria::create(['nombre' => 'Categoría Reportes', 'activo' => true]);
        $producto = Producto::create([
            'id_categoria' => $categoria->id_categoria,
            'id_usuario_socio' => $socioUser->id_usuario,
            'nombre' => 'Producto Reportes',
            'precio' => 100,
            'stock_actual' => 2,
            'stock_minimo' => 1,
            'activo' => true,
        ]);
        $venta = Venta::create([
            'id_usuario_directiva' => $directiva->id_usuario,
            'total' => 100,
            'comision_cappias' => 12,
            'comision_calculada' => true,
            'estado' => 'PAGADA',
            'created_at' => now(),
        ]);
        DetalleVenta::create([
            'id_venta' => $venta->id_venta,
            'id_producto' => $producto->id_producto,
            'cantidad' => 1,
            'precio_unitario' => 100,
            'subtotal' => 100,
        ]);

        $curso = Curso::create([
            'id_usuario_instructor' => $socioUser->id_usuario,
            'nombre' => 'Curso Reportes',
            'duracion' => '1 mes',
            'costo' => 40,
            'activo' => true,
        ]);
        $horario = HorarioCurso::create([
            'id_curso' => $curso->id_curso,
            'dia_semana' => 'Lunes',
            'hora_inicio' => '09:00',
            'hora_fin' => '10:00',
            'fecha_inicio' => today()->toDateString(),
            'fecha_fin' => today()->addMonth()->toDateString(),
            'cupo_maximo' => 10,
        ]);
        $alumno = Alumno::create([
            'nombres' => 'Alumno Reportes',
            'ci' => '87654321',
            'telefono' => '70000001',
            'edad' => 20,
        ]);
        Inscripcion::create([
            'id_alumno' => $alumno->id_alumno,
            'id_horario_curso' => $horario->id_horario_curso,
            'comision_cappias' => 8,
            'comision_calculada' => true,
            'estado' => 'CONFIRMADA',
            'created_at' => now(),
        ]);
        CuotaSocio::create([
            'id_socio' => $socio->id_socio,
            'tipo' => 'MATRICULA',
            'metodo' => 'EFECTIVO',
            'estado' => 'CONFIRMADO',
            'monto' => 100,
            'monto_efectivo' => 100,
            'fecha_pago' => today()->toDateString(),
        ]);
        CuotaSocio::create([
            'id_socio' => $socio->id_socio,
            'tipo' => 'MENSUALIDAD',
            'metodo' => 'QR',
            'estado' => 'CONFIRMADO',
            'periodo' => today()->format('Y-m'),
            'monto' => 50,
            'monto_efectivo' => 0,
            'fecha_pago' => today()->toDateString(),
        ]);
        Parametro::create(['clave' => 'comision_venta', 'valor' => '5']);
        Parametro::create(['clave' => 'comision_curso', 'valor' => '20']);

        return [$directiva, $socioUser, $socio, $venta];
    }

    private function createDirectiva(): Usuario
    {
        $role = Rol::create(['nombre' => 'Directiva']);
        $user = $this->createUser('directiva@example.test');
        $user->roles()->attach($role->id_rol);

        return $user;
    }

    private function createUser(string $email): Usuario
    {
        return Usuario::create([
            'nombre' => 'Usuario de prueba',
            'correo' => $email,
            'contrasena_hash' => Hash::make('password'),
            'activo' => true,
        ]);
    }
}
