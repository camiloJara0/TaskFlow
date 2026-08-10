<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recordatorio extends Model
{
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'tarea_id',
        'fecha',
        'tipo',
        'enviado',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'enviado' => 'boolean',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function tarea()
    {
        return $this->belongsTo(Tarea::class);
    }
}
