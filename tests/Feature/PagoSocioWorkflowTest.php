<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Alumno;
use App\Models\Curso;
use App\Models\DetalleVenta;
use App\Models\HorarioCurso;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\PagoSocio;
use App\Models\Parametro;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Usuario;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PagoSocioWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_is_saved_without_changing_shared_sale_status_and_cannot_exceed_balance(): void
    {
        [$directiva, $socio, $otraSocio, $venta] = $this->createFixtures();

        $this->actingAs($directiva)
            ->from('/admin/pagos-socios/crear/'.$socio->id_usuario)
            ->post('/admin/pagos-socios/crear/'.$socio->id_usuario, [
                'monto_pagar' => 95,
                'metodo' => 'QR',
            ])
            ->assertRedirect('/admin/pagos-socios')
            ->assertSessionHas('success');

        $pago = PagoSocio::where('id_usuario_socio', $socio->id_usuario)->firstOrFail();
        $this->assertSame('QR', $pago->metodo);
        $this->assertEquals(100, (float) $pago->monto_bruto);
        $this->assertEquals(5, (float) $pago->comision);
        $this->assertSame('VENTAS', $pago->origen);
        $this->assertSame('PAGADA', $pago->estado);
        $this->assertSame('PAGADA', $venta->fresh()->estado);

        $this->actingAs($directiva)
            ->from('/admin/pagos-socios/crear/'.$socio->id_usuario)
            ->post('/admin/pagos-socios/crear/'.$socio->id_usuario, [
                'monto_pagar' => 0.01,
                'metodo' => 'EFECTIVO',
            ])
            ->assertRedirect('/admin/pagos-socios/crear/'.$socio->id_usuario)
            ->assertSessionHasErrors('monto_pagar');

        $this->assertSame(1, PagoSocio::where('id_usuario_socio', $socio->id_usuario)->count());

        $this->actingAs($directiva)
            ->from('/admin/pagos-socios/crear/'.$socio->id_usuario)
            ->post('/admin/pagos-socios/crear/'.$socio->id_usuario, [
                'monto_pagar' => '0.001',
                'metodo' => 'EFECTIVO',
            ])
            ->assertRedirect('/admin/pagos-socios/crear/'.$socio->id_usuario)
            ->assertSessionHasErrors('monto_pagar');

        $this->actingAs($directiva)
            ->get('/admin/pagos-socios')
            ->assertOk()
            ->assertSee($otraSocio->nombre)
            ->assertSee('Bs. 190.00');
    }

    public function test_annulling_payment_restores_the_member_balance_and_keeps_auditable_record(): void
    {
        [$directiva, $socio, , $venta] = $this->createFixtures();
        $pago = PagoSocio::create([
            'id_usuario_socio' => $socio->id_usuario,
            'periodo_inicio' => now()->startOfMonth()->toDateString(),
            'periodo_fin' => now()->toDateString(),
            'monto_bruto' => 100,
            'comision' => 5,
            'monto_neto' => 95,
            'origen' => 'VENTAS',
            'metodo' => 'EFECTIVO',
            'estado' => 'PAGADA',
        ]);

        $this->actingAs($directiva)
            ->post("/admin/pagos-socios/{$pago->id_pago_socio}/anular")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('ANULADA', $pago->fresh()->estado);
        $this->assertSame('PAGADA', $venta->fresh()->estado);

        $this->actingAs($directiva)
            ->get('/admin/pagos-socios')
            ->assertOk()
            ->assertSee('Anulada')
            ->assertSee('EFECTIVO')
            ->assertSee($socio->nombre);
    }

    public function test_course_income_is_added_to_the_member_balance_after_cappias_commission(): void
    {
        [$directiva, $socio] = $this->createFixtures();
        PagoSocio::create([
            'id_usuario_socio' => $socio->id_usuario,
            'periodo_inicio' => now()->startOfMonth()->toDateString(),
            'periodo_fin' => now()->toDateString(),
            'monto_bruto' => 100,
            'comision' => 5,
            'monto_neto' => 95,
            'origen' => 'VENTAS',
            'metodo' => 'EFECTIVO',
            'estado' => 'PAGADA',
        ]);

        $curso = Curso::create([
            'id_usuario_instructor' => $socio->id_usuario,
            'nombre' => 'Curso del instructor',
            'costo' => 100,
            'activo' => true,
        ]);
        $horario = HorarioCurso::create([
            'id_curso' => $curso->id_curso,
            'dia_semana' => 'Sábado',
            'hora_inicio' => '09:00',
            'hora_fin' => '12:00',
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonth()->toDateString(),
            'cupo_maximo' => 10,
        ]);
        $alumno = Alumno::create([
            'nombres' => 'Alumno de prueba',
            'ci' => '12345678',
            'telefono' => '70000000',
            'edad' => 20,
        ]);
        $inscripcion = Inscripcion::create([
            'id_alumno' => $alumno->id_alumno,
            'id_horario_curso' => $horario->id_horario_curso,
            'comision_cappias' => 20,
            'comision_calculada' => true,
            'estado' => 'CONFIRMADA',
        ]);
        Pago::create([
            'id_inscripcion' => $inscripcion->id_inscripcion,
            'monto' => 100,
            'metodo' => 'QR',
            'estado' => 'CONFIRMADO',
        ]);

        $this->actingAs($directiva)
            ->get('/admin/pagos-socios')
            ->assertOk()
            ->assertSee('Bs. 80.00');

        $this->actingAs($directiva)
            ->get("/admin/pagos-socios/crear/{$socio->id_usuario}")
            ->assertOk()
            ->assertSee('Curso Del Instructor')
            ->assertSee('Saldo Pendiente a Pagar:')
            ->assertSee('Bs. 80.00');

        $this->actingAs($directiva)
            ->post("/admin/pagos-socios/crear/{$socio->id_usuario}", [
                'monto_pagar' => 40,
                'metodo' => 'EFECTIVO',
            ])
            ->assertRedirect('/admin/pagos-socios')
            ->assertSessionHas('success');

        $abonoParcial = PagoSocio::where('id_usuario_socio', $socio->id_usuario)
            ->where('estado', 'PAGADA')
            ->where('monto_bruto_cursos', 50)
            ->firstOrFail();
        $this->assertEquals(50, (float) $abonoParcial->monto_bruto_cursos);
        $this->assertEquals(10, (float) $abonoParcial->comision_cursos);
        $this->assertEquals(0, (float) $abonoParcial->monto_bruto_ventas);
        $this->assertSame('CURSOS', $abonoParcial->origen);

        $this->actingAs($directiva)
            ->post("/admin/pagos-socios/{$abonoParcial->id_pago_socio}/anular")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->actingAs($directiva)
            ->get('/admin/pagos-socios')
            ->assertOk()
            ->assertSee('Bs. 80.00');

        $this->actingAs($directiva)
            ->post("/admin/pagos-socios/crear/{$socio->id_usuario}", [
                'monto_pagar' => 80,
                'metodo' => 'EFECTIVO',
            ])
            ->assertRedirect('/admin/pagos-socios')
            ->assertSessionHas('success');

        $pagoSocio = PagoSocio::where('id_usuario_socio', $socio->id_usuario)
            ->where('estado', 'PAGADA')
            ->where('monto_bruto_cursos', 100)
            ->firstOrFail();
        $this->assertEquals(100, (float) $pagoSocio->monto_bruto);
        $this->assertEquals(20, (float) $pagoSocio->comision);
        $this->assertEquals(80, (float) $pagoSocio->monto_neto);
        $this->assertEquals(100, (float) $pagoSocio->monto_bruto_cursos);
        $this->assertEquals(20, (float) $pagoSocio->comision_cursos);
        $this->assertEquals(0, (float) $pagoSocio->monto_bruto_ventas);
        $this->assertSame('CURSOS', $pagoSocio->origen);
    }

    private function createFixtures(): array
    {
        $directivaRole = Rol::create(['nombre' => 'Directiva']);
        $socioRole = Rol::create(['nombre' => 'Socio/Instructor']);
        $directiva = $this->createUser('directiva@example.test');
        $directiva->roles()->attach($directivaRole->id_rol);
        $socio = $this->createUser('socio@example.test');
        $socio->roles()->attach($socioRole->id_rol);
        $otraSocio = $this->createUser('otro-socio@example.test');
        $otraSocio->roles()->attach($socioRole->id_rol);
        $categoria = Categoria::create(['nombre' => 'Artesanía', 'activo' => true]);
        $producto = $this->createProduct($categoria, $socio, 'Producto Socio Uno');
        $productoOtro = $this->createProduct($categoria, $otraSocio, 'Producto Socio Dos');
        $venta = Venta::create([
            'id_usuario_directiva' => $directiva->id_usuario,
            'total' => 300,
            'estado' => 'PAGADA',
        ]);
        DetalleVenta::create([
            'id_venta' => $venta->id_venta,
            'id_producto' => $producto->id_producto,
            'cantidad' => 1,
            'precio_unitario' => 100,
            'subtotal' => 100,
        ]);
        DetalleVenta::create([
            'id_venta' => $venta->id_venta,
            'id_producto' => $productoOtro->id_producto,
            'cantidad' => 1,
            'precio_unitario' => 200,
            'subtotal' => 200,
        ]);
        Parametro::create([
            'clave' => 'comision_venta',
            'valor' => '5',
            'descripcion' => 'Comisión de venta',
        ]);

        return [$directiva, $socio, $otraSocio, $venta];
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

    private function createProduct(Categoria $categoria, Usuario $socio, string $nombre): Producto
    {
        return Producto::create([
            'id_categoria' => $categoria->id_categoria,
            'id_usuario_socio' => $socio->id_usuario,
            'nombre' => $nombre,
            'precio' => 100,
            'stock_actual' => 5,
            'stock_minimo' => 1,
            'activo' => true,
        ]);
    }
}
