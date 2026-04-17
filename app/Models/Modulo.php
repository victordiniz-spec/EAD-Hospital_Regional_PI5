<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    use HasFactory;

    protected $table = 'modulos';

    protected $fillable = [
        'nome',
        'curso_id'
    ];

    // 🔥 RELAÇÃO COM AULAS
    public function aulas()
    {
        return $this->hasMany(Aula::class);
    }
}