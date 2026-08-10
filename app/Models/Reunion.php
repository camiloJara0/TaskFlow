<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reunion extends Model
{
    use HasFactory;

    protected $table = 'reuniones';

    protected $fillable = [
        'espacio_trabajo_id',
        'creador_id',
        'titulo',
        'descripcion',
        'estado',
        'fecha',
        'hora',
        'url',
        'archivada',
        'recordatorio_enviado',
    ];

    protected $casts = [
        'fecha' => 'date',
        'archivada' => 'boolean',
        'recordatorio_enviado' => 'boolean',
    ];

    public function espacioTrabajo()
    {
        return $this->belongsTo(EspacioTrabajo::class, 'espacio_trabajo_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'creador_id');
    }

    public function integrantes()
    {
        return $this->hasMany(ReunionIntegrante::class, 'id_reunion');
    }

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'reunion_integrantes', 'id_reunion', 'id_usuario')
            ->withTimestamps();
    }
}
