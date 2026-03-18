<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aula extends Model
{
    use HasFactory;

    // Garantindo que ele use a tabela correta
    protected $table = 'aulas';

    protected $fillable = [
        'titulo',
        'descricao',
        'video_url',
        'curso_id'
    ];
}