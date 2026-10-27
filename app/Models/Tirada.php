<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tirada extends Model
{
    /** @use HasFactory<\Database\Factories\TiradaFactory> */
    use HasFactory;
    protected $fillable = ['partida_id', 'tirada_jugador', 'tirada_maquina', 'resultado'];
}
