<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Curso;
use App\Models\HorarioCurso;
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

    public function test_public_course_checkout_rejects_a_schedule_from_another_course(): void
    {
        $instructor = Usuario::create([
            'nombre' => 'Instructor de prueba',
            'correo' => 'instructor@example.test',
            'contrasena_hash' => Hash::make('password'),
            'activo' => true,
        ]);
        $course = Curso::create([
            'id_usuario_instructor' => $instructor->id_usuario,
            'nombre' => 'Curso solicitado',
            'duracion' => '4 sesiones',
            'costo' => 100,
            'activo' => true,
        ]);
        $otherCourse = Curso::create([
            'id_usuario_instructor' => $instructor->id_usuario,
            'nombre' => 'Otro curso',
            'duracion' => '4 sesiones',
            'costo' => 100,
            'activo' => true,
        ]);
        $schedule = HorarioCurso::create([
            'id_curso' => $otherCourse->id_curso,
            'dia_semana' => 'Sabado',
            'hora_inicio' => '09:00',
            'hora_fin' => '12:00',
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonth()->toDateString(),
            'cupo_maximo' => 10,
        ]);

        $this->post('/checkout/curso/'.$course->id_curso, [
            'correo' => 'alumno@example.test',
            'nombres' => 'Alumno de prueba',
            'ci' => '12345678',
            'telefono' => '70000000',
            'id_horario_curso' => $schedule->id_horario_curso,
            'metodo_pago' => 'QR',
        ])->assertNotFound();

        $this->assertDatabaseCount('inscripciones', 0);
        $this->assertDatabaseCount('usuarios', 1);
    }
}
