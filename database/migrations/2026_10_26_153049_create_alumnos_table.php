<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('alumnos', function (Blueprint $table) {
            $table->uuid('id_alumno')->primary();
            $table->uuid('id_usuario')->nullable()->unique();
            $table->string('nombres', 150);
            $table->string('ci', 20)->unique(); 
            $table->string('telefono', 20);
            $table->integer('edad');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->restrictOnDelete();
        });
    }
    public function down(): void { Schema::dropIfExists('alumnos'); }
};