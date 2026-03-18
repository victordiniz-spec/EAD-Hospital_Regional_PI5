<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Avaliacao extends Model
{
    use HasFactory;

    // Nome da tabela no banco
    protected $table = 'avaliacoes';

    // Colunas preenchíveis
    protected $fillable = [
        'titulo',
        'aula_id'
    ];
}