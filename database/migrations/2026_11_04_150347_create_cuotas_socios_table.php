<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuotas_socios', function (Blueprint $table) {
            $table->uuid('id_cuota')->primary();
            $table->uuid('id_socio');
            $table->string('tipo', 20); // MATRICULA, MENSUALIDAD
            $table->string('metodo', 20); // EFECTIVO, QR, MIXTO
            $table->string('estado', 20)->default('CONFIRMADO');
            $table->string('periodo', 7)->nullable(); // Ej: 2026-11
            $table->decimal('monto', 10, 2);
            $table->date('fecha_pago');
            $table->timestamps();

            $table->foreign('id_socio')->references('id_socio')->on('socios')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuotas_socios');
    }
};