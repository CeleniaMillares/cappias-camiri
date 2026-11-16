<?php

namespace Tests\Feature;

use App\Models\Auditoria;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Socio;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuditoriaWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_observer_records_system_actor_and_product_deactivation_as_deletion(): void
    {
        $categoria = Categoria::create([
            'nombre' => 'Categoría auditoría',
            'activo' => true,
        ]);
        $socio = $this->createUser('socio@example.test', 'Socio auditoría');

        $producto = Producto::create([
            'id_categoria' => $categoria->id_categoria,
            'id_usuario_socio' => $socio->id_usuario,
            'nombre' => 'Producto auditoría',
            'precio' => 10,
            'stock_actual' => 1,
            'stock_minimo' => 1,
            'activo' => true,
        ]);

        $this->assertDatabaseHas('auditorias', [
            'id_usuario' => null,
            'nombre_usuario' => 'Sistema',
            'accion' => 'CREAR',
            'entidad' => 'Producto',
        ]);

        $administrador = $this->createUser('admin@example.test', 'Administrador auditoría');
        $this->actingAs($administrador);
        $producto->update(['activo' => false]);

        $this->assertDatabaseHas('auditorias', [
            'id_usuario' => $administrador->id_usuario,
            'nombre_usuario' => 'Administrador Auditoría',
            'accion' => 'ELIMINAR',
            'entidad' => 'Producto',
        ]);
    }

    public function test_promotion_and_removal_are_audited_only_when_role_changes(): void
    {
        $adminRole = Rol::create(['nombre' => 'Administrador']);
        $memberRole = Rol::create(['nombre' => 'Socio/Instructor']);
        Rol::create(['nombre' => 'Directiva']);

        $admin = $this->createUser('admin@example.test', 'Administradora');
        $admin->roles()->attach($adminRole->id_rol);
        $member = $this->createUser('member@example.test', 'Socio prueba');
        $member->roles()->attach($memberRole->id_rol);
        $socio = Socio::create([
            'id_usuario' => $member->id_usuario,
            'ci' => '12345678',
            'telefono' => '70000000',
            'fecha_inscripcion' => today()->toDateString(),
            'estado' => 'ACTIVO',
        ]);

        $this->actingAs($admin)
            ->from('/admin/usuarios/promover')
            ->post("/admin/usuarios/promover/{$socio->id_socio}")
            ->assertRedirect('/admin/usuarios/promover')
            ->assertSessionHas('success');

        $this->post("/admin/usuarios/promover/{$socio->id_socio}")
            ->assertRedirect('/admin/usuarios/promover')
            ->assertSessionHas('error', 'El socio ya es Directiva.');

        $this->post("/admin/usuarios/retirar-directiva/{$socio->id_socio}")
            ->assertRedirect('/admin/usuarios/promover')
            ->assertSessionHas('success');

        $this->post("/admin/usuarios/retirar-directiva/{$socio->id_socio}")
            ->assertRedirect('/admin/usuarios/promover')
            ->assertSessionHas('error', 'El socio no es Directiva.');

        $this->assertSame(1, Auditoria::where('accion', 'PROMOVER')->count());
        $this->assertSame(1, Auditoria::where('accion', 'RETIRAR')->count());
        $this->assertDatabaseHas('auditorias', [
            'id_usuario' => $admin->id_usuario,
            'nombre_usuario' => 'Administradora',
            'accion' => 'PROMOVER',
        ]);
    }

    public function test_audit_view_keeps_the_actor_name_after_user_soft_deletion(): void
    {
        $adminRole = Rol::create(['nombre' => 'Administrador']);
        $actor = $this->createUser('actor@example.test', 'Usuario Histórico');
        $actor->roles()->attach($adminRole->id_rol);
        $viewer = $this->createUser('viewer@example.test', 'Administrador actual');
        $viewer->roles()->attach($adminRole->id_rol);

        $this->actingAs($actor);
        Auditoria::create([
            'id_usuario' => $actor->id_usuario,
            'nombre_usuario' => $actor->nombre,
            'accion' => 'EDITAR',
            'entidad' => 'Producto',
        ]);
        $actor->delete();

        $this->actingAs($viewer)
            ->get('/admin/auditoria')
            ->assertOk()
            ->assertSee('Usuario Histórico');
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
