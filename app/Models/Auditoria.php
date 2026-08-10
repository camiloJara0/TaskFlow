<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    use HasFactory;

    protected $table = 'auditoria';

    protected $fillable = [
        'usuario_id',
        'accion',
        'objeto_type',
        'objeto_id',
        'descripcion',
        'detalles',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'detalles' => 'array',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function objeto()
    {
        return $this->morphTo();
    }
}
