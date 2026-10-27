<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partida extends Model
{
    /** @use HasFactory<\Database\Factories\PartidaFactory> */
    use HasFactory;
    protected $fillable = ['jugador_id', 'activa', 'resultado'];

    public function tiradas(): HasMany
    {
        return $this->hasMany(Tirada::class, 'partida_id');
    }

    public function jugador()
    {
        return $this->belongsTo(Jugador::class, 'jugador_id');
    }
}
