<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Logro extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'titulo',
        'descripcion',
        'icono',
        'rareza',
        'categoria',
        'condicion',
        'meta',
    ];

    public function gamificaciones()
    {
        return $this->belongsToMany(Gamificacion::class, 'gamificacion_logros')
            ->withPivot('desbloqueado_en');
    }
}
