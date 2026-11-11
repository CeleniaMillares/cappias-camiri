<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pagos_socios', function (Blueprint $table) {
            $table->uuid('id_pago_socio')->primary();
            $table->uuid('id_usuario_socio');
            $table->date('periodo_inicio');
            $table->date('periodo_fin');
            $table->decimal('monto_bruto', 10, 2);
            $table->decimal('comision', 10, 2);
            $table->decimal('monto_neto', 10, 2);
            $table->string('metodo', 20)->default('EFECTIVO');
            $table->string('estado', 20)->default('PAGADA');
            $table->timestamps();

            $table->foreign('id_usuario_socio')->references('id_usuario')->on('usuarios');
        });
    }
    public function down(): void { Schema::dropIfExists('pagos_socios'); }
};