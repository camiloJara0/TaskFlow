<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TareaEtiqueta extends Model
{
    use HasFactory;

    protected $table = 'tarea_etiqueta';

    protected $fillable = [
        'tarea_id',
        'etiqueta_id',
    ];

    public function tarea()
    {
        return $this->belongsTo(Tarea::class);
    }

    public function etiqueta()
    {
        return $this->belongsTo(Etiqueta::class);
    }
}
