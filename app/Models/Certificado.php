<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificado extends Model
{
    protected $table = 'certificados';

    protected $fillable = [
        'aluno_id',
        'nome_aluno',
        'curso',
        'carga_horaria',
        'codigo',
        'data_conclusao'
    ];
}