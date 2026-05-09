<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    use HasFactory;

    protected $table = 'cursos';

    protected $fillable = [
        'nome',
        'descricao',
        'professor_id',
    ];

    public function modulos()
    {
        return $this->hasMany(Modulo::class, 'curso_id')->orderBy('ordem');
    }

    public function aulas()
    {
        return $this->hasMany(Aula::class, 'curso_id');
    }
}