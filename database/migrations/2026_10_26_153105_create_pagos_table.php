<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pagos', function (Blueprint $table) {
            $table->uuid('id_pago')->primary();
            $table->uuid('id_venta')->nullable();
            $table->uuid('id_inscripcion')->nullable();
            $table->decimal('monto', 10, 2);
            $table->string('metodo', 20); 
            $table->string('comprobante_url', 255)->nullable();
            $table->string('estado', 20)->default('PENDIENTE'); 
            $table->timestamps();

            $table->foreign('id_venta')->references('id_venta')->on('ventas');
            $table->foreign('id_inscripcion')->references('id_inscripcion')->on('inscripciones');
        });
    }
    public function down(): void { Schema::dropIfExists('pagos'); }
};