<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Categoria;
use App\Models\Curso;
use App\Models\Feria;
use App\Models\HorarioCurso;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Socio;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TextQualityWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_named_models_save_names_in_title_case(): void
    {
        $usuario = $this->createUser('mARÍA del cARMEN');
        $categoria = Categoria::create([
            'nombre' => 'artesanía en cuero',
            'activo' => true,
        ]);
        $alumno = Alumno::create([
            'nombres' => 'jOSÉ luis',
            'ci' => '12345678',
            'telefono' => '70000000',
            'edad' => 18,
        ]);
        $producto = Producto::create([
            'id_categoria' => $categoria->id_categoria,
            'id_usuario_socio' => $usuario->id_usuario,
            'nombre' => 'cartera de cuero',
            'precio' => 25,
            'stock_actual' => 1,
            'stock_minimo' => 1,
            'activo' => true,
        ]);
        $curso = Curso::create([
            'id_usuario_instructor' => $usuario->id_usuario,
            'nombre' => 'taller de cerámica',
            'costo' => 100,
            'activo' => true,
        ]);
        $feria = Feria::create([
            'nombre' => 'feria de artesanías',
            'fecha' => '2026-11-11',
            'lugar' => 'Camiri',
        ]);

        $this->assertSame('María Del Carmen', $usuario->fresh()->nombre);
        $this->assertSame('José Luis', $alumno->fresh()->nombres);
        $this->assertSame('Artesanía En Cuero', $categoria->fresh()->nombre);
        $this->assertSame('Cartera De Cuero', $producto->fresh()->nombre);
        $this->assertSame('Taller De Cerámica', $curso->fresh()->nombre);
        $this->assertSame('Feria De Artesanías', $feria->fresh()->nombre);
    }

    public function test_product_search_matches_different_letter_case(): void
    {
        $directivaRole = Rol::create(['nombre' => 'Directiva']);
        $directiva = $this->createUser('Directiva');
        $directiva->roles()->attach($directivaRole->id_rol);
        $categoria = Categoria::create(['nombre' => 'Artesanía', 'activo' => true]);
        Producto::create([
            'id_categoria' => $categoria->id_categoria,
            'id_usuario_socio' => $directiva->id_usuario,
            'nombre' => 'Cerámica',
            'precio' => 25,
            'stock_actual' => 1,
            'stock_minimo' => 1,
            'activo' => true,
        ]);

        $this->actingAs($directiva)
            ->get('/admin/productos?buscar=cERAMICA')
            ->assertOk()
            ->assertSee('Cerámica');
    }

    public function test_member_search_ignores_case_and_diacritics_for_name_and_ci(): void
    {
        $directivaRole = Rol::create(['nombre' => 'Directiva']);
        $memberRole = Rol::create(['nombre' => 'Socio/Instructor']);
        $directiva = $this->createUser('Directiva');
        $directiva->roles()->attach($directivaRole->id_rol);
        $member = $this->createUser('María Artesana');
        $member->roles()->attach($memberRole->id_rol);
        Socio::create([
            'id_usuario' => $member->id_usuario,
            'ci' => '1234567-1A',
            'telefono' => '70000000',
            'fecha_inscripcion' => now()->toDateString(),
            'estado' => 'ACTIVO',
        ]);

        $this->actingAs($directiva)
            ->get('/admin/socios?buscar=maria')
            ->assertOk()
            ->assertSee('María Artesana');

        $this->get('/admin/socios?buscar=-1a')
            ->assertOk()
            ->assertSee('1234567-1A');
    }

    public function test_admin_cannot_create_a_course_with_zero_cost(): void
    {
        $directivaRole = Rol::create(['nombre' => 'Directiva']);
        $directiva = $this->createUser('Directiva');
        $directiva->roles()->attach($directivaRole->id_rol);
        $instructor = $this->createUser('Instructor');

        $this->actingAs($directiva)
            ->from('/admin/cursos/crear')
            ->post('/admin/cursos', [
                'id_usuario_instructor' => $instructor->id_usuario,
                'nombre' => 'Taller de Prueba',
                'costo' => 0,
                'horarios' => [[
                    'dia_semana' => 'Sabado',
                    'hora_inicio' => '09:00',
                    'hora_fin' => '12:00',
                    'fecha_inicio' => now()->toDateString(),
                    'fecha_fin' => now()->addMonth()->toDateString(),
                    'cupo_maximo' => 10,
                ]],
            ])
            ->assertRedirect('/admin/cursos/crear')
            ->assertSessionHasErrors('costo');

        $this->assertDatabaseCount('cursos', 0);
    }

    private function createUser(string $name): Usuario
    {
        return Usuario::create([
            'nombre' => $name,
            'correo' => strtolower(str_replace(' ', '.', $name)).'@example.test',
            'contrasena_hash' => Hash::make('password'),
            'activo' => true,
        ]);
    }
}
