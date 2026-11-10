<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ferias', function (Blueprint $table) {
            $table->uuid('id_feria')->primary();
            $table->string('nombre', 150);
            $table->date('fecha');
            $table->string('lugar', 255);
            $table->string('imagen_url')->nullable();
            $table->timestamps();
            
        });
    }
    public function down(): void { Schema::dropIfExists('ferias'); }
};