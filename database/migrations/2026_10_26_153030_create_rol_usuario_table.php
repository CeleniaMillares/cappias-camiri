<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rol_usuario', function (Blueprint $table) {
            $table->uuid('id_rol');
            $table->uuid('id_usuario');
            
            $table->foreign('id_rol')->references('id_rol')->on('roles')->onDelete('cascade');
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->onDelete('cascade');
            $table->primary(['id_rol', 'id_usuario']); 
        });
    }
    public function down(): void { Schema::dropIfExists('rol_usuario'); }
};