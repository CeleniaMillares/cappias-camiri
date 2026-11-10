<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

        // 🔥 CHECK: un pago debe estar vinculado a UNA venta O a UNA inscripción (no a ambas ni a ninguna)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("
                ALTER TABLE pagos
                ADD CONSTRAINT pagos_un_solo_origen_check
                CHECK (
                    (id_venta IS NOT NULL AND id_inscripcion IS NULL)
                    OR
                    (id_venta IS NULL AND id_inscripcion IS NOT NULL)
                )
            ");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE pagos DROP CONSTRAINT IF EXISTS pagos_un_solo_origen_check');
        }
        Schema::dropIfExists('pagos');
    }
};