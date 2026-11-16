<?php

namespace Tests\Feature;

use App\Models\Auditoria;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductoInventoryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $directiva;
    private Usuario $socio;
    private Categoria $categoria;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $directivaRole = Rol::create(['nombre' => 'Directiva']);
        $socioRole = Rol::create(['nombre' => 'Socio/Instructor']);
        $this->directiva = $this->createUser('directiva@example.test');
        $this->directiva->roles()->attach($directivaRole->id_rol);
        $this->socio = $this->createUser('socio@example.test');
        $this->socio->roles()->attach($socioRole->id_rol);
        $this->categoria = $this->createCategory('Categoría de prueba');

        $this->actingAs($this->directiva);
    }

    public function test_product_photo_is_validated_stored_replaced_and_displayed(): void
    {
        $this->post('/admin/productos', $this->productData([
            'fotografia_url' => UploadedFile::fake()->image('producto.png'),
        ]))->assertRedirect('/admin/productos');

        $producto = Producto::firstOrFail();
        $rutaAnterior = $producto->fotografia_url;
        Storage::disk('public')->assertExists($rutaAnterior);

        $this->get('/admin/productos')
            ->assertOk()
            ->assertSee('storage/' . $rutaAnterior);

        $this->put("/admin/productos/{$producto->id_producto}", $this->productData([
            'nombre' => 'Producto actualizado',
            'fotografia_url' => UploadedFile::fake()->image('producto-nuevo.png'),
        ]))->assertRedirect('/admin/productos');

        $rutaNueva = $producto->fresh()->fotografia_url;
        $this->assertNotSame($rutaAnterior, $rutaNueva);
        Storage::disk('public')->assertMissing($rutaAnterior);
        Storage::disk('public')->assertExists($rutaNueva);
    }

    public function test_stock_can_be_added_without_overwriting_the_product_form_values(): void
    {
        $producto = $this->createProduct('Producto para reponer');

        $this->from('/admin/productos')->post("/admin/productos/{$producto->id_producto}/stock", [
            'cantidad' => 3,
        ])->assertRedirect('/admin/productos')
            ->assertSessionHas('success');

        $this->assertSame(11, (int) $producto->fresh()->stock_actual);

        $this->from('/admin/productos')
            ->post("/admin/productos/{$producto->id_producto}/stock", [
                'cantidad' => 0,
            ])
            ->assertRedirect('/admin/productos')
            ->assertSessionHasErrors('cantidad');

        $this->assertSame(11, (int) $producto->fresh()->stock_actual);
    }

    public function test_product_rejects_zero_price_and_non_image_or_oversized_photos(): void
    {
        $this->from('/admin/productos/crear')
            ->post('/admin/productos', $this->productData(['precio' => '0']))
            ->assertRedirect('/admin/productos/crear')
            ->assertSessionHasErrors('precio');

        $this->from('/admin/productos/crear')
            ->post('/admin/productos', $this->productData([
                'fotografia_url' => UploadedFile::fake()->create('documento.txt', 10, 'text/plain'),
            ]))
            ->assertRedirect('/admin/productos/crear')
            ->assertSessionHasErrors('fotografia_url');

        $this->from('/admin/productos/crear')
            ->post('/admin/productos', $this->productData([
                'fotografia_url' => UploadedFile::fake()->image('grande.png')->size(2049),
            ]))
            ->assertRedirect('/admin/productos/crear')
            ->assertSessionHasErrors('fotografia_url');

        $this->assertDatabaseCount('productos', 0);
    }

    public function test_product_requires_active_category_and_a_user_with_member_role(): void
    {
        $categoriaInactiva = $this->createCategory('Categoría inactiva', false);
        $this->from('/admin/productos/crear')
            ->post('/admin/productos', $this->productData([
                'id_categoria' => $categoriaInactiva->id_categoria,
            ]))
            ->assertRedirect('/admin/productos/crear')
            ->assertSessionHasErrors('id_categoria');

        $usuarioSinRolSocio = $this->createUser('otro@example.test');
        $this->from('/admin/productos/crear')
            ->post('/admin/productos', $this->productData([
                'id_usuario_socio' => $usuarioSinRolSocio->id_usuario,
            ]))
            ->assertRedirect('/admin/productos/crear')
            ->assertSessionHasErrors('id_usuario_socio');

        $this->assertDatabaseCount('productos', 0);
    }

    public function test_deactivating_category_deactivates_products_and_audits_each_product(): void
    {
        $productoActivo = $this->createProduct('Producto activo');
        $productoInactivo = $this->createProduct('Producto ya inactivo', false);

        $this->delete("/admin/categorias/{$this->categoria->id_categoria}")
            ->assertRedirect('/admin/categorias');

        $this->assertFalse((bool) $this->categoria->fresh()->activo);
        $this->assertFalse((bool) $productoActivo->fresh()->activo);
        $this->assertFalse((bool) $productoInactivo->fresh()->activo);
        $this->assertSame(
            1,
            Auditoria::where('entidad', 'Producto')
                ->where('accion', 'ELIMINAR')
                ->count()
        );
    }

    public function test_deactivating_category_from_edit_also_deactivates_active_products(): void
    {
        $producto = $this->createProduct('Producto de categoría');

        $this->put("/admin/categorias/{$this->categoria->id_categoria}", [
            'nombre' => $this->categoria->nombre,
            'descripcion' => $this->categoria->descripcion,
        ])->assertRedirect('/admin/categorias');

        $this->assertFalse((bool) $this->categoria->fresh()->activo);
        $this->assertFalse((bool) $producto->fresh()->activo);
    }

    public function test_inactive_product_cannot_be_submitted_to_a_sale_directly(): void
    {
        $producto = $this->createProduct('Producto desactivado', false);

        $this->from('/admin/ventas/crear')
            ->post('/admin/ventas', [
                'metodo_pago' => 'EFECTIVO',
                'productos' => [
                    ['id_producto' => $producto->id_producto, 'cantidad' => 1],
                ],
            ])
            ->assertRedirect('/admin/ventas/crear')
            ->assertSessionHasErrors('productos');

        $this->assertDatabaseCount('ventas', 0);
    }

    public function test_product_and_category_indexes_are_paginated_and_keep_query_parameters(): void
    {
        for ($i = 0; $i < 11; $i++) {
            $this->createProduct("Inventario {$i}");
            $this->createCategory("Categoría {$i}");
        }

        $this->get('/admin/productos?buscar=Inventario&page=2')
            ->assertOk()
            ->assertSee('buscar=Inventario', false)
            ->assertViewHas('productos', fn ($productos) => $productos->currentPage() === 2);

        $this->get('/admin/categorias?page=2')
            ->assertOk()
            ->assertViewHas('categorias', fn ($categorias) => $categorias->currentPage() === 2);
    }

    private function productData(array $overrides = []): array
    {
        return array_merge([
            'id_categoria' => $this->categoria->id_categoria,
            'id_usuario_socio' => $this->socio->id_usuario,
            'nombre' => 'Producto de prueba',
            'descripcion' => 'Descripción de prueba',
            'precio' => '10.50',
            'materiales' => 'Madera',
            'tecnica' => 'Tallado',
            'stock_actual' => 8,
            'stock_minimo' => 2,
        ], $overrides);
    }

    private function createCategory(string $name, bool $active = true): Categoria
    {
        return Categoria::create([
            'nombre' => $name,
            'descripcion' => 'Categoría para pruebas',
            'activo' => $active,
        ]);
    }

    private function createProduct(string $name, bool $active = true): Producto
    {
        return Producto::create([
            'id_categoria' => $this->categoria->id_categoria,
            'id_usuario_socio' => $this->socio->id_usuario,
            'nombre' => $name,
            'precio' => 10,
            'stock_actual' => 8,
            'stock_minimo' => 2,
            'activo' => $active,
        ]);
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
}
