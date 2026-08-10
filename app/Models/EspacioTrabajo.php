<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EspacioTrabajo extends Model
{
    use HasFactory;

    protected $table = 'espacios_trabajo';

    protected $fillable = [
        'equipo_id',
        'nombre',
        'descripcion',
        'color',
        'icono',
        'orden',
    ];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }

    public function estados()
    {
        return $this->hasMany(Estado::class, 'workspace_id');
    }

    public function tablas()
    {
        return $this->hasMany(Tabla::class, 'workspace_id');
    }

    public function etiquetas()
    {
        return $this->hasMany(Etiqueta::class, 'workspace_id');
    }

    public function automatizaciones()
    {
        return $this->hasMany(Automatizacion::class, 'workspace_id');
    }

    public function plantillas()
    {
        return $this->hasMany(Plantilla::class, 'workspace_id');
    }

    public function tareas()
    {
        return $this->hasMany(Tarea::class, 'espacio_trabajo_id');
    }
}
