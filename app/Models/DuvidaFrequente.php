<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DuvidaFrequente extends Model
{
    protected $table = 'duvidas_frequentes';

    protected $fillable = [
        'pergunta',
        'resposta',
        'categoria',
        'texto_botao',
        'rota_botao',
        'ativo',
        'ordem',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];
}