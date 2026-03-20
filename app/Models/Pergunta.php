<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pergunta extends Model
{
    protected $table = 'perguntas';

    protected $fillable = [
        'pergunta',
        'avaliacao_id'
    ];

    // 🔥 RELAÇÃO COM RESPOSTAS
    public function respostas()
    {
        return $this->hasMany(Resposta::class, 'pergunta_id');
    }

    // (opcional)
    public function avaliacao()
    {
        return $this->belongsTo(Avaliacao::class, 'avaliacao_id');
    }
}