<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Archivo extends Model
{
    use HasFactory;

    protected $fillable = [
        'tarea_id',
        'nombre',
        'url',
        'tipo',
        'peso',
        'subido_por',
    ];

    public function tarea()
    {
        return $this->belongsTo(Tarea::class);
    }

    public function subidor()
    {
        return $this->belongsTo(User::class, 'subido_por');
    }
}
