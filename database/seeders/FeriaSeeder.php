<?php

namespace Database\Seeders;

use App\Models\Feria;
use Illuminate\Database\Seeder;

class FeriaSeeder extends Seeder
{
    public function run(): void
    {
        Feria::create([
            'nombre' => 'Feria de Artesanías 2026',
            'fecha' => now()->addDays(15)->toDateString(),
            'lugar' => 'Plaza Principal de Camiri',
        ]);

        Feria::create([
            'nombre' => 'Feria Gastronómica 2026',
            'fecha' => now()->addDays(30)->toDateString(),
            'lugar' => 'Coliseo Municipal de Camiri',
        ]);
    }
}