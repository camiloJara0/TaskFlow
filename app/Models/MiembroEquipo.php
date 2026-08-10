<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MiembroEquipo extends Model
{
    use HasFactory;

    protected $table = 'miembros_equipo';

    protected $fillable = [
        'equipo_id',
        'usuario_id',
        'rol',
        'permisos',
        'fecha_ingreso',
    ];

    protected $casts = [
        'permisos' => 'array',
        'fecha_ingreso' => 'datetime',
    ];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
