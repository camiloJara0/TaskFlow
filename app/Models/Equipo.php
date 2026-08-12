<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipo extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'propietario_id',
        'estado',
    ];

    public function propietario()
    {
        return $this->belongsTo(User::class, 'propietario_id');
    }

    public function miembros()
    {
        return $this->belongsToMany(User::class, 'miembros_equipo', 'equipo_id', 'usuario_id')
            ->withPivot('rol', 'permisos', 'fecha_ingreso');
    }

    public function miembrosEquipo()
    {
        return $this->hasMany(MiembroEquipo::class);
    }

    public function espaciosTrabajo()
    {
        return $this->hasMany(EspacioTrabajo::class);
    }
}
