<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cursos', function (Blueprint $table) {
            $table->uuid('id_curso')->primary();
            $table->uuid('id_usuario_instructor');
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->string('duracion', 50); 
            $table->decimal('costo', 10, 2);
             $table->string('imagen_url')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('id_usuario_instructor')->references('id_usuario')->on('usuarios');
        });
    }
    public function down(): void { Schema::dropIfExists('cursos'); }
};