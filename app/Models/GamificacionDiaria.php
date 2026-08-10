<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GamificacionDiaria extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'gamificacion_diaria';

    protected $fillable = [
        'gamificacion_id',
        'fecha',
        'tareas',
        'xp',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function gamificacion()
    {
        return $this->belongsTo(Gamificacion::class);
    }
}
