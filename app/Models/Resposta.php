<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resposta extends Model
{
    protected $table = 'respostas';

    protected $fillable = [
        'resposta',
        'correta',
        'pergunta_id'
    ];

    // 🔥 RELAÇÃO COM PERGUNTA
    public function pergunta()
    {
        return $this->belongsTo(Pergunta::class, 'pergunta_id');
    }
}