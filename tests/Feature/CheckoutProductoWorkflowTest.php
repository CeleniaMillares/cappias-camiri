<?php

namespace Tests\Feature;

use App\Events\VentaConfirmada;
use App\Models\Categoria;
use App\Models\Curso;
use App\Models\HorarioCurso;
use App\Models\Pago;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Usuario;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CheckoutProductoWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmed_qr_checkout_marks_sale_and_payment_as_confirmed(): void
    {
        [, $producto] = $this->createProductFixtures();
        $cliente = $this->createUser('cliente@example.test');
        Event::fake([VentaConfirmada::class]);

        $this->actingAs($cliente)
            ->post("/checkout/producto/{$producto->id_producto}", [
                'correo' => $cliente->correo,
                'nombres' => $cliente->nombre,
                'cantidad' => 2,
                'metodo_pago' => 'QR',
                'confirmar_qr' => '1',
            ])
            ->assertRedirect('/')
            ->assertSessionHas('success');

        $venta = Venta::firstOrFail();
        $this->assertSame('PAGADA', $venta->estado);
        $this->assertDatabaseHas('pagos', [
            'id_venta' => $venta->id_venta,
            'monto' => 200,
            'metodo' => 'QR',
            'estado' => 'CONFIRMADO',
        ]);
        Event::assertDispatched(VentaConfirmada::class, fn ($event) => $event->venta->is($venta));
    }

    public function test_unconfirmed_qr_checkout_leaves_sale_pending_without_confirmation_event(): void
    {
        [, $producto] = $this->createProductFixtures();
        $cliente = $this->createUser('cliente@example.test');
        Event::fake([VentaConfirmada::class]);

        $this->actingAs($cliente)
            ->post("/checkout/producto/{$producto->id_producto}", [
                'correo' => $cliente->correo,
                'nombres' => $cliente->nombre,
                'cantidad' => 1,
                'metodo_pago' => 'QR',
            ])
            ->assertRedirect('/')
            ->assertSessionHas('success', 'Compra registrada. La Directiva verificará tu pago para confirmarla.');

        $this->assertSame('PENDIENTE_PAGO', Venta::firstOrFail()->estado);
        $this->assertSame(0, Pago::count());
        Event::assertNotDispatched(VentaConfirmada::class);
    }

    public function test_inactive_product_cannot_be_opened_or_purchased_directly(): void
    {
        [, $producto] = $this->createProductFixtures();
        $producto->update(['activo' => false]);

        $this->get("/checkout/producto/{$producto->id_producto}")->assertNotFound();
        $this->post("/checkout/producto/{$producto->id_producto}", [
            'correo' => 'cliente@example.test',
            'nombres' => 'Cliente de prueba',
            'cantidad' => 1,
            'metodo_pago' => 'EFECTIVO',
        ])->assertNotFound();

        $this->assertDatabaseCount('ventas', 0);
    }

    public function test_existing_email_error_shows_login_link_in_product_checkout(): void
    {
        [, $producto] = $this->createProductFixtures();
        $this->createUser('registrado@example.test');

        $this->from("/checkout/producto/{$producto->id_producto}")
            ->post("/checkout/producto/{$producto->id_producto}", [
                'correo' => 'registrado@example.test',
                'nombres' => 'Cliente registrado',
                'cantidad' => 1,
                'metodo_pago' => 'EFECTIVO',
            ])
            ->assertRedirect("/checkout/producto/{$producto->id_producto}")
            ->assertSessionHasErrors('correo');

        $this->get("/checkout/producto/{$producto->id_producto}")
            ->assertOk()
            ->assertSee('href="/login"', false)
            ->assertSee('Inicia sesión aquí');
    }

    public function test_existing_email_error_shows_login_link_in_course_checkout(): void
    {
        $instructor = $this->createUser('instructor@example.test');
        $curso = Curso::create([
            'id_usuario_instructor' => $instructor->id_usuario,
            'nombre' => 'Curso de prueba',
            'duracion' => '4 sesiones',
            'costo' => 100,
            'activo' => true,
        ]);
        $horario = HorarioCurso::create([
            'id_curso' => $curso->id_curso,
            'dia_semana' => 'Sabado',
            'hora_inicio' => '09:00',
            'hora_fin' => '12:00',
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonth()->toDateString(),
            'cupo_maximo' => 10,
        ]);
        $this->createUser('registrado@example.test');

        $this->from("/checkout/curso/{$curso->id_curso}")
            ->post("/checkout/curso/{$curso->id_curso}", [
                'correo' => 'registrado@example.test',
                'nombres' => 'Alumno registrado',
                'ci' => '12345678',
                'telefono' => '70000000',
                'id_horario_curso' => $horario->id_horario_curso,
                'metodo_pago' => 'EFECTIVO',
            ])
            ->assertRedirect("/checkout/curso/{$curso->id_curso}")
            ->assertSessionHasErrors('correo');

        $this->get("/checkout/curso/{$curso->id_curso}")
            ->assertOk()
            ->assertSee('href="/login"', false)
            ->assertSee('Inicia sesión aquí');
    }

    private function createProductFixtures(): array
    {
        $role = Rol::create(['nombre' => 'Directiva']);
        $directiva = $this->createUser('directiva@example.test');
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
