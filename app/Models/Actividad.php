<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    use HasFactory;

    protected $table = 'actividades';

    protected $fillable = [
        'usuario_id',
        'tipo',
        'objeto_type',
        'objeto_id',
        'descripcion',
        'json_detalles',
    ];

    protected $casts = [
        'json_detalles' => 'array',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function objeto()
    {
        return $this->morphTo();
    }
}
