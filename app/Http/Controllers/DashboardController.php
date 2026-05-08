<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Aula;
use App\Models\Avaliacao;
use App\Models\Nota;
use App\Models\Aviso;
use App\Models\Modulo;

class DashboardController extends Controller
{
    // =========================
    // DASHBOARD PROFESSOR (ADMIN)
    // =========================
    public function professor()
    {
        $totalAulas = Aula::count();

        // TOTAL DE USUÁRIOS APROVADOS
        $totalAlunos = User::where('status', 'aprovado')->count();

        $totalProvas = Avaliacao::count();
        $mediaGeral = Nota::avg('nota') ?? 0;

        $aulasRecentes = Aula::orderBy('id', 'desc')
            ->take(5)
            ->get();

        // USUÁRIOS PENDENTES
        $usuariosPendentes = User::where('status', 'pendente')
            ->orderBy('created_at', 'desc')
            ->get();

        // AVISOS RECENTES PARA DASHBOARD DO PROFESSOR
        // Urgentes aparecem primeiro, depois importantes/informativos.
        $avisosRecentes = Aviso::query()
            ->orderByRaw("
                CASE
                    WHEN categoria = 'urgente' THEN 0
                    WHEN tipo = 'urgente' THEN 0
                    ELSE 1
                END
            ")
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        return view('dashboard.professor', compact(
            'totalAulas',
            'totalAlunos',
            'totalProvas',
            'mediaGeral',
            'aulasRecentes',
            'usuariosPendentes',
            'avisosRecentes'
        ));
    }

    // =========================
    // LISTA DE ALUNOS
    // =========================
    public function alunos()
    {
        $alunos = User::where('tipo', 'residente')
            ->where('status', 'aprovado')
            ->leftJoin('notas', 'users.id', '=', 'notas.aluno_id')
            ->leftJoin('aulas_assistidas', 'users.id', '=', 'aulas_assistidas.aluno_id')
            ->select(
                'users.id',
                'users.name',
                DB::raw('MAX(notas.nota) as nota'),
                DB::raw('MAX(aulas_assistidas.assistido) as assistido')
            )
            ->groupBy('users.id', 'users.name')
            ->get();

        return view('dashboard.alunos', compact('alunos'));
    }

    // =========================
    // CONTROLE DE USUÁRIOS
    // =========================
    public function controleUsuarios()
    {
        $usuarios = User::whereIn('tipo', ['residente', 'preceptor'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.controle-usuarios', compact('usuarios'));
    }

    // =========================
    // DASHBOARD ALUNO
    // =========================
    public function aluno()
    {
        $alunoId = auth()->id();

        // MÓDULOS COM AULAS
        $modulos = Modulo::with('aulas')->get();

        // TOTAL DE AULAS DO ALUNO
        $totalAulas = DB::table('aulas')
            ->join('cursos', 'aulas.curso_id', '=', 'cursos.id')
            ->join('matriculas', 'cursos.id', '=', 'matriculas.curso_id')
            ->where('matriculas.aluno_id', $alunoId)
            ->count();

        // AULAS ASSISTIDAS
        $aulasAssistidas = DB::table('aulas_assistidas')
            ->where('aluno_id', $alunoId)
            ->where('assistido', true)
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

        // PRÓXIMAS AULAS
        $proximasAulas = DB::table('aulas')
            ->whereNotIn('id', function ($query) use ($alunoId) {
                $query->select('aula_id')
                    ->from('aulas_assistidas')
                    ->where('aluno_id', $alunoId)
                    ->where('assistido', true);
            })
            ->limit(3)
            ->get();

        // AULAS ASSISTIDAS LISTA
        $aulasAssistidasLista = DB::table('aulas')
            ->join('aulas_assistidas', 'aulas.id', '=', 'aulas_assistidas.aula_id')
            ->where('aulas_assistidas.aluno_id', $alunoId)
            ->where('aulas_assistidas.assistido', true)
            ->select('aulas.*')
            ->limit(3)
            ->get();

        // TESTES DISPONÍVEIS/PENDENTES
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
                DB::raw('CASE WHEN aulas_assistidas.assistido = true THEN true ELSE false END as assistido')
            )
            ->limit(3)
            ->get();

        // AVISOS ATIVOS PARA O ALUNO
        // 1. Só mostra aviso que ainda não venceu.
        // 2. Urgente aparece primeiro.
        // 3. Depois mostra os mais recentes.
        $avisosRecentes = Aviso::query()
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', 'publicado');
            })
            ->orderByRaw("
                CASE
                    WHEN categoria = 'urgente' THEN 0
                    WHEN tipo = 'urgente' THEN 0
                    ELSE 1
                END
            ")
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        return view('dashboard.aluno', compact(
            'modulos',
            'totalAulas',
            'aulasAssistidas',
            'progresso',
            'testesPendentes',
            'media',
            'proximasAulas',
            'aulasAssistidasLista',
            'listaTestes',
            'avisosRecentes'
        ));
    }
}