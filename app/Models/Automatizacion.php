<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Automatizacion extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'nombre',
        'evento',
        'condicion',
        'accion',
        'activo',
    ];

    protected $casts = [
        'condicion' => 'array',
        'accion' => 'array',
        'activo' => 'boolean',
    ];

    public function workspace()
    {
        return $this->belongsTo(EspacioTrabajo::class, 'workspace_id');
    }
}
