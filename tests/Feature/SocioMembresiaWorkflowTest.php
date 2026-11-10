<?php

namespace Tests\Feature;

use App\Models\CuotaSocio;
use App\Models\Parametro;
use App\Models\Rol;
use App\Models\Socio;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SocioMembresiaWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_enrollment_persists_cash_and_mixed_payment_amounts(): void
    {
        $directiva = $this->createDirectiva();
        Parametro::create(['clave' => 'monto_inscripcion', 'valor' => '100']);

        $this->actingAs($directiva)->post('/admin/socios', [
            'nombre' => 'Socio efectivo',
            'correo' => 'efectivo@example.test',
            'contrasena' => 'password',
            'ci' => '1111111',
            'telefono' => '70000001',
            'metodo_pago' => 'EFECTIVO',
        ])->assertRedirect('/admin/socios');

        $this->assertEquals(100, (float) CuotaSocio::whereHas('socio', function ($query) {
            $query->where('ci', '1111111');
        })->firstOrFail()->monto_efectivo);

        $this->actingAs($directiva)->post('/admin/socios', [
            'nombre' => 'Socio mixto',
            'correo' => 'mixto@example.test',
            'contrasena' => 'password',
            'ci' => '2222222',
            'telefono' => '70000002',
            'metodo_pago' => 'MIXTO',
            'monto_efectivo' => 35,
        ])->assertRedirect('/admin/socios');

        $cuotaMixta = CuotaSocio::whereHas('socio', function ($query) {
            $query->where('ci', '2222222');
        })->firstOrFail();

        $this->assertEquals(35, (float) $cuotaMixta->monto_efectivo);
        $this->assertEquals(65, (float) ($cuotaMixta->monto - $cuotaMixta->monto_efectivo));
    }

    public function test_enrollment_rejects_mixed_cash_above_registration_fee(): void
    {
        $directiva = $this->createDirectiva();
        Parametro::create(['clave' => 'monto_inscripcion', 'valor' => '100']);

        $this->actingAs($directiva)
            ->from('/admin/socios/crear')
            ->post('/admin/socios', [
                'nombre' => 'Socio de prueba',
                'correo' => 'socio@example.test',
                'contrasena' => 'password',
                'ci' => '3333333',
                'telefono' => '70000003',
                'metodo_pago' => 'MIXTO',
                'monto_efectivo' => 101,
            ])
            ->assertRedirect('/admin/socios/crear')
            ->assertSessionHasErrors('monto_efectivo');

        $this->assertDatabaseCount('socios', 0);
    }

    public function test_monthly_payment_rejects_mixed_cash_above_total(): void
    {
        $directiva = $this->createDirectiva();
        $socio = $this->createSocio();
        Parametro::create(['clave' => 'monto_mensualidad', 'valor' => '50']);

        $this->actingAs($directiva)
            ->from("/admin/socios/{$socio->id_socio}/pago-mensual")
            ->post("/admin/socios/{$socio->id_socio}/pago-mensual", [
                'periodo_inicio' => '2027-01',
                'meses' => 2,
                'metodo_pago' => 'MIXTO',
                'monto_efectivo' => 101,
            ])
            ->assertRedirect("/admin/socios/{$socio->id_socio}/pago-mensual")
            ->assertSessionHasErrors('monto_efectivo');

        $this->assertDatabaseCount('cuotas_socios', 0);
    }

    public function test_monthly_payment_skips_existing_pending_periods(): void
    {
        $directiva = $this->createDirectiva();
        $socio = $this->createSocio();
        Parametro::create(['clave' => 'monto_mensualidad', 'valor' => '50']);
        CuotaSocio::create([
            'id_socio' => $socio->id_socio,
            'tipo' => 'MENSUALIDAD',
            'metodo' => 'QR',
            'estado' => 'PENDIENTE',
            'periodo' => '2027-01',
            'monto' => 50,
            'monto_efectivo' => 0,
            'fecha_pago' => '2026-11-10',
        ]);

        $this->actingAs($directiva)
            ->post("/admin/socios/{$socio->id_socio}/pago-mensual", [
                'periodo_inicio' => '2027-01',
                'meses' => 2,
                'metodo_pago' => 'EFECTIVO',
            ])
            ->assertRedirect('/admin/socios')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('cuotas_socios', 3);
        $this->assertDatabaseHas('cuotas_socios', [
            'id_socio' => $socio->id_socio,
            'periodo' => '2027-02',
        ]);
        $this->assertDatabaseHas('cuotas_socios', [
            'id_socio' => $socio->id_socio,
            'periodo' => '2027-03',
        ]);
    }

    public function test_monthly_payment_supports_120_months(): void
    {
        $directiva = $this->createDirectiva();
        $socio = $this->createSocio();
        Parametro::create(['clave' => 'monto_mensualidad', 'valor' => '50']);

        $this->actingAs($directiva)
            ->post("/admin/socios/{$socio->id_socio}/pago-mensual", [
                'periodo_inicio' => '2027-01',
                'meses' => 120,
                'metodo_pago' => 'EFECTIVO',
            ])
            ->assertRedirect('/admin/socios')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('cuotas_socios', 120);
        $this->assertDatabaseHas('cuotas_socios', [
            'id_socio' => $socio->id_socio,
            'periodo' => '2036-12',
        ]);
    }

    public function test_monthly_periods_advance_consecutively_from_month_end_dates(): void
    {
        $directiva = $this->createDirectiva();
        $socio = $this->createSocio();
        Parametro::create(['clave' => 'monto_mensualidad', 'valor' => '50']);
        $this->travelTo(Carbon::parse('2026-01-31 12:00:00'));

        $this->actingAs($directiva)
            ->post("/admin/socios/{$socio->id_socio}/pago-mensual", [
                'periodo_inicio' => '2027-01',
                'meses' => 3,
                'metodo_pago' => 'EFECTIVO',
            ])
            ->assertRedirect('/admin/socios')
            ->assertSessionHasNoErrors();

        foreach (['2027-01', '2027-02', '2027-03'] as $periodo) {
            $this->assertDatabaseHas('cuotas_socios', [
                'id_socio' => $socio->id_socio,
                'periodo' => $periodo,
            ]);
        }
    }

    public function test_member_profile_loads_membership_dues(): void
    {
        $socio = $this->createSocio();
        CuotaSocio::create([
            'id_socio' => $socio->id_socio,
            'tipo' => 'MATRICULA',
            'metodo' => 'EFECTIVO',
            'estado' => 'CONFIRMADO',
            'monto' => 100,
            'monto_efectivo' => 100,
            'fecha_pago' => '2026-11-10',
        ]);

        $this->actingAs($socio->usuario)
            ->get('/admin/mi-perfil-socio')
            ->assertOk()
            ->assertSee('MATRICULA');
    }

    public function test_individual_payment_confirmation_is_safe_to_repeat(): void
    {
        $directiva = $this->createDirectiva();
        $socio = $this->createSocio();
        $cuota = CuotaSocio::create([
            'id_socio' => $socio->id_socio,
            'tipo' => 'MENSUALIDAD',
            'metodo' => 'QR',
            'estado' => 'PENDIENTE',
            'periodo' => '2027-01',
            'monto' => 50,
            'monto_efectivo' => 0,
            'fecha_pago' => '2026-11-10',
        ]);

        $this->actingAs($directiva)
            ->post("/admin/socios/confirmar-pago/{$cuota->id_cuota}")
            ->assertRedirect()
            ->assertSessionHas('success', 'Pago QR confirmado correctamente.');

        $this->post("/admin/socios/confirmar-pago/{$cuota->id_cuota}")
            ->assertRedirect()
            ->assertSessionHas('success', 'Este pago ya estaba confirmado.');

        $this->assertSame('CONFIRMADO', $cuota->fresh()->estado);
    }

    private function createDirectiva(): Usuario
    {
        $role = Rol::create(['nombre' => 'Directiva']);
        Rol::create(['nombre' => 'Socio/Instructor']);

        $user = Usuario::create([
            'nombre' => 'Directiva de prueba',
            'correo' => 'directiva@example.test',
            'contrasena_hash' => Hash::make('password'),
            'activo' => true,
        ]);
        $user->roles()->attach($role->id_rol);

        return $user;
    }

    private function createSocio(): Socio
    {
        $role = Rol::firstOrCreate(['nombre' => 'Socio/Instructor']);
        $user = Usuario::create([
            'nombre' => 'Socio de prueba',
            'correo' => 'socio@example.test',
            'contrasena_hash' => Hash::make('password'),
            'activo' => true,
        ]);
        $user->roles()->attach($role->id_rol);

        return Socio::create([
            'id_usuario' => $user->id_usuario,
            'ci' => '12345678',
            'telefono' => '70000000',
            'fecha_inscripcion' => '2026-01-01',
            'estado' => 'ACTIVO',
        ]);
    }
}
