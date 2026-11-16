<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feria_socio', function (Blueprint $table) {
            $table->foreignUuid('id_feria')
                ->constrained('ferias', 'id_feria')
                ->cascadeOnDelete();
            $table->foreignUuid('id_socio')
                ->constrained('socios', 'id_socio')
                ->cascadeOnDelete();
            $table->string('estado')->default('CONFIRMADO');
            $table->timestamps();

            $table->primary(['id_feria', 'id_socio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feria_socio');
    }
};
