<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'nombre',
        'color',
        'orden',
    ];

    public function workspace()
    {
        return $this->belongsTo(EspacioTrabajo::class, 'workspace_id');
    }

    public function tareas()
    {
        return $this->hasMany(Tarea::class);
    }
}
