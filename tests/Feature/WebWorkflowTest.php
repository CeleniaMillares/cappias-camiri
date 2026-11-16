<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WebWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_mixed_sale_cannot_accept_cash_above_the_total(): void
    {
        $role = Rol::create(['nombre' => 'Directiva']);
        $directiva = Usuario::create([
            'nombre' => 'Directiva de prueba',
            'correo' => 'directiva@example.test',
            'contrasena_hash' => Hash::make('password'),
            'activo' => true,
        ]);
        $directiva->roles()->attach($role->id_rol);

        $category = Categoria::create(['nombre' => 'Prueba', 'activo' => true]);
        $product = Producto::create([
            'id_categoria' => $category->id_categoria,
            'id_usuario_socio' => $directiva->id_usuario,
            'nombre' => 'Producto de prueba',
            'precio' => 25,
            'stock_actual' => 3,
            'activo' => true,
        ]);

        $this->actingAs($directiva)
            ->from('/admin/ventas/crear')
            ->post('/admin/ventas', [
                'metodo_pago' => 'MIXTO',
                'monto_efectivo' => 30,
                'confirmar_qr' => 1,
                'productos' => [
                    ['id_producto' => $product->id_producto, 'cantidad' => 1],
                ],
            ])
            ->assertRedirect('/admin/ventas/crear')
            ->assertSessionHasErrors('monto_efectivo');

        $this->assertDatabaseCount('ventas', 0);
        $this->assertSame(3, $product->fresh()->stock_actual);
    }

}
