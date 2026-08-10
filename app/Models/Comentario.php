<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comentario extends Model
{
    use HasFactory;

    protected $fillable = [
        'tarea_id',
        'usuario_id',
        'comentario',
    ];

    public function tarea()
    {
        return $this->belongsTo(Tarea::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function reacciones()
    {
        return $this->hasMany(Reaccione::class);
    }
}
