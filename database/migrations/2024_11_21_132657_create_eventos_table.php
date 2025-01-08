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
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            $table->string('accion')->nullable();
            $table->string('descripcion')->nullable();
            $table->string('codigo')->nullable();
            $table->timestamps();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('origen')->nullable();
            $table->string('destino')->nullable();
            $table->integer('cantidad')->nullable();
            $table->float('peso')->nullable();
            $table->text('observacion')->nullable();
            $table->timestamp('fecha_recibido')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};
