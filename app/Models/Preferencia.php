<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Preferencia extends Model
{
    use HasFactory;

    protected $casts = [
        'apariencia' => 'array',
    ];

    protected $fillable = [
        'usuario_id',
        'apariencia',
    ];

    public $timestamps = false;
}
