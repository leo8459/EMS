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
        Schema::create('historico', function (Blueprint $table) {
            $table->id();
            $table->string('numero_guia');
            $table->timestamp('fecha_hora_admision')->nullable();
            // $table->date('dia_entrega_programado')->nullable();
            $table->timestamp('fecha_actualizacion')->nullable();
            $table->unsignedInteger('id_estado_actualizacion');
            $table->string('estado_actualizacion');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historico');
    }
};
