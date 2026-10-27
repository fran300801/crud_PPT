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
        Schema::create('tiradas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partida_id')->constrained('partidas')->onDelete('cascade');
            $table->string('tirada_jugador');
            $table->string('tirada_maquina');
            $table->integer('resultado'); // 0: empate, 1: jugador gana, 2: máquina gana
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tiradas');
    }
};
