<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Usuario;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class VentaWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_sale_reserves_stock_and_confirmation_does_not_deduct_it_twice(): void
    {
        [$directiva, $producto] = $this->createSaleFixtures();

        $this->actingAs($directiva)
            ->post('/admin/ventas', [
                'metodo_pago' => 'QR',
                'productos' => [
                    ['id_producto' => $producto->id_producto, 'cantidad' => 2],
                ],
            ])
            ->assertRedirect('/admin/ventas')
            ->assertSessionHasNoErrors();

        $venta = Venta::firstOrFail();
        $this->assertSame('PENDIENTE_PAGO', $venta->estado);
        $this->assertTrue($venta->stock_procesado);
        $this->assertSame(3, $producto->fresh()->stock_actual);

        $this->actingAs($directiva)
            ->post("/admin/ventas/{$venta->id_venta}/confirmar-pago")
            ->assertRedirect()
            ->assertSessionHas('success');

        $venta->refresh();
        $this->assertSame('PAGADA', $venta->estado);
        $this->assertSame('10.00', $venta->comision_cappias);
        $this->assertTrue($venta->comision_calculada);
        $this->assertSame(3, $producto->fresh()->stock_actual);
    }

    public function test_duplicate_product_lines_are_aggregated_for_stock_validation(): void
    {
        [$directiva, $producto] = $this->createSaleFixtures();

        $this->actingAs($directiva)
            ->from('/admin/ventas/crear')
            ->post('/admin/ventas', [
                'metodo_pago' => 'EFECTIVO',
                'productos' => [
                    ['id_producto' => $producto->id_producto, 'cantidad' => 3],
                    ['id_producto' => $producto->id_producto, 'cantidad' => 3],
                ],
            ])
            ->assertRedirect('/admin/ventas/crear')
            ->assertSessionHasErrors('productos');

        $this->assertSame(5, $producto->fresh()->stock_actual);
        $this->assertDatabaseCount('ventas', 0);
    }

    private function createSaleFixtures(): array
    {
        $role = Rol::create(['nombre' => 'Directiva']);
        $directiva = Usuario::create([
            'nombre' => 'Directiva de prueba',
            'correo' => 'directiva@example.test',
            'contrasena_hash' => Hash::make('password'),
            'activo' => true,
        ]);
        $directiva->roles()->attach($role->id_rol);

        $categoria = Categoria::create([
            'nombre' => 'Artesanía de prueba',
            'activo' => true,
        ]);
        $producto = Producto::create([
            'id_categoria' => $categoria->id_categoria,
            'id_usuario_socio' => $directiva->id_usuario,
            'nombre' => 'Producto de prueba',
            'precio' => 100,
            'stock_actual' => 5,
            'stock_minimo' => 1,
            'activo' => true,
        ]);

        return [$directiva, $producto];
    }
}
