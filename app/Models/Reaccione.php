<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reaccione extends Model
{
    use HasFactory;

    protected $table = 'reacciones';
    public $timestamps = false;
    protected $fillable = [
        'usuario_id',
        'comentario_id',
        'tipo',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function comentario()
    {
        return $this->belongsTo(Comentario::class);
    }
}
