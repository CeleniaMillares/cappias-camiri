<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackupWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'backup.backup.name' => 'CAPPIAS',
            'backup.backup.destination.disks' => ['local'],
        ]);
        Storage::fake('local');
    }

    public function test_administrator_can_view_existing_backups(): void
    {
        $admin = $this->createUserWithRole('admin@example.test', 'Administrador');
        Storage::disk('local')->put('CAPPIAS/cappias-20261110.zip', 'backup archive');

        $this->actingAs($admin)
            ->get('/admin/backups')
            ->assertOk()
            ->assertSee('cappias-20261110.zip')
            ->assertSee('Descargar')
            ->assertSee('Eliminar');
    }

    public function test_administrator_can_generate_backup(): void
    {
        $admin = $this->createUserWithRole('admin@example.test', 'Administrador');
        Artisan::shouldReceive('call')
            ->once()
            ->with('backup:run')
            ->andReturn(0);

        $this->actingAs($admin)
            ->post('/admin/backups')
            ->assertRedirect()
            ->assertSessionHas('success', 'El respaldo se generó correctamente.');
    }

    public function test_non_administrator_cannot_access_backup_management(): void
    {
        $directiva = $this->createUserWithRole('directiva@example.test', 'Directiva');

        $this->actingAs($directiva)
            ->get('/admin/backups')
            ->assertForbidden();

        $this->actingAs($directiva)
            ->post('/admin/backups')
            ->assertForbidden();
    }

    public function test_administrator_can_download_and_delete_a_backup(): void
    {
        $admin = $this->createUserWithRole('admin@example.test', 'Administrador');
        $path = 'CAPPIAS/cappias-20261110.zip';
        Storage::disk('local')->put($path, 'backup archive');

        $this->actingAs($admin)
            ->get('/admin/backups/download/cappias-20261110.zip')
            ->assertOk();

        $this->actingAs($admin)
            ->delete('/admin/backups/cappias-20261110.zip')
            ->assertRedirect()
            ->assertSessionHas('success');

        Storage::disk('local')->assertMissing($path);
    }

    public function test_backup_filename_cannot_escape_the_backup_directory(): void
    {
        $admin = $this->createUserWithRole('admin@example.test', 'Administrador');
        Storage::disk('local')->put('outside.zip', 'not a backup');

        $this->actingAs($admin)
            ->get('/admin/backups/download/outside.zip')
            ->assertNotFound();

        Storage::disk('local')->assertExists('outside.zip');
    }

    private function createUserWithRole(string $email, string $roleName): Usuario
    {
        $role = Rol::create(['nombre' => $roleName]);
        $user = Usuario::create([
            'nombre' => 'Usuario de prueba',
            'correo' => $email,
            'contrasena_hash' => Hash::make('password'),
            'activo' => true,
        ]);
        $user->roles()->attach($role->id_rol);

        return $user;
    }
}
