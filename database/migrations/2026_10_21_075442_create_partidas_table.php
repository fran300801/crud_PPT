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
        Schema::create('partidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jugador_id')->constrained('jugadores')->onDelete('cascade');
            $table->integer('resultado')->default(0); // 0: partida en curso, 1: jugador gana, 2: máquina gana, no puede haber empate en un mejor de 5
            $table->boolean('activa')->default(true); // true: partida en curso, false: partida finalizada
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partidas');
    }
};
