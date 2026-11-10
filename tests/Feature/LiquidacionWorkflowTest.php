<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\DetalleVenta;
use App\Models\Liquidacion;
use App\Models\Parametro;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Usuario;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LiquidacionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_is_saved_without_changing_shared_sale_status_and_cannot_exceed_balance(): void
    {
        [$directiva, $socio, $otraSocio, $venta] = $this->createFixtures();

        $this->actingAs($directiva)
            ->from('/admin/liquidaciones/crear/'.$socio->id_usuario)
            ->post('/admin/liquidaciones/crear/'.$socio->id_usuario, [
                'monto_pagar' => 95,
                'metodo' => 'QR',
            ])
            ->assertRedirect('/admin/liquidaciones')
            ->assertSessionHas('success');

        $liquidacion = Liquidacion::where('id_usuario_socio', $socio->id_usuario)->firstOrFail();
        $this->assertSame('QR', $liquidacion->metodo);
        $this->assertEquals(100, (float) $liquidacion->monto_bruto);
        $this->assertEquals(5, (float) $liquidacion->comision);
        $this->assertSame('PAGADA', $liquidacion->estado);
        $this->assertSame('PAGADA', $venta->fresh()->estado);

        $this->actingAs($directiva)
            ->from('/admin/liquidaciones/crear/'.$socio->id_usuario)
            ->post('/admin/liquidaciones/crear/'.$socio->id_usuario, [
                'monto_pagar' => 0.01,
                'metodo' => 'EFECTIVO',
            ])
            ->assertRedirect('/admin/liquidaciones/crear/'.$socio->id_usuario)
            ->assertSessionHasErrors('monto_pagar');

        $this->assertSame(1, Liquidacion::where('id_usuario_socio', $socio->id_usuario)->count());

        $this->actingAs($directiva)
            ->from('/admin/liquidaciones/crear/'.$socio->id_usuario)
            ->post('/admin/liquidaciones/crear/'.$socio->id_usuario, [
                'monto_pagar' => '0.001',
                'metodo' => 'EFECTIVO',
            ])
            ->assertRedirect('/admin/liquidaciones/crear/'.$socio->id_usuario)
            ->assertSessionHasErrors('monto_pagar');

        $this->actingAs($directiva)
            ->get('/admin/liquidaciones')
            ->assertOk()
            ->assertSee($otraSocio->nombre)
            ->assertSee('Bs. 190.00');
    }

    public function test_annulling_payment_restores_the_member_balance_and_keeps_auditable_record(): void
    {
        [$directiva, $socio, , $venta] = $this->createFixtures();
        $liquidacion = Liquidacion::create([
            'id_usuario_socio' => $socio->id_usuario,
            'periodo_inicio' => now()->startOfMonth()->toDateString(),
            'periodo_fin' => now()->toDateString(),
            'monto_bruto' => 100,
            'comision' => 5,
            'monto_neto' => 95,
            'metodo' => 'EFECTIVO',
            'estado' => 'PAGADA',
        ]);

        $this->actingAs($directiva)
            ->post("/admin/liquidaciones/{$liquidacion->id_liquidacion}/anular")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('ANULADA', $liquidacion->fresh()->estado);
        $this->assertSame('PAGADA', $venta->fresh()->estado);

        $this->actingAs($directiva)
            ->get('/admin/liquidaciones')
            ->assertOk()
            ->assertSee('Anulada')
            ->assertSee('EFECTIVO')
            ->assertSee($socio->nombre);
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
