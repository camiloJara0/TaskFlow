<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subtarea extends Model
{
    use HasFactory;

    protected $fillable = [
        'tarea_id',
        'titulo',
        'estado',
        'orden',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    public function tarea()
    {
        return $this->belongsTo(Tarea::class);
    }
}
