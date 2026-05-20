<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aula extends Model
{
    use HasFactory;

    protected $table = 'aulas';

    protected $fillable = [
        'titulo',
        'descricao',
        'video_url',
        'video',
        'curso_id',
        'modulo_id',
        'tempo_minimo_video',
        'tempo_maximo_video',
    ];

    protected $casts = [
        'tempo_minimo_video' => 'integer',
        'tempo_maximo_video' => 'integer',
    ];

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'modulo_id');
    }

    public function avaliacoes()
    {
        return $this->hasMany(Avaliacao::class, 'aula_id');
    }

    public function avaliacao()
    {
        return $this->hasOne(Avaliacao::class, 'aula_id');
    }
}