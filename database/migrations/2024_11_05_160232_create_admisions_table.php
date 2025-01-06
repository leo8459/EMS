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
            $table->string('tipo_correspondencia')->nullable();
            $table->integer('cantidad')->nullable();
            $table->decimal('peso', 8, 2)->nullable();
            $table->decimal('peso_ems', 8, 2)->nullable();
            $table->decimal('peso_regional', 8, 2)->nullable();
            $table->string('observacion')->nullable();
            $table->string('destino')->nullable();
            $table->string('codigo')->unique();
            $table->decimal('precio', 10, 2);
            $table->string('numero_factura')->nullable();
            $table->string('nombre_remitente')->nullable();
            $table->string('nombre_envia')->nullable();
            $table->string('carnet')->nullable();
            $table->string('telefono_remitente')->nullable();
            $table->string('nombre_destinatario')->nullable();
            $table->string('telefono_destinatario')->nullable();
            $table->string('direccion')->nullable();
            $table->string('provincia')->nullable();
            $table->string('ciudad')->nullable();
            $table->string('pais')->nullable();
            $table->text('firma_entrega')->nullable();
            $table->string('photo')->nullable();
            $table->string('contenido')->nullable();
            $table->string('reencaminamiento')->nullable();
            
            $table->string('creacionadmision')->nullable();
            $table->enum('notificacion', ['FALTANTE', 'SOBRANTE', 'MALENCAMINADO', 'DAÑADO'])->nullable();

            $table->integer('estado')->default(1);
            $table->timestamps();




            $table->foreignId('user_id')->constrained()->onDelete('cascade');

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
