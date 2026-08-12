<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarea extends Model
{
    use HasFactory;

    protected $fillable = [
        'espacio_trabajo_id',
        'lista_id',
        'creador_id',
        'responsable_id',
        'titulo',
        'descripcion',
        'estado_id',
        'prioridad',
        'fecha_inicio',
        'fecha_vencimiento',
        'estimacion_horas',
        'horas_invertidas',
        'porcentaje',
        'orden',
        'es_recurrente',
        'plantilla',
        'archivada',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_vencimiento' => 'datetime',
        'es_recurrente' => 'boolean',
        'plantilla' => 'boolean',
        'archivada' => 'boolean',
        'estimacion_horas' => 'decimal:2',
        'horas_invertidas' => 'decimal:2',
        'descripcion' => 'array',
    ];

    public function proyecto()
    {
        return $this->belongsTo(EspacioTrabajo::class);
    }

    public function lista()
    {
        return $this->belongsTo(Tabla::class, 'lista_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'creador_id');
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class);
    }

    public function subtareas()
    {
        return $this->hasMany(Subtarea::class);
    }

    public function etiquetas()
    {
        return $this->belongsToMany(Etiqueta::class, 'tarea_etiqueta');
    }

    public function dependenciasPadre()
    {
        return $this->hasMany(Dependencia::class, 'tarea_padre_id');
    }

    public function dependenciasHija()
    {
        return $this->hasMany(Dependencia::class, 'tarea_hija_id');
    }

    public function comentarios()
    {
        return $this->hasMany(Comentario::class);
    }

    public function archivos()
    {
        return $this->hasMany(Archivo::class);
    }

    public function checklists()
    {
        return $this->hasMany(Checklist::class);
    }

    public function recordatorios()
    {
        return $this->hasMany(Recordatorio::class);
    }
}
