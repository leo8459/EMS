<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tarifas', function (Blueprint $table) {
            $table->id(); // Llave primaria
            $table->string('servicio')->nullable();
            $table->decimal('peso_min', 8, 3)->nullable(); // Peso mínimo con 3 decimales
            $table->decimal('peso_max', 8, 3)->nullable(); // Peso máximo con 3 decimales
            $table->integer('ems_local_cobertura_1')->nullable(); // EMS Local Cobertura 1 como entero
            $table->integer('ems_local_cobertura_2')->nullable(); // EMS Local Cobertura 2 como entero
            $table->integer('ems_local_cobertura_3')->nullable(); // EMS Local Cobertura 3 como entero
            $table->integer('ems_local_cobertura_4')->nullable(); // EMS Local Cobertura 4 como entero
            $table->integer('ems_nacional')->nullable(); // EMS Nacional como entero
            $table->integer('destino_1')->nullable(); // Destino 1 como entero
            $table->integer('destino_2')->nullable(); // Destino 2 como entero
            $table->integer('destino_3')->nullable(); // Destino 3 como entero
            $table->timestamps(); // Columnas created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarifas');
    }
};
