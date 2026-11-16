<?php

namespace Tests\Feature;

use App\Models\Feria;
use App\Models\Rol;
use App\Models\Socio;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FeriaSociosWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_pivot_uses_composite_keys_foreign_keys_and_confirmed_default(): void
    {
        $this->assertTrue(Schema::hasColumn('feria_socio', 'id_feria'));
        $this->assertTrue(Schema::hasColumn('feria_socio', 'id_socio'));
        $this->assertTrue(Schema::hasColumn('feria_socio', 'estado'));
        $this->assertTrue(Schema::hasColumn('feria_socio', 'created_at'));
        $this->assertTrue(Schema::hasColumn('feria_socio', 'updated_at'));

        [$feria, $socio] = $this->createFeriaAndSocio();
        $feria->socios()->attach($socio->id_socio);

        $this->assertDatabaseHas('feria_socio', [
            'id_feria' => $feria->id_feria,
            'id_socio' => $socio->id_socio,
            'estado' => 'CONFIRMADO',
        ]);
        $this->assertTrue($socio->fresh()->ferias->contains('id_feria', $feria->id_feria));
    }

    public function test_directiva_can_assign_change_status_and_remove_fair_participants(): void
    {
        $role = Rol::create(['nombre' => 'Directiva']);
        $directiva = $this->createUser('Directiva', 'directiva@example.test');
        $directiva->roles()->attach($role->id_rol);
        $feria = $this->createFeria();
        $socioPendiente = $this->createSocio('Socio Pendiente', 'pendiente@example.test', '1234567');
        $socioConfirmado = $this->createSocio('Socio Confirmado', 'confirmado@example.test', '2345678');

        $this->actingAs($directiva)
            ->get("/admin/ferias/{$feria->id_feria}/editar")
            ->assertOk()
            ->assertSee('Socios participantes')
            ->assertSee('Socio Pendiente')
            ->assertSee('name="socios[]"', false);

        $this->put("/admin/ferias/{$feria->id_feria}", [
            'nombre' => $feria->nombre,
            'fecha' => $feria->fecha,
            'lugar' => $feria->lugar,
            'gestionar_socios' => '1',
            'socios' => [$socioPendiente->id_socio, $socioConfirmado->id_socio],
            'estados' => [
                $socioPendiente->id_socio => 'PENDIENTE',
                $socioConfirmado->id_socio => 'CONFIRMADO',
            ],
        ])->assertRedirect('/admin/ferias')->assertSessionHasNoErrors();

        $this->assertDatabaseHas('feria_socio', [
            'id_feria' => $feria->id_feria,
            'id_socio' => $socioPendiente->id_socio,
            'estado' => 'PENDIENTE',
        ]);
        $this->assertDatabaseHas('feria_socio', [
            'id_feria' => $feria->id_feria,
            'id_socio' => $socioConfirmado->id_socio,
            'estado' => 'CONFIRMADO',
        ]);

        $this->put("/admin/ferias/{$feria->id_feria}", [
            'nombre' => $feria->nombre,
            'fecha' => $feria->fecha,
            'lugar' => $feria->lugar,
            'gestionar_socios' => '1',
        ])->assertRedirect('/admin/ferias');

        $this->assertDatabaseCount('feria_socio', 0);
    }

    public function test_member_can_request_a_future_fair_and_repeat_request_does_not_change_status(): void
    {
        $role = Rol::create(['nombre' => 'Socio/Instructor']);
        $socio = $this->createSocio('Socio Solicitante', 'socio@example.test', '3456789');
        $socio->usuario->roles()->attach($role->id_rol);
        $feria = $this->createFeria();

        $this->actingAs($socio->usuario)
            ->get('/admin/mi-perfil-socio')
            ->assertOk()
            ->assertSee($feria->nombre)
            ->assertSee('Solicitar Participación');

        $this->actingAs($socio->usuario)
            ->from('/admin/mi-perfil-socio')
            ->post("/perfil/socio/feria/{$feria->id_feria}")
            ->assertRedirect('/admin/mi-perfil-socio')
            ->assertSessionHas('success', 'Tu solicitud para participar en la feria fue registrada.');

        $this->assertDatabaseHas('feria_socio', [
            'id_feria' => $feria->id_feria,
            'id_socio' => $socio->id_socio,
            'estado' => 'PENDIENTE',
        ]);

        DB::table('feria_socio')
            ->where('id_feria', $feria->id_feria)
            ->where('id_socio', $socio->id_socio)
            ->update(['estado' => 'CONFIRMADO']);

        $this->actingAs($socio->usuario)
            ->from('/admin/mi-perfil-socio')
            ->post("/perfil/socio/feria/{$feria->id_feria}")
            ->assertRedirect('/admin/mi-perfil-socio')
            ->assertSessionHas('success', 'Ya tienes una participación en esta feria con estado CONFIRMADO.');

        $this->assertDatabaseHas('feria_socio', [
            'id_feria' => $feria->id_feria,
            'id_socio' => $socio->id_socio,
            'estado' => 'CONFIRMADO',
        ]);
        $this->assertDatabaseCount('feria_socio', 1);
    }

    public function test_member_cannot_request_participation_in_a_past_fair(): void
    {
        $role = Rol::create(['nombre' => 'Socio/Instructor']);
        $socio = $this->createSocio('Socio Solicitante', 'socio@example.test', '4567890');
        $socio->usuario->roles()->attach($role->id_rol);
        $feria = $this->createFeria(now()->subDay()->toDateString());

        $this->actingAs($socio->usuario)
            ->post("/perfil/socio/feria/{$feria->id_feria}")
            ->assertNotFound();

        $this->assertDatabaseCount('feria_socio', 0);
    }

    private function createFeria(?string $fecha = null): Feria
    {
        return Feria::create([
            'nombre' => 'Feria de Artesanías',
            'fecha' => $fecha ?? now()->addWeek()->toDateString(),
            'lugar' => 'Camiri',
        ]);
    }

    private function createFeriaAndSocio(): array
    {
        return [
            $this->createFeria(),
            $this->createSocio('Socio de Prueba', 'prueba@example.test', '5678901'),
        ];
    }

    private function createSocio(string $nombre, string $correo, string $ci): Socio
    {
        $user = $this->createUser($nombre, $correo);

        return Socio::create([
            'id_usuario' => $user->id_usuario,
            'ci' => $ci,
            'telefono' => '70000000',
            'fecha_inscripcion' => now()->toDateString(),
            'estado' => 'ACTIVO',
        ]);
    }

    private function createUser(string $nombre, string $correo): Usuario
    {
        return Usuario::create([
            'nombre' => $nombre,
            'correo' => $correo,
            'contrasena_hash' => Hash::make('password'),
            'activo' => true,
        ]);
    }
}
