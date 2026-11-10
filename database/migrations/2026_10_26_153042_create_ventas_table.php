<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ventas', function (Blueprint $table) {
            $table->uuid('id_venta')->primary();
            $table->uuid('id_feria')->nullable(); 
            $table->uuid('id_usuario_directiva');
            $table->decimal('total', 10, 2);
            $table->decimal('comision_cappias', 10, 2)->default(0);
            $table->boolean('comision_calculada')->default(false);
            $table->boolean('stock_procesado')->default(false);
            $table->string('estado', 20)->default('PENDIENTE_PAGO');
            $table->timestamps();

            $table->foreign('id_feria')->references('id_feria')->on('ferias');
            $table->foreign('id_usuario_directiva')->references('id_usuario')->on('usuarios');
        });
    }
    public function down(): void { Schema::dropIfExists('ventas'); }
};