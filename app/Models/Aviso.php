<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aviso extends Model
{
    use HasFactory;

    protected $table = 'avisos';

    protected $fillable = [
        'titulo',
        'mensagem',
        'descricao',
        'categoria',
        'tipo',
        'status',
        'expires_at',
        'favorito',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'favorito' => 'boolean',
    ];
}
