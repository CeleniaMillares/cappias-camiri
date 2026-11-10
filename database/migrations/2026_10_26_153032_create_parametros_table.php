<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('parametros', function (Blueprint $table) {
            $table->uuid('id_parametro')->primary();
            $table->string('clave', 50)->unique(); 
            $table->string('valor', 50);           
            $table->string('descripcion', 255)->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('parametros'); }
};