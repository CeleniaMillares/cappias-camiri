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
        $this->assertDatabaseHas('pagos', [
            'id_inscripcion' => $inscripcion->id_inscripcion,
            'monto' => 100,
            'metodo' => 'QR',
            'estado' => 'PENDIENTE',
        ]);
        Event::assertNotDispatched(InscripcionConfirmada::class);
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
            ->assertSee('Pendiente de verificación');

        $this->actingAs($directiva)
            ->get("/admin/cursos/{$curso->id_curso}/alumnos")
            ->assertOk()
            ->assertSee('QR')
            ->assertSee('Pendiente');

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

    private function createCourse(Usuario $instructor): Curso
    {
        return Curso::create([
            'id_usuario_instructor' => $instructor->id_usuario,
            'nombre' => 'Curso de prueba',
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
