<?php

namespace Tests\Feature;

use App\Models\Parametro;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginAndParametroSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_user_cannot_log_in_and_receives_specific_message(): void
    {
        Usuario::create([
            'nombre' => 'Usuario inactivo',
            'correo' => 'inactivo@example.test',
            'contrasena_hash' => Hash::make('password'),
            'activo' => false,
        ]);

        $this->from('/login')
            ->post('/login', [
                'correo' => 'inactivo@example.test',
                'contrasena' => 'password',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors([
                'correo' => 'Tu cuenta ha sido desactivada. Contacta al administrador.',
            ]);

        $this->assertGuest();
    }

    public function test_active_user_can_log_in(): void
    {
        Usuario::create([
            'nombre' => 'Usuario activo',
            'correo' => 'activo@example.test',
            'contrasena_hash' => Hash::make('password'),
            'activo' => true,
        ]);

        $this->post('/login', [
            'correo' => 'activo@example.test',
            'contrasena' => 'password',
        ])->assertRedirect('/admin/dashboard');

        $this->assertAuthenticated();
    }

    public function test_user_with_alumno_role_cannot_log_in(): void
    {
        $role = Rol::create(['nombre' => 'Alumno']);
        $student = Usuario::create([
            'nombre' => 'Alumno de prueba',
            'correo' => 'alumno@example.test',
            'contrasena_hash' => Hash::make('password'),
            'activo' => true,
        ]);
        $student->roles()->attach($role->id_rol);

        $this->from('/login')
            ->post('/login', [
                'correo' => 'alumno@example.test',
                'contrasena' => 'password',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors([
                'correo' => 'Las cuentas con rol Alumno no pueden iniciar sesión.',
            ]);

        $this->assertGuest();
    }

    public function test_administrator_cannot_save_negative_or_out_of_range_parameters(): void
    {
        $admin = $this->createAdministrator();
        $commission = Parametro::create(['clave' => 'comision_venta', 'valor' => '5']);
        $fee = Parametro::create(['clave' => 'monto_inscripcion', 'valor' => '100']);
        $monthlyFee = Parametro::create(['clave' => 'monto_mensualidad', 'valor' => '50']);

        $this->actingAs($admin)
            ->from('/admin/parametros')
            ->post('/admin/parametros', [
                'parametros' => [
                    $commission->id_parametro => -1,
                    $fee->id_parametro => 0,
                    $monthlyFee->id_parametro => -10,
                ],
            ])
            ->assertRedirect('/admin/parametros')
            ->assertSessionHasErrors([
                'parametros.'.$commission->id_parametro,
                'parametros.'.$fee->id_parametro,
                'parametros.'.$monthlyFee->id_parametro,
            ]);

        $this->assertSame('5', $commission->fresh()->valor);
        $this->assertSame('100', $fee->fresh()->valor);
        $this->assertSame('50', $monthlyFee->fresh()->valor);
    }

    public function test_commission_above_one_hundred_is_rejected(): void
    {
        $admin = $this->createAdministrator();
        $commission = Parametro::create(['clave' => 'comision_curso', 'valor' => '20']);

        $this->actingAs($admin)
            ->from('/admin/parametros')
            ->post('/admin/parametros', [
                'parametros' => [$commission->id_parametro => 101],
            ])
            ->assertSessionHasErrors('parametros.'.$commission->id_parametro);

        $this->assertSame('20', $commission->fresh()->valor);
    }

    public function test_valid_parameter_values_are_saved(): void
    {
        $admin = $this->createAdministrator();
        $commission = Parametro::create(['clave' => 'comision_venta', 'valor' => '5']);
        $fee = Parametro::create(['clave' => 'monto_inscripcion', 'valor' => '100']);
        $stock = Parametro::create(['clave' => 'stock_minimo', 'valor' => '5']);

        $this->actingAs($admin)
            ->post('/admin/parametros', [
                'parametros' => [
                    $commission->id_parametro => '7.5',
                    $fee->id_parametro => '125.50',
                    $stock->id_parametro => '8',
                ],
            ])
            ->assertRedirect('/admin/parametros')
            ->assertSessionHas('success', 'Parámetros actualizados correctamente.');

        $this->assertSame('7.5', $commission->fresh()->valor);
        $this->assertSame('125.50', $fee->fresh()->valor);
        $this->assertSame('8', $stock->fresh()->valor);
    }

    private function createAdministrator(): Usuario
    {
        $role = Rol::create(['nombre' => 'Administrador']);
        $admin = Usuario::create([
            'nombre' => 'Administrador de prueba',
            'correo' => 'admin@example.test',
            'contrasena_hash' => Hash::make('password'),
            'activo' => true,
        ]);
        $admin->roles()->attach($role->id_rol);

        return $admin;
    }
}
