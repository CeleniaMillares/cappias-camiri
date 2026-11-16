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
use Tests\TestCase;

class InscripcionCursoWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_schema_has_description_and_poster_fields_without_duration(): void
    {
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('cursos', 'descripcion'));
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('cursos', 'imagen_url'));
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('cursos', 'duracion'));
    }

    public function test_course_creation_form_accepts_an_optional_publicity_image(): void
    {
        $directivaRole = Rol::create(['nombre' => 'Directiva']);
        $directiva = $this->createUser('directiva@example.test');
        $directiva->roles()->attach($directivaRole->id_rol);
        $instructor = $this->createUser('instructor@example.test');

        $this->actingAs($directiva)
            ->get('/admin/cursos/crear')
            ->assertOk()
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('name="imagen_url"', false)
            ->assertSee('name="descripcion"', false)
            ->assertSee('name="horarios[0][dia_semana]"', false)
            ->assertSee('Volver a Cursos');
    }

    public function test_directiva_can_create_a_course_with_multiple_groups(): void
    {
        $role = Rol::create(['nombre' => 'Directiva']);
        $directiva = $this->createUser('directiva@example.test');
        $directiva->roles()->attach($role->id_rol);
        $instructor = $this->createUser('instructor@example.test');
        $payload = [
            'id_usuario_instructor' => $instructor->id_usuario,
            'nombre' => 'Curso de prueba',
            'descripcion' => 'Descripción amplia del curso.',
            'costo' => 100,
            'horarios' => [
                [
                    'dia_semana' => 'Lunes y Miércoles',
                    'hora_inicio' => '08:00',
                    'hora_fin' => '12:00',
                    'fecha_inicio' => now()->toDateString(),
                    'fecha_fin' => now()->addMonth()->toDateString(),
                    'cupo_maximo' => 10,
                ],
                [
                    'dia_semana' => 'Martes y Jueves',
                    'hora_inicio' => '14:00',
                    'hora_fin' => '17:00',
                    'fecha_inicio' => now()->toDateString(),
                    'fecha_fin' => now()->addMonth()->toDateString(),
                    'cupo_maximo' => 12,
                ],
            ],
        ];

        $this->actingAs($directiva)
            ->post('/admin/cursos', $payload)
            ->assertRedirect('/admin/cursos')
            ->assertSessionHasNoErrors();

        $curso = Curso::where('nombre', 'Curso De Prueba')->firstOrFail();
        $this->assertSame('Descripción amplia del curso.', $curso->descripcion);
        $this->assertCount(2, $curso->horarios);
        $this->assertSame(
            ['Lunes y Miércoles', 'Martes y Jueves'],
            $curso->horarios->pluck('dia_semana')->sort()->values()->all()
        );
    }

    public function test_course_edit_updates_and_adds_groups_without_deleting_omitted_groups(): void
    {
        $role = Rol::create(['nombre' => 'Directiva']);
        $directiva = $this->createUser('directiva@example.test');
        $directiva->roles()->attach($role->id_rol);
        $instructor = $this->createUser('instructor@example.test');
        $curso = $this->createCourse($instructor);
        $firstSchedule = $this->createSchedule($curso);
        $secondSchedule = $this->createSchedule($curso);

        $this->actingAs($directiva)
            ->get("/admin/cursos/{$curso->id_curso}/editar")
            ->assertOk()
            ->assertSee('Gestionar grupos')
            ->assertSee('name="horarios[0][id_horario_curso]"', false)
            ->assertSee('name="horarios[1][id_horario_curso]"', false);

        $this->actingAs($directiva)
            ->put("/admin/cursos/{$curso->id_curso}", [
                'id_usuario_instructor' => $instructor->id_usuario,
                'nombre' => $curso->nombre,
                'descripcion' => 'Descripción editada.',
                'costo' => $curso->costo,
                'horarios' => [
                    [
                        'id_horario_curso' => $firstSchedule->id_horario_curso,
                        'dia_semana' => 'Lunes',
                        'hora_inicio' => '08:00',
                        'hora_fin' => '12:00',
                        'fecha_inicio' => now()->toDateString(),
                        'fecha_fin' => now()->addMonth()->toDateString(),
                        'cupo_maximo' => $firstSchedule->cupo_maximo,
                    ],
                    [
                        'dia_semana' => 'Viernes',
                        'hora_inicio' => '14:00',
                        'hora_fin' => '17:00',
                        'fecha_inicio' => now()->toDateString(),
                        'fecha_fin' => now()->addMonth()->toDateString(),
                        'cupo_maximo' => 12,
                    ],
                ],
            ])
            ->assertRedirect('/admin/cursos')
            ->assertSessionHasNoErrors();

        $this->assertSame('Descripción editada.', $curso->fresh()->descripcion);
        $this->assertSame('Lunes', $firstSchedule->fresh()->dia_semana);
        $this->assertNotNull($secondSchedule->fresh());
        $this->assertDatabaseCount('horario_cursos', 3);
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
                'costo' => $curso->costo,
                'horarios' => [[
                    'id_horario_curso' => $horario->id_horario_curso,
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

        $this->actingAs($instructor)
            ->get('/admin/alumnos/inscribir')
            ->assertForbidden();

        $this->actingAs($instructor)
            ->post('/admin/alumnos/inscribir', [
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
            ->assertRedirect('/admin/alumnos')
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
            ->assertRedirect('/admin/alumnos')
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
            ->assertRedirect('/admin/alumnos');

        $this->actingAs($directiva)
            ->get('/admin/alumnos')
            ->assertOk()
            ->assertSee('QR')
            ->assertSee('Inscripciones Pendientes de Pago')
            ->assertSee('Confirmar Pago')
            ->assertSee('reservan un cupo');

        $this->actingAs($directiva)
            ->get("/admin/cursos/{$curso->id_curso}/alumnos")
            ->assertOk()
            ->assertSee('QR')
            ->assertSee('cupo reservado');

        $this->actingAs($directiva)
            ->post("/admin/inscripciones/{$inscripcion->id_inscripcion}/confirmar")
            ->assertRedirect('/admin/alumnos')
            ->assertSessionHas('success');

        $this->assertSame('CONFIRMADA', $inscripcion->fresh()->estado);
        $this->assertSame('CONFIRMADO', $inscripcion->pagos()->firstOrFail()->estado);
        Event::assertDispatched(InscripcionConfirmada::class);
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
