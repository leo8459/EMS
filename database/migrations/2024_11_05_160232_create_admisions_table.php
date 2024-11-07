<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('admisions', function (Blueprint $table) {
            $table->id();
            // $table->string('paquetes');
            // $table->string('departamento');
            $table->string('origen');
            $table->datetime('fecha');
            $table->string('servicio');
            $table->string('tipo_correspondencia');
            $table->integer('cantidad');
            $table->decimal('peso', 8, 2);
            $table->string('destino');
            $table->string('codigo')->unique();
            $table->decimal('precio', 10, 2);
            $table->string('numero_factura')->nullable();
            $table->string('nombre_remitente');
            $table->string('nombre_envia')->nullable();
            $table->string('carnet');
            $table->string('telefono_remitente');
            $table->string('nombre_destinatario');
            $table->string('telefono_destinatario')->nullable();
            $table->string('direccion');
            $table->string('provincia')->nullable();
            $table->string('ciudad');
            $table->string('pais');
            $table->integer('estado')->default(1);
            $table->timestamps();
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admisions');
    }
};
