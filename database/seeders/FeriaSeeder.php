<?php

namespace Database\Seeders;

use App\Models\Feria;
use Illuminate\Database\Seeder;

class FeriaSeeder extends Seeder
{
    public function run(): void
    {
        $this->updateOrCreateTitleCase('Feria de Artesanías 2026', [
            'fecha' => now()->addDays(15)->toDateString(),
            'lugar' => 'Plaza Principal de Camiri',
        ]);

        $this->updateOrCreateTitleCase('Feria Gastronómica 2026', [
            'fecha' => now()->addDays(30)->toDateString(),
            'lugar' => 'Coliseo Municipal de Camiri',
        ]);
    }

    private function updateOrCreateTitleCase(string $nombre, array $attributes): void
    {
        $feria = Feria::whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre, 'UTF-8')])->first()
            ?? new Feria();

        $feria->fill(['nombre' => $nombre] + $attributes)->save();
    }
}