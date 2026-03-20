<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Aula;
use App\Models\Avaliacao;
use App\Models\Nota;

class DashboardController extends Controller
{
    public function professor()
    {
        $totalAulas = Aula::count();
        $totalAlunos = User::where('tipo', 'aluno')->count();
        $totalProvas = Avaliacao::count();
        $mediaGeral = Nota::avg('nota') ?? 0;
        $aulasRecentes = Aula::orderBy('id', 'desc')->take(5)->get();

        return view('dashboard.professor', compact(
            'totalAulas',
            'totalAlunos',
            'totalProvas',
            'mediaGeral',
            'aulasRecentes'
        ));
    }

    public function aluno()
    {
        $alunoId = auth()->id();

        // TOTAL DE AULAS
        $totalAulas = DB::table('aulas')
            ->join('cursos', 'aulas.curso_id', '=', 'cursos.id')
            ->join('matriculas', 'cursos.id', '=', 'matriculas.curso_id')
            ->where('matriculas.aluno_id', $alunoId)
            ->count();

        // AULAS ASSISTIDAS (TOTAL)
        $aulasAssistidas = DB::table('aulas_assistidas')
            ->where('aluno_id', $alunoId)
            ->count();

        // PROGRESSO
        $progresso = $totalAulas > 0
            ? ($aulasAssistidas / $totalAulas) * 100
            : 0;

        // TESTES PENDENTES
        $testesPendentes = DB::table('avaliacoes')
            ->whereNotIn('id', function ($query) use ($alunoId) {
                $query->select('avaliacao_id')
                      ->from('notas')
                      ->where('aluno_id', $alunoId);
            })
            ->count();

        // MÉDIA
        $media = DB::table('notas')
            ->where('aluno_id', $alunoId)
            ->avg('nota') ?? 0;

        // PRÓXIMAS AULAS (NÃO ASSISTIDAS)
        $proximasAulas = DB::table('aulas')
            ->whereNotIn('id', function ($query) use ($alunoId) {
                $query->select('aula_id')
                      ->from('aulas_assistidas')
                      ->where('aluno_id', $alunoId);
            })
            ->limit(3)
            ->get();

        // 🔥 AULAS ASSISTIDAS (LISTA NOVA)
        $aulasAssistidasLista = DB::table('aulas')
            ->join('aulas_assistidas', 'aulas.id', '=', 'aulas_assistidas.aula_id')
            ->where('aulas_assistidas.aluno_id', $alunoId)
            ->select('aulas.*')
            ->limit(3)
            ->get();

        // LISTA DE TESTES
        $listaTestes = DB::table('avaliacoes')
            ->leftJoin('aulas_assistidas', function ($join) use ($alunoId) {
                $join->on('avaliacoes.aula_id', '=', 'aulas_assistidas.aula_id')
                    ->where('aulas_assistidas.aluno_id', $alunoId);
            })
            ->whereNotIn('avaliacoes.id', function ($query) use ($alunoId) {
                $query->select('avaliacao_id')
                    ->from('notas')
                    ->where('aluno_id', $alunoId);
            })
            ->select(
                'avaliacoes.*',
                DB::raw('IF(aulas_assistidas.assistido = 1, true, false) as assistido')
            )
            ->limit(3)
            ->get();

        return view('dashboard.aluno', compact(
            'totalAulas',
            'aulasAssistidas',
            'progresso',
            'testesPendentes',
            'media',
            'proximasAulas',
            'aulasAssistidasLista', // 🔥 IMPORTANTE
            'listaTestes'
        ));
    }
}