<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('socios', function (Blueprint $table) {
            $table->uuid('id_socio')->primary();
            $table->uuid('id_usuario');
            $table->string('ci', 20)->unique();
            $table->string('telefono', 20);
            $table->string('direccion', 255)->nullable();
            $table->date('fecha_inscripcion');
            $table->string('estado', 20)->default('ACTIVO');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('socios');
    }
};