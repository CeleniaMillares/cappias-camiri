<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuotas_socios', function (Blueprint $table) {
            $table->uuid('id_cuota')->primary();
            $table->uuid('id_socio');
            $table->string('tipo', 20);
            $table->string('metodo', 20);
            $table->string('estado', 20)->default('PENDIENTE');
            $table->string('periodo', 7)->nullable();
            $table->decimal('monto', 10, 2);
            $table->decimal('monto_efectivo', 10, 2)->default(0);
            $table->date('fecha_pago');
            $table->timestamps();

            $table->foreign('id_socio')
                  ->references('id_socio')
                  ->on('socios')
                  ->restrictOnDelete();
        });

        DB::statement("
            CREATE UNIQUE INDEX cuotas_socios_matricula_unique
            ON cuotas_socios (id_socio)
            WHERE tipo = 'MATRICULA'
        ");

        DB::statement("
            CREATE UNIQUE INDEX cuotas_socios_mensualidad_periodo_unique
            ON cuotas_socios (id_socio, periodo)
            WHERE tipo = 'MENSUALIDAD' AND periodo IS NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS cuotas_socios_matricula_unique');
        DB::statement('DROP INDEX IF EXISTS cuotas_socios_mensualidad_periodo_unique');
        Schema::dropIfExists('cuotas_socios');
    }
};