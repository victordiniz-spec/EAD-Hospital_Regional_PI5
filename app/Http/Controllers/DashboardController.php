<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Aula;
use App\Models\Avaliacao;
use App\Models\Nota;

class DashboardController extends Controller
{
    public function professor()
    {
        // Total de aulas
        $totalAulas = Aula::count();

        // Total de alunos
        $totalAlunos = User::where('tipo', 'aluno')->count();

        // Total de provas criadas
        // Verifica se o Model Avaliacao existe antes
        $totalProvas = class_exists(Avaliacao::class) ? Avaliacao::count() : 0;

        // Média geral das notas dos alunos
        $mediaGeral = class_exists(Nota::class) ? Nota::avg('nota') ?? 0 : 0;

        // Últimas 5 aulas criadas
        // Se a tabela não tiver 'created_at', ordena pelo id
        $aulasRecentes = Aula::orderBy('id', 'desc')->take(5)->get();

        return view('dashboard.professor', compact(
            'totalAulas',
            'totalAlunos',
            'totalProvas',
            'mediaGeral',
            'aulasRecentes'
        ));
    }
}