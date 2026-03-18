<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Aula;
use App\Models\Avaliacao;

class DashboardController extends Controller
{
    public function professor()
    {
        $totalAulas = Aula::count();
        $totalAlunos = User::where('tipo', 'aluno')->count();
        $totalProvas = Avaliacao::count();

        $mediaGeral = Avaliacao::avg('nota') ?? 0;

        $aulasRecentes = Aula::latest()->take(5)->get();

        return view('dashboard.professor', compact(
            'totalAulas',
            'totalAlunos',
            'totalProvas',
            'mediaGeral',
            'aulasRecentes'
        ));
    }
}