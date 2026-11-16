<?php

namespace Tests\Feature;

use App\Events\InscripcionConfirmada;
use App\Models\Alumno;
use App\Models\Curso;
use App\Models\HorarioCurso;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AlumnoManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_alumno_index_searches_students_and_lists_pending_enrollments(): void
    {
        [$directiva, $curso, $horario] = $this->createFixtures();
        $alumno = Alumno::create([
            'nombres' => 'Andrea Artesana',
            'ci' => '12345678',
            'telefono' => '70000000',
            'edad' => 20,
            'activo' => true,
        ]);
        $inscripcion = Inscripcion::create([
            'id_alumno' => $alumno->id_alumno,
            'id_horario_curso' => $horario->id_horario_curso,
            'estado' => 'PENDIENTE_PAGO',
        ]);
        Pago::create([
            'id_inscripcion' => $inscripcion->id_inscripcion,
            'monto' => 100,
            'metodo' => 'QR',
            'estado' => 'PENDIENTE',
        ]);

        $this->actingAs($directiva)
            ->get('/admin/alumnos?buscar=12345678')
            ->assertOk()
            ->assertSee('Andrea Artesana')
            ->assertSee('Inscripciones Pendientes de Pago')
            ->assertSee($curso->nombre)
            ->assertSee('QR');
    }

    public function test_new_alumno_inscription_selects_schedule_and_registers_pending_payment(): void
    {
        [$directiva, $curso, $horario] = $this->createFixtures();

        $this->actingAs($directiva)
            ->get('/admin/alumnos/inscribir')
            ->assertOk()
            ->assertSee($curso->nombre)
            ->assertSee('name="id_curso"', false)
            ->assertSee('name="id_horario_curso"', false);

        $this->post('/admin/alumnos/inscribir', [
            'id_curso' => $curso->id_curso,
            'id_horario_curso' => $horario->id_horario_curso,
            'nombres' => 'Alumno Nuevo',
            'ci' => '87654321',
            'telefono' => '70000001',
            'edad' => 18,
            'metodo_pago' => 'QR',
        ])->assertRedirect('/admin/alumnos')
            ->assertSessionHas('success');

        $alumno = Alumno::where('ci', '87654321')->firstOrFail();
        $inscripcion = Inscripcion::where('id_alumno', $alumno->id_alumno)->firstOrFail();

        $this->assertSame(1, Usuario::count());
        $this->assertSame('PENDIENTE_PAGO', $inscripcion->estado);
        $this->assertDatabaseHas('pagos', [
            'id_inscripcion' => $inscripcion->id_inscripcion,
            'monto' => 75,
            'metodo' => 'QR',
            'estado' => 'PENDIENTE',
        ]);
    }

    public function test_enrollment_requires_a_group_belonging_to_the_selected_course(): void
    {
        [$directiva, , $horario] = $this->createFixtures();
        $anotherCourse = Curso::create([
            'id_usuario_instructor' => $directiva->id_usuario,
            'nombre' => 'Otro curso',
            'costo' => 100,
            'activo' => true,
        ]);
        HorarioCurso::create([
            'id_curso' => $anotherCourse->id_curso,
            'dia_semana' => 'Domingo',
            'hora_inicio' => '09:00',
            'hora_fin' => '12:00',
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonth()->toDateString(),
            'cupo_maximo' => 10,
        ]);

        $this->actingAs($directiva)
            ->from('/admin/alumnos/inscribir')
            ->post('/admin/alumnos/inscribir', [
                'id_curso' => $anotherCourse->id_curso,
                'id_horario_curso' => $horario->id_horario_curso,
                'nombres' => 'Alumno Nuevo',
                'ci' => '87654321',
                'telefono' => '70000001',
                'edad' => 18,
                'metodo_pago' => 'QR',
            ])
            ->assertRedirect('/admin/alumnos/inscribir')
            ->assertSessionHasErrors('id_horario_curso');

        $this->assertDatabaseCount('inscripciones', 0);
    }

    public function test_new_alumno_inscription_confirms_cash_and_checked_qr_payments(): void
    {
        [$directiva, , $horario] = $this->createFixtures();
        Event::fake([InscripcionConfirmada::class]);

        $this->actingAs($directiva)->post('/admin/alumnos/inscribir', [
            'id_curso' => $horario->id_curso,
            'id_horario_curso' => $horario->id_horario_curso,
            'nombres' => 'Alumno Efectivo',
            'ci' => '11110000',
            'telefono' => '70000011',
            'edad' => 20,
            'metodo_pago' => 'EFECTIVO',
        ])->assertRedirect('/admin/alumnos');

        $inscripcionEfectivo = Inscripcion::whereHas('alumno', fn ($query) => $query->where('ci', '11110000'))
            ->firstOrFail();
        $this->assertSame('CONFIRMADA', $inscripcionEfectivo->estado);
        $this->assertDatabaseHas('pagos', [
            'id_inscripcion' => $inscripcionEfectivo->id_inscripcion,
            'metodo' => 'EFECTIVO',
            'estado' => 'CONFIRMADO',
        ]);
        Event::assertDispatched(InscripcionConfirmada::class, 1);

        $this->actingAs($directiva)->post('/admin/alumnos/inscribir', [
            'id_curso' => $horario->id_curso,
            'id_horario_curso' => $horario->id_horario_curso,
            'nombres' => 'Alumno QR confirmado',
            'ci' => '22220000',
            'telefono' => '70000022',
            'edad' => 20,
            'metodo_pago' => 'QR',
            'confirmar_qr' => '1',
        ])->assertRedirect('/admin/alumnos');

        $inscripcionQr = Inscripcion::whereHas('alumno', fn ($query) => $query->where('ci', '22220000'))
            ->firstOrFail();
        $this->assertSame('CONFIRMADA', $inscripcionQr->estado);
        $this->assertDatabaseHas('pagos', [
            'id_inscripcion' => $inscripcionQr->id_inscripcion,
            'metodo' => 'QR',
            'estado' => 'CONFIRMADO',
        ]);

        $this->actingAs($directiva)->post('/admin/alumnos/inscribir', [
            'id_curso' => $horario->id_curso,
            'id_horario_curso' => $horario->id_horario_curso,
            'nombres' => 'Alumno mixto confirmado',
            'ci' => '33330000',
            'telefono' => '70000033',
            'edad' => 20,
            'metodo_pago' => 'MIXTO',
            'monto_efectivo' => 20,
            'confirmar_qr' => '1',
        ])->assertRedirect('/admin/alumnos');

        $inscripcionMixta = Inscripcion::whereHas('alumno', fn ($query) => $query->where('ci', '33330000'))
            ->firstOrFail();
        $this->assertSame('CONFIRMADA', $inscripcionMixta->estado);
        $this->assertDatabaseHas('pagos', [
            'id_inscripcion' => $inscripcionMixta->id_inscripcion,
            'metodo' => 'EFECTIVO',
            'estado' => 'CONFIRMADO',
        ]);
        $this->assertDatabaseHas('pagos', [
            'id_inscripcion' => $inscripcionMixta->id_inscripcion,
            'metodo' => 'QR',
            'estado' => 'CONFIRMADO',
        ]);
        Event::assertDispatched(InscripcionConfirmada::class, 3);
    }

    public function test_directiva_can_update_student_record_and_view_course_history(): void
    {
        [$directiva, , $horario] = $this->createFixtures();
        $alumno = Alumno::create([
            'nombres' => 'Nombre Anterior',
            'ci' => '11112222',
            'telefono' => '70000002',
            'edad' => 19,
            'activo' => true,
        ]);
        Inscripcion::create([
            'id_alumno' => $alumno->id_alumno,
            'id_horario_curso' => $horario->id_horario_curso,
            'estado' => 'CONFIRMADA',
        ]);

        $this->actingAs($directiva)
            ->put("/admin/alumnos/{$alumno->id_alumno}", [
                'nombres' => 'Nombre Actualizado',
                'ci' => '11112222',
                'telefono' => '70000003',
                'edad' => 21,
            ])
            ->assertRedirect("/admin/alumnos/{$alumno->id_alumno}/editar")
            ->assertSessionHas('success');

        $this->assertSame('Nombre Actualizado', $alumno->fresh()->nombres);

        $this->get("/admin/alumnos/{$alumno->id_alumno}/historial")
            ->assertOk()
            ->assertSee('CONFIRMADA');
    }

    public function test_alumno_records_do_not_have_a_user_account_column(): void
    {
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('alumnos', 'id_usuario'));
    }

    private function createFixtures(): array
    {
        $role = Rol::create(['nombre' => 'Directiva']);
        $directiva = Usuario::create([
            'nombre' => 'Directiva de prueba',
            'correo' => 'directiva@example.test',
            'contrasena_hash' => Hash::make('password'),
            'activo' => true,
        ]);
        $directiva->roles()->attach($role->id_rol);

        $curso = Curso::create([
            'id_usuario_instructor' => $directiva->id_usuario,
            'nombre' => 'Curso Artesanal',
            'costo' => 75,
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

        return [$directiva, $curso, $horario];
    }
}
