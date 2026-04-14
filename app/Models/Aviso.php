<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aviso extends Model
{
    protected $table = 'avisos';

    // 🔥 CAMPOS QUE PODEM SER SALVOS
    protected $fillable = [
        'titulo',
        'mensagem',
        'categoria'
    ];
}