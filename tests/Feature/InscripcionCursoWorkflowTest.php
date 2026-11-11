<?php

namespace Tests\Feature;

use App\Events\InscripcionConfirmada;
use App\Models\Alumno;
use App\Models\Curso;
use App\Models\HorarioCurso;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\Parametro;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InscripcionCursoWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_qr_checkout_displays_official_qr_and_stays_pending_until_review(): void
    {
        Rol::create(['nombre' => 'Alumno']);
        $instructor = $this->createUser('instructor@example.test');
        $curso = $this->createCourse($instructor);
        $horario = $this->createSchedule($curso);
        Parametro::create([
            'clave' => 'qr_image_url',
            'valor' => 'qr/official.png',
            'descripcion' => 'QR oficial',
        ]);
        Storage::disk('public')->put('qr/official.png', 'fake image content');
        Password::shouldReceive('sendResetLink')
            ->once()
            ->with(['correo' => 'alumno@example.test'])
            ->andReturn(Password::RESET_LINK_SENT);
        Event::fake([InscripcionConfirmada::class]);

        $this->get("/checkout/curso/{$curso->id_curso}")
            ->assertOk()
            ->assertSee('storage/qr/official.png');

        $this->post("/checkout/curso/{$curso->id_curso}", [
            'correo' => 'alumno@example.test',
            'nombres' => 'Alumno de prueba',
            'ci' => '12345678',
            'telefono' => '70000000',
            'id_horario_curso' => $horario->id_horario_curso,
            'metodo_pago' => 'QR',
        ])
            ->assertRedirect('/')
            ->assertSessionHas(
                'success',
                'Inscripción registrada. La Directiva verificará tu pago para confirmar tu cupo.'
            );
        $this->get('/')
            ->assertOk()
            ->assertSee('Inscripción registrada. La Directiva verificará tu pago para confirmar tu cupo.');

        $inscripcion = Inscripcion::firstOrFail();
        $this->assertSame('PENDIENTE_PAGO', $inscripcion->estado);
        $this->assertDatabaseHas('alumnos', [
            'id_alumno' => $inscripcion->id_alumno,
            'id_usuario' => Usuario::where('correo', 'alumno@example.test')->value('id_usuario'),
        ]);
        $this->assertDatabaseHas('pagos', [
            'id_inscripcion' => $inscripcion->id_inscripcion,
            'monto' => 100,
            'metodo' => 'QR',
            'estado' => 'PENDIENTE',
        ]);
        Event::assertNotDispatched(InscripcionConfirmada::class);
    }

    public function test_public_qr_checkout_can_confirm_payment_and_persist_course_commission(): void
    {
        Rol::create(['nombre' => 'Alumno']);
        $instructor = $this->createUser('instructor@example.test');
        $curso = $this->createCourse($instructor);
        $horario = $this->createSchedule($curso);
        Parametro::create([
            'clave' => 'comision_curso',
            'valor' => '20',
            'descripcion' => 'Comisión de cursos',
        ]);
        Password::shouldReceive('sendResetLink')
            ->once()
            ->with(['correo' => 'alumno@example.test'])
            ->andReturn(Password::RESET_LINK_SENT);

        $this->post("/checkout/curso/{$curso->id_curso}", [
            'correo' => 'alumno@example.test',
            'nombres' => 'Alumno de prueba',
            'ci' => '12345678',
            'telefono' => '70000000',
            'id_horario_curso' => $horario->id_horario_curso,
            'metodo_pago' => 'QR',
            'confirmar_qr' => '1',
        ])
            ->assertRedirect('/')
            ->assertSessionHas('success', 'Inscripción confirmada y pago QR registrado.');

        $inscripcion = Inscripcion::firstOrFail();
        $this->assertSame('CONFIRMADA', $inscripcion->estado);
        $this->assertSame('20.00', $inscripcion->fresh()->comision_cappias);
        $this->assertTrue($inscripcion->fresh()->comision_calculada);
        $this->assertDatabaseHas('pagos', [
            'id_inscripcion' => $inscripcion->id_inscripcion,
            'monto' => 100,
            'metodo' => 'QR',
            'estado' => 'CONFIRMADO',
        ]);
    }

    public function test_public_checkout_rejects_duplicate_enrollment_with_a_friendly_message(): void
    {
        Rol::create(['nombre' => 'Alumno']);
        $instructor = $this->createUser('instructor@example.test');
        $curso = $this->createCourse($instructor);
        $horario = $this->createSchedule($curso);
        $alumno = Alumno::create([
            'nombres' => 'Alumno de prueba',
            'ci' => '12345678',
            'telefono' => '70000000',
            'edad' => 20,
        ]);
        Inscripcion::create([
            'id_alumno' => $alumno->id_alumno,
            'id_horario_curso' => $horario->id_horario_curso,
            'estado' => 'PENDIENTE_PAGO',
        ]);

        $this->from("/checkout/curso/{$curso->id_curso}")
            ->post("/checkout/curso/{$curso->id_curso}", [
                'correo' => 'alumno@example.test',
                'nombres' => 'Alumno de prueba',
                'ci' => '12345678',
                'telefono' => '70000000',
                'id_horario_curso' => $horario->id_horario_curso,
                'metodo_pago' => 'QR',
            ])
            ->assertRedirect("/checkout/curso/{$curso->id_curso}")
            ->assertSessionHasErrors([
                'id_horario_curso' => 'Este alumno ya está inscrito en este horario.',
            ]);

        $this->assertDatabaseCount('inscripciones', 1);
    }

    public function test_authenticated_existing_customer_is_given_student_role_and_linked_to_alumno(): void
    {
        $studentRole = Rol::create(['nombre' => 'Alumno']);
        $customerRole = Rol::create(['nombre' => 'Cliente']);
        $user = $this->createUser('cliente@example.test');
        $user->roles()->attach($customerRole->id_rol);
        $instructor = $this->createUser('instructor@example.test');
        $curso = $this->createCourse($instructor);
        $horario = $this->createSchedule($curso);

        $this->actingAs($user)
            ->post("/checkout/curso/{$curso->id_curso}", [
                'correo' => $user->correo,
                'nombres' => 'Cliente de prueba',
                'ci' => '12345678',
                'telefono' => '70000000',
                'id_horario_curso' => $horario->id_horario_curso,
                'metodo_pago' => 'EFECTIVO',
            ])
            ->assertRedirect('/')
            ->assertSessionHas('success');

        $this->assertTrue($user->fresh()->hasRole('Alumno'));
        $this->assertDatabaseHas('alumnos', [
            'ci' => '12345678',
            'id_usuario' => $user->id_usuario,
        ]);
        $this->assertDatabaseHas('rol_usuario', [
            'id_rol' => $studentRole->id_rol,
            'id_usuario' => $user->id_usuario,
        ]);
    }

    public function test_inactive_course_cannot_be_opened_or_submitted_through_public_checkout(): void
    {
        $instructor = $this->createUser('instructor@example.test');
        $curso = $this->createCourse($instructor);
        $horario = $this->createSchedule($curso);
        $curso->update(['activo' => false]);

        $this->get("/checkout/curso/{$curso->id_curso}")->assertNotFound();

        $this->post("/checkout/curso/{$curso->id_curso}", [
            'correo' => 'alumno@example.test',
            'nombres' => 'Alumno de prueba',
            'ci' => '12345678',
            'telefono' => '70000000',
            'id_horario_curso' => $horario->id_horario_curso,
            'metodo_pago' => 'QR',
        ])->assertNotFound();

        $this->assertDatabaseCount('inscripciones', 0);
    }

    public function test_course_edit_rejects_end_date_before_start_date(): void
    {
        $directivaRole = Rol::create(['nombre' => 'Directiva']);
        $directiva = $this->createUser('directiva@example.test');
        $directiva->roles()->attach($directivaRole->id_rol);
        $instructor = $this->createUser('instructor@example.test');
        $curso = $this->createCourse($instructor);
        $horario = $this->createSchedule($curso);

        $this->actingAs($directiva)
            ->from("/admin/cursos/{$curso->id_curso}/editar")
            ->put("/admin/cursos/{$curso->id_curso}", [
                'id_usuario_instructor' => $instructor->id_usuario,
                'nombre' => $curso->nombre,
                'duracion' => $curso->duracion,
                'costo' => $curso->costo,
                'horarios' => [[
                    'dia_semana' => 'Sabado',
                    'hora_inicio' => '09:00',
                    'hora_fin' => '12:00',
                    'fecha_inicio' => now()->toDateString(),
                    'fecha_fin' => now()->subDay()->toDateString(),
                    'cupo_maximo' => $horario->cupo_maximo,
                ]],
            ])
            ->assertRedirect("/admin/cursos/{$curso->id_curso}/editar")
            ->assertSessionHasErrors('horarios.0.fecha_fin');

        $this->assertSame(now()->addMonth()->toDateString(), $horario->fresh()->fecha_fin);
    }

    public function test_instructor_cannot_create_or_submit_enrollment_for_another_instructors_course(): void
    {
        $role = Rol::create(['nombre' => 'Socio/Instructor']);
        $instructor = $this->createUser('instructor@example.test');
        $instructor->roles()->attach($role->id_rol);
        $otherInstructor = $this->createUser('otro-instructor@example.test');
        $cursoAjeno = $this->createCourse($otherInstructor);
        $horarioAjeno = $this->createSchedule($cursoAjeno);

        $this->actingAs($instructor)
            ->get("/admin/inscripciones/crear/{$horarioAjeno->id_horario_curso}")
            ->assertForbidden();

        $this->actingAs($instructor)
            ->post('/admin/inscripciones', [
                'id_horario_curso' => $horarioAjeno->id_horario_curso,
                'nombres' => 'Alumno',
                'ci' => '12345678',
                'telefono' => '70000000',
                'edad' => 20,
                'metodo_pago' => 'EFECTIVO',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('inscripciones', 0);
    }

    public function test_directiva_can_confirm_an_enrollment_payment_during_registration(): void
    {
        $directivaRole = Rol::create(['nombre' => 'Directiva']);
        $directiva = $this->createUser('directiva@example.test');
        $directiva->roles()->attach($directivaRole->id_rol);
        $instructor = $this->createUser('instructor@example.test');
        $curso = $this->createCourse($instructor);
        $horario = $this->createSchedule($curso);
        Event::fake([InscripcionConfirmada::class]);

        $this->actingAs($directiva)
            ->post('/admin/inscripciones', [
                'id_horario_curso' => $horario->id_horario_curso,
                'nombres' => 'Alumno confirmado',
                'ci' => '12345678',
                'telefono' => '70000000',
                'edad' => 20,
                'metodo_pago' => 'QR',
                'confirmar_qr' => '1',
            ])
            ->assertRedirect('/admin/inscripciones')
            ->assertSessionHasNoErrors();

        $inscripcion = Inscripcion::firstOrFail();
        $this->assertSame('CONFIRMADA', $inscripcion->estado);
        $this->assertDatabaseHas('pagos', [
            'id_inscripcion' => $inscripcion->id_inscripcion,
            'monto' => 100,
            'metodo' => 'QR',
            'estado' => 'CONFIRMADO',
        ]);
        Event::assertDispatched(InscripcionConfirmada::class);
    }

    public function test_directiva_registration_without_qr_confirmation_stays_pending(): void
    {
        $directivaRole = Rol::create(['nombre' => 'Directiva']);
        $directiva = $this->createUser('directiva@example.test');
        $directiva->roles()->attach($directivaRole->id_rol);
        $instructor = $this->createUser('instructor@example.test');
        $curso = $this->createCourse($instructor);
        $horario = $this->createSchedule($curso);
        Event::fake([InscripcionConfirmada::class]);

        $this->actingAs($directiva)
            ->post('/admin/inscripciones', [
                'id_horario_curso' => $horario->id_horario_curso,
                'nombres' => 'Alumno pendiente',
                'ci' => '12345678',
                'telefono' => '70000000',
                'edad' => 20,
                'metodo_pago' => 'QR',
            ])
            ->assertRedirect('/admin/inscripciones')
            ->assertSessionHasNoErrors();

        $inscripcion = Inscripcion::firstOrFail();
        $this->assertSame('PENDIENTE_PAGO', $inscripcion->estado);
        $this->assertDatabaseHas('pagos', [
            'id_inscripcion' => $inscripcion->id_inscripcion,
            'monto' => 100,
            'metodo' => 'QR',
            'estado' => 'PENDIENTE',
        ]);
        Event::assertNotDispatched(InscripcionConfirmada::class);
    }

    public function test_course_schedule_capacity_obeys_configured_minimum_and_maximum_on_create_and_update(): void
    {
        $directivaRole = Rol::create(['nombre' => 'Directiva']);
        $directiva = $this->createUser('directiva@example.test');
        $directiva->roles()->attach($directivaRole->id_rol);
        $instructor = $this->createUser('instructor@example.test');
        Parametro::create(['clave' => 'cupo_minimo', 'valor' => '6']);
        Parametro::create(['clave' => 'cupo_maximo', 'valor' => '15']);

        $payload = [
            'id_usuario_instructor' => $instructor->id_usuario,
            'nombre' => 'Curso de prueba',
            'duracion' => '4 sesiones',
            'costo' => 100,
            'horarios' => [[
                'dia_semana' => 'Sabado',
                'hora_inicio' => '09:00',
                'hora_fin' => '12:00',
                'fecha_inicio' => now()->toDateString(),
                'fecha_fin' => now()->addMonth()->toDateString(),
                'cupo_maximo' => 5,
            ]],
        ];

        $this->actingAs($directiva)
            ->from('/admin/cursos/crear')
            ->post('/admin/cursos', $payload)
            ->assertRedirect('/admin/cursos/crear')
            ->assertSessionHasErrors('horarios.0.cupo_maximo');

        $curso = $this->createCourse($instructor);
        $horario = $this->createSchedule($curso);
        $payload['horarios'][0]['cupo_maximo'] = 16;

        $this->actingAs($directiva)
            ->from("/admin/cursos/{$curso->id_curso}/editar")
            ->put("/admin/cursos/{$curso->id_curso}", $payload)
            ->assertRedirect("/admin/cursos/{$curso->id_curso}/editar")
            ->assertSessionHasErrors('horarios.0.cupo_maximo');

        $this->assertSame(10, $horario->fresh()->cupo_maximo);
    }

    public function test_student_profile_lists_only_inscriptions_linked_to_the_authenticated_account(): void
    {
        $studentRole = Rol::create(['nombre' => 'Alumno']);
        $user = $this->createUser('alumno@example.test');
        $user->update(['nombre' => 'Persona Compartida']);
        $user->roles()->attach($studentRole->id_rol);
        $otherUser = $this->createUser('otro-alumno@example.test');
        $otherUser->update(['nombre' => 'Persona Compartida Otro']);

        $alumno = Alumno::create([
            'id_usuario' => $user->id_usuario,
            'nombres' => 'Persona Compartida',
            'ci' => '12345678',
            'telefono' => '70000000',
            'edad' => 20,
        ]);
        $otherAlumno = Alumno::create([
            'id_usuario' => $otherUser->id_usuario,
            'nombres' => 'Persona Compartida Otro',
            'ci' => '87654321',
            'telefono' => '70000001',
            'edad' => 21,
        ]);

        $instructor = $this->createUser('instructor@example.test');
        $ownCourse = $this->createCourse($instructor, 'Curso propio');
        $otherCourse = $this->createCourse($instructor, 'Curso ajeno');
        $ownSchedule = $this->createSchedule($ownCourse);
        $otherSchedule = $this->createSchedule($otherCourse);
        Inscripcion::create([
            'id_alumno' => $alumno->id_alumno,
            'id_horario_curso' => $ownSchedule->id_horario_curso,
            'estado' => 'PENDIENTE_PAGO',
        ]);
        Inscripcion::create([
            'id_alumno' => $otherAlumno->id_alumno,
            'id_horario_curso' => $otherSchedule->id_horario_curso,
            'estado' => 'PENDIENTE_PAGO',
        ]);

        $this->actingAs($user)
            ->get('/admin/mi-perfil')
            ->assertOk()
            ->assertSee('Curso propio')
            ->assertDontSee('Curso ajeno');
    }

    public function test_student_profile_displays_unrecognized_enrollment_states_explicitly(): void
    {
        $studentRole = Rol::create(['nombre' => 'Alumno']);
        $user = $this->createUser('alumno@example.test');
        $user->roles()->attach($studentRole->id_rol);
        $alumno = Alumno::create([
            'id_usuario' => $user->id_usuario,
            'nombres' => 'Alumno de prueba',
            'ci' => '12345678',
            'telefono' => '70000000',
            'edad' => 20,
        ]);
        $instructor = $this->createUser('instructor@example.test');
        $curso = $this->createCourse($instructor);
        $horario = $this->createSchedule($curso);
        Inscripcion::create([
            'id_alumno' => $alumno->id_alumno,
            'id_horario_curso' => $horario->id_horario_curso,
            'estado' => 'RECHAZADA',
        ]);

        $this->actingAs($user)
            ->get('/admin/mi-perfil')
            ->assertOk()
            ->assertSee('RECHAZADA')
            ->assertDontSee('Pendiente de Pago');
    }

    public function test_directiva_confirms_registered_payment_and_sees_payment_method_in_both_lists(): void
    {
        $directivaRole = Rol::create(['nombre' => 'Directiva']);
        $directiva = $this->createUser('directiva@example.test');
        $directiva->roles()->attach($directivaRole->id_rol);
        $instructor = $this->createUser('instructor@example.test');
        $curso = $this->createCourse($instructor);
        $horario = $this->createSchedule($curso);
        $alumno = Alumno::create([
            'nombres' => 'Alumno de prueba',
            'ci' => '87654321',
            'telefono' => '70000001',
            'edad' => 20,
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
        Event::fake([InscripcionConfirmada::class]);

        $this->actingAs($directiva)
            ->get('/admin/inscripciones')
            ->assertOk()
            ->assertSee('QR')
            ->assertSee('Pendiente de verificación')
            ->assertSee('cupo reservado');

        $this->actingAs($directiva)
            ->get("/admin/cursos/{$curso->id_curso}/alumnos")
            ->assertOk()
            ->assertSee('QR')
            ->assertSee('cupo reservado');

        $this->actingAs($directiva)
            ->post("/admin/inscripciones/{$inscripcion->id_inscripcion}/confirmar")
            ->assertRedirect('/admin/inscripciones')
            ->assertSessionHas('success');

        $this->assertSame('CONFIRMADA', $inscripcion->fresh()->estado);
        $this->assertSame('CONFIRMADO', $inscripcion->pagos()->firstOrFail()->estado);
        Event::assertDispatched(InscripcionConfirmada::class);
    }

    public function test_newly_registered_student_can_use_the_password_reset_link(): void
    {
        $user = $this->createUser('alumno@example.test');
        $token = Password::createToken($user);

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->correo]))
            ->assertOk()
            ->assertSee($user->correo);

        $this->post(route('password.update'), [
            'token' => $token,
            'correo' => $user->correo,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
            ->assertRedirect('/login')
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->contrasena_hash));
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

    private function createCourse(Usuario $instructor, string $name = 'Curso de prueba'): Curso
    {
        return Curso::create([
            'id_usuario_instructor' => $instructor->id_usuario,
            'nombre' => $name,
            'duracion' => '4 sesiones',
            'costo' => 100,
            'activo' => true,
        ]);
    }

    private function createSchedule(Curso $curso): HorarioCurso
    {
        return HorarioCurso::create([
            'id_curso' => $curso->id_curso,
            'dia_semana' => 'Sabado',
            'hora_inicio' => '09:00',
            'hora_fin' => '12:00',
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonth()->toDateString(),
            'cupo_maximo' => 10,
        ]);
    }
}
