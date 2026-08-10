<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReunionIntegrante extends Model
{
    use HasFactory;

    protected $table = 'reunion_integrantes';

    protected $fillable = [
        'id_reunion',
        'id_usuario',
    ];

    public function reunion()
    {
        return $this->belongsTo(Reunion::class, 'id_reunion');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
