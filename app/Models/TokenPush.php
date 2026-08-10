<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TokenPush extends Model
{
    use HasFactory;

    protected $table = 'tokens_push';

    protected $fillable = [
        'usuario_id',
        'token',
        'p256dh',
        'auth',
        'navegador',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
