<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('productos', function (Blueprint $table) {
            $table->uuid('id_producto')->primary();
            $table->uuid('id_categoria');
            $table->uuid('id_usuario_socio');
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2);
            $table->string('materiales', 255)->nullable();
            $table->string('tecnica', 255)->nullable();
            $table->string('fotografia_url', 255)->nullable();
            $table->integer('stock_actual')->default(0);
            $table->integer('stock_minimo')->default(5);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_categoria')->references('id_categoria')->on('categorias');
            $table->foreign('id_usuario_socio')->references('id_usuario')->on('usuarios');
        });
    }
    public function down(): void { Schema::dropIfExists('productos'); }
};