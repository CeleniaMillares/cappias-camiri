<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Curso;
use App\Models\HorarioCurso;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PublicCatalogWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_anonymous_course_catalog_is_read_only(): void
    {
        $instructor = $this->createUser('instructor@example.test', 'Instructora de prueba');
        $curso = Curso::create([
            'id_usuario_instructor' => $instructor->id_usuario,
            'nombre' => 'Taller de cerámica',
            'descripcion' => 'Aprende técnicas de cerámica artesanal.',
            'costo' => 100,
            'activo' => true,
        ]);
        HorarioCurso::create([
            'id_curso' => $curso->id_curso,
            'dia_semana' => 'Lunes y Miércoles',
            'hora_inicio' => '08:00',
            'hora_fin' => '12:00',
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonth()->toDateString(),
            'cupo_maximo' => 10,
        ]);

        $this->get('/vitrina/cursos')
            ->assertOk()
            ->assertSee('Taller De Cerámica')
            ->assertSee('Aprende técnicas de cerámica artesanal.')
            ->assertSee('Grupo 1: Lunes y Miércoles')
            ->assertSee('08:00–12:00')
            ->assertDontSee('Inscribirme');
    }

    public function test_anonymous_product_catalog_is_read_only(): void
    {
        $socio = $this->createUser('socio@example.test', 'Socio de prueba');
        $categoria = Categoria::create([
            'nombre' => 'Artesanía',
            'activo' => true,
        ]);
        Producto::create([
            'id_categoria' => $categoria->id_categoria,
            'id_usuario_socio' => $socio->id_usuario,
            'nombre' => 'Jarrón artesanal',
            'precio' => 100,
            'stock_actual' => 5,
            'stock_minimo' => 1,
            'activo' => true,
        ]);

        $this->get('/vitrina/productos')
            ->assertOk()
            ->assertSee('Jarrón Artesanal')
            ->assertSee('Socio De Prueba')
            ->assertSee('Stock: 5')
            ->assertSee('Disponible para venta en ferias y sede')
            ->assertDontSee('Comprar');
    }

    public function test_landing_page_links_to_the_read_only_catalogs(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('href="/vitrina/cursos"', false)
            ->assertSee('href="/vitrina/productos"', false);
    }

    private function createUser(string $email, string $name): Usuario
    {
        return Usuario::create([
            'nombre' => $name,
            'correo' => $email,
            'contrasena_hash' => Hash::make('password'),
            'activo' => true,
        ]);
    }
}
