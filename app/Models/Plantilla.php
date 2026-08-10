<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plantilla extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'nombre',
        'descripcion',
        'datos',
        'creado_por',
    ];

    protected $casts = [
        'datos' => 'array',
    ];

    public function workspace()
    {
        return $this->belongsTo(EspacioTrabajo::class, 'workspace_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }
}
