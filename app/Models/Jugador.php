<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jugador extends Model
{
    /** @use HasFactory<\Database\Factories\JugadorFactory> */
    use HasFactory;

    protected $table = 'jugadores';

    protected $fillable = ['user_id', 'jugadas', 'ganadas'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function partidas() {
        return $this->hasMany(Partida::class, 'jugador_id');
    }
}
