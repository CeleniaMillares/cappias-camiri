<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('lista_esperas', function (Blueprint $table) {
            $table->uuid('id_lista_espera')->primary();
            $table->uuid('id_alumno');
            $table->uuid('id_horario_curso');
            $table->timestamps();

            $table->foreign('id_alumno')->references('id_alumno')->on('alumnos');
            $table->foreign('id_horario_curso')->references('id_horario_curso')->on('horario_cursos');
            
            $table->unique(['id_alumno', 'id_horario_curso']); 
        });
    }
    public function down(): void { Schema::dropIfExists('lista_esperas'); }
};