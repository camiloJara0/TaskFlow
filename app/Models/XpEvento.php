<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XpEvento extends Model
{
    use HasFactory;

    protected $table = 'xp_eventos';

    protected $fillable = [
        'gamificacion_id',
        'tarea_id',
        'cantidad',
        'motivo',
    ];

    public function gamificacion()
    {
        return $this->belongsTo(Gamificacion::class);
    }
}
