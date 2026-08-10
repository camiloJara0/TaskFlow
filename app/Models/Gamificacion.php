<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gamificacion extends Model
{
    use HasFactory;

    protected $table = 'gamificaciones';

    protected $fillable = [
        'usuario_id',
        'xp',
        'nivel',
        'racha',
        'mejor_racha',
        'ultimo_dia_activo',
        'tareas_completadas',
        'preferencias',
    ];

    protected $casts = [
        'ultimo_dia_activo' => 'date',
        'preferencias' => 'array',
        'racha' => 'integer',
        'mejor_racha' => 'integer',
        'nivel' => 'integer',
        'tareas_completadas' => 'integer',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function registrosDiarios()
    {
        return $this->hasMany(GamificacionDiaria::class);
    }

    public function logros()
    {
        return $this->belongsToMany(Logro::class, 'gamificacion_logros')->withPivot('desbloqueado_en');
    }

    public function xpEventos()
    {
        return $this->hasMany(XpEvento::class);
    }
}
