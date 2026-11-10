<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            ['nombre' => 'Carpintería', 'descripcion' => 'Muebles y objetos de madera.'],
            ['nombre' => 'Gastronomía', 'descripcion' => 'Comidas y bebidas típicas.'],
            ['nombre' => 'Tejidos', 'descripcion' => 'Ropa y accesorios en telar.'],
            ['nombre' => 'Cuero', 'descripcion' => 'Artículos de cuero y marroquinería.'],
            ['nombre' => 'Cerámica', 'descripcion' => 'Vasijas y adornos de barro.'],
        ];

        foreach ($categorias as $categoria) {
            Categoria::create($categoria);
        }
    }
}