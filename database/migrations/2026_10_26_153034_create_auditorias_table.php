<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('auditorias', function (Blueprint $table) {
            $table->uuid('id_auditoria')->primary();
            $table->uuid('id_usuario')->nullable();
            $table->string('nombre_usuario', 150);
            $table->string('accion', 100);  
            $table->string('entidad', 100); 
            $table->timestamps();
            
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios');
        });
    }
    public function down(): void { Schema::dropIfExists('auditorias'); }
};