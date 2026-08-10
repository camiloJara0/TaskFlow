<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'nombre',
        'email',
        'password',
        'foto',
        'estado',
        'ultimo_login',
        'zona_horaria',
        'idioma',
        'tema',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'ultimo_login' => 'datetime',
    ];

    public function equipos()
    {
        return $this->hasMany(Equipo::class, 'propietario_id');
    }

    public function equiposComoMiembro()
    {
        return $this->belongsToMany(Equipo::class, 'miembros_equipo')
            ->withPivot('rol', 'permisos', 'fecha_ingreso');
    }

    public function tareasCreadas()
    {
        return $this->hasMany(Tarea::class, 'creador_id');
    }

    public function tareasAsignadas()
    {
        return $this->hasMany(Tarea::class, 'responsable_id');
    }

    public function comentarios()
    {
        return $this->hasMany(Comentario::class, 'usuario_id');
    }

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'usuario_id');
    }

    public function actividades()
    {
        return $this->hasMany(Actividad::class, 'usuario_id');
    }

    public function recordatorios()
    {
        return $this->hasMany(Recordatorio::class, 'usuario_id');
    }

    public function favoritos()
    {
        return $this->hasMany(Favorito::class, 'usuario_id');
    }

    public function tokensPush()
    {
        return $this->hasMany(TokenPush::class, 'usuario_id');
    }

    public function gamificacion()
    {
        return $this->hasOne(Gamificacion::class, 'usuario_id');
    }
}
