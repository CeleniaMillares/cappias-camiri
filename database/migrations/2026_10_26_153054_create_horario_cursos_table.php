<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('horario_cursos', function (Blueprint $table) {
            $table->uuid('id_horario_curso')->primary();
            $table->uuid('id_curso');
            $table->string('dia_semana', 100); 
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->integer('cupo_maximo')->default(15); 
            $table->timestamps();

            $table->foreign('id_curso')->references('id_curso')->on('cursos')->onDelete('cascade');
        });
    }
    public function down(): void { Schema::dropIfExists('horario_cursos'); }
};