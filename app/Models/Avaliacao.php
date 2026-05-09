<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Avaliacao extends Model
{
    use HasFactory;

    protected $table = 'avaliacoes';

    protected $fillable = [
        'titulo',
        'tempo_limite',
        'qtd_perguntas',
        'tipo',
        'aula_id',
    ];

    public function aula()
    {
        return $this->belongsTo(Aula::class, 'aula_id');
    }

    public function perguntas()
    {
        return $this->hasMany(Pergunta::class, 'avaliacao_id');
    }
}