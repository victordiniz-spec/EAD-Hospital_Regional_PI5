<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Modulo;

class Aula extends Model
{
    use HasFactory;

    protected $table = 'aulas';

    protected $fillable = [
        'titulo',
        'descricao',
        'video_url',
        'curso_id',
        'modulo_id' // 🔥 IMPORTANTE
    ];

    // 🔥 RELAÇÃO COM MÓDULO
    public function modulo()
    {
        return $this->belongsTo(Modulo::class);
    }
}