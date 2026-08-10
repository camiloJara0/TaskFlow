<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dependencia extends Model
{
    use HasFactory;

    protected $table = 'dependencias';

    protected $fillable = [
        'tarea_padre_id',
        'tarea_hija_id',
        'tipo',
    ];

    public function tareaPadre()
    {
        return $this->belongsTo(Tarea::class, 'tarea_padre_id');
    }

    public function tareaHija()
    {
        return $this->belongsTo(Tarea::class, 'tarea_hija_id');
    }
}
