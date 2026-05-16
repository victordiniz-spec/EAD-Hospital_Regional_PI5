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
    /*
    |--------------------------------------------------------------------------
    | TIPOS DO SISTEMA
    |--------------------------------------------------------------------------
    | super_admin: você, dono/desenvolvedor do sistema.
    | professor: professora responsável por criar cursos, aulas, avaliações e avisos.
    | residente/preceptor: usuários com papel de aluno.
    */

    private array $tiposAdministrativos = ['super_admin', 'admin', 'professor'];
    private array $tiposAlunos = ['residente', 'preceptor'];

    // =========================
    // DASHBOARD PROFESSOR / ADMIN
    // =========================
    public function professor()
    {
        $totalAulas = Aula::count();

        /*
        |--------------------------------------------------------------------------
        | TOTAL DE USUÁRIOS APROVADOS
        |--------------------------------------------------------------------------
        | Não conta o super_admin como usuário comum.
        | Conta professor, residente e preceptor aprovados.
        */
        $totalAlunos = User::where('status', 'aprovado')
            ->where('tipo', '!=', 'super_admin')
            ->count();

        $totalProvas = Avaliacao::count();
        $mediaGeral = Nota::avg('nota') ?? 0;

        $aulasRecentes = Aula::orderBy('id', 'desc')
            ->take(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | USUÁRIOS PENDENTES
        |--------------------------------------------------------------------------
        | Nunca mostra super_admin como pendente.
        */
        $usuariosPendentes = User::where('status', 'pendente')
            ->where('tipo', '!=', 'super_admin')
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
        /*
        |--------------------------------------------------------------------------
        | RESIDENTE E PRECEPTOR SÃO ALUNOS
        |--------------------------------------------------------------------------
        | Aqui não entra professor e não entra super_admin.
        */
        $alunos = User::whereIn('users.tipo', $this->tiposAlunos)
            ->where('users.status', 'aprovado')
            ->leftJoin('notas', 'users.id', '=', 'notas.aluno_id')
            ->leftJoin('aulas_assistidas', 'users.id', '=', 'aulas_assistidas.aluno_id')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.cpf',
                'users.tipo',
                'users.status',
                DB::raw('MAX(notas.nota) as nota'),
                DB::raw('MAX(aulas_assistidas.assistido) as assistido')
            )
            ->groupBy(
                'users.id',
                'users.name',
                'users.email',
                'users.cpf',
                'users.tipo',
                'users.status'
            )
            ->orderBy('users.name')
            ->get();

        return view('dashboard.alunos', compact('alunos'));
    }

    // =========================
    // CONTROLE DE USUÁRIOS
    // =========================
    public function controleUsuarios()
    {
        /*
        |--------------------------------------------------------------------------
        | CONTROLE DE USUÁRIOS
        |--------------------------------------------------------------------------
        | O super_admin não aparece aqui como usuário comum.
        |
        | Mostra:
        | - professor: conta da professora;
        | - residente: aluno;
        | - preceptor: aluno.
        |
        | Se algum usuário antigo ainda estiver como "admin", também não será mostrado
        | para evitar confundir com o super_admin.
        */
        $usuarios = User::where('tipo', '!=', 'super_admin')
            ->whereIn('tipo', ['professor', 'residente', 'preceptor'])
            ->orderByRaw("
                CASE
                    WHEN status = 'pendente' THEN 0
                    WHEN status = 'aprovado' THEN 1
                    WHEN status = 'inutilizado' THEN 2
                    ELSE 3
                END
            ")
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

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD DO ALUNO
        |--------------------------------------------------------------------------
        | O super_admin também pode acessar esta tela para testar.
        | Por isso mantemos o cálculo baseado no usuário logado.
        |
        | A view aluno.blade.php já foi ajustada para buscar o curso atual/publicado
        | quando não houver matrícula específica.
        */

        // MÓDULOS COM AULAS
        $modulos = Modulo::with('aulas')->get();

        /*
        |--------------------------------------------------------------------------
        | TOTAL DE AULAS DO ALUNO
        |--------------------------------------------------------------------------
        | Usa matrícula se existir. Caso o usuário seja super_admin e não tenha matrícula,
        | usa o curso publicado/ativo/mais recente como fallback.
        */
        $cursoAtualId = null;

        if (DB::getSchemaBuilder()->hasTable('matriculas')) {
            $cursoAtualId = DB::table('matriculas')
                ->where('aluno_id', $alunoId)
                ->orderByDesc('id')
                ->value('curso_id');
        }

        if (!$cursoAtualId && DB::getSchemaBuilder()->hasTable('cursos')) {
            $queryCurso = DB::table('cursos');

            if (DB::getSchemaBuilder()->hasColumn('cursos', 'publicado')) {
                $queryCurso->where('publicado', true);
            } elseif (DB::getSchemaBuilder()->hasColumn('cursos', 'ativo')) {
                $queryCurso->where('ativo', true);
            } elseif (DB::getSchemaBuilder()->hasColumn('cursos', 'status')) {
                $queryCurso->whereIn('status', ['publicado', 'ativo', 'aprovado']);
            }

            $cursoAtualId = $queryCurso
                ->orderByDesc('id')
                ->value('id');
        }

        $modulosCursoIds = collect();

        if ($cursoAtualId && DB::getSchemaBuilder()->hasTable('modulos')) {
            $modulosCursoIds = DB::table('modulos')
                ->where('curso_id', $cursoAtualId)
                ->pluck('id');
        }

        $aulasCursoIds = collect();

        if ($modulosCursoIds->count() > 0 && DB::getSchemaBuilder()->hasTable('aulas')) {
            $aulasCursoIds = DB::table('aulas')
                ->whereIn('modulo_id', $modulosCursoIds)
                ->pluck('id');
        }

        if ($cursoAtualId && DB::getSchemaBuilder()->hasTable('aulas') && DB::getSchemaBuilder()->hasColumn('aulas', 'curso_id')) {
            $aulasPorCurso = DB::table('aulas')
                ->where('curso_id', $cursoAtualId)
                ->pluck('id');

            $aulasCursoIds = $aulasCursoIds
                ->merge($aulasPorCurso)
                ->unique()
                ->values();
        }

        $totalAulas = $aulasCursoIds->count();

        // AULAS ASSISTIDAS DO USUÁRIO LOGADO
        $aulasAssistidas = $totalAulas > 0
            ? DB::table('aulas_assistidas')
                ->where('aluno_id', $alunoId)
                ->whereIn('aula_id', $aulasCursoIds)
                ->where('assistido', true)
                ->distinct('aula_id')
                ->count('aula_id')
            : 0;

        // PROGRESSO
        $progresso = $totalAulas > 0
            ? round(($aulasAssistidas / $totalAulas) * 100)
            : 0;

        // TESTES PENDENTES DO CURSO ATUAL
        $avaliacoesCursoIds = collect();

        if ($aulasCursoIds->count() > 0) {
            $avaliacoesCursoIds = DB::table('avaliacoes')
                ->whereIn('aula_id', $aulasCursoIds)
                ->where(function ($query) {
                    $query->where('tipo', 'normal')
                        ->orWhere('tipo', 'pos_teste')
                        ->orWhere('tipo', 'pós-teste')
                        ->orWhereNull('tipo');
                })
                ->pluck('id');
        }

        $testesPendentes = $avaliacoesCursoIds->count() > 0
            ? DB::table('avaliacoes')
                ->whereIn('id', $avaliacoesCursoIds)
                ->whereNotIn('id', function ($query) use ($alunoId) {
                    $query->select('avaliacao_id')
                        ->from('notas')
                        ->where('aluno_id', $alunoId);
                })
                ->count()
            : 0;

        // MÉDIA
        $media = DB::table('notas')
            ->where('aluno_id', $alunoId)
            ->avg('nota') ?? 0;

        // PRÓXIMAS AULAS DO CURSO ATUAL
        $proximasAulas = $aulasCursoIds->count() > 0
            ? DB::table('aulas')
                ->whereIn('id', $aulasCursoIds)
                ->whereNotIn('id', function ($query) use ($alunoId) {
                    $query->select('aula_id')
                        ->from('aulas_assistidas')
                        ->where('aluno_id', $alunoId)
                        ->where('assistido', true);
                })
                ->limit(3)
                ->get()
            : collect();

        // AULAS ASSISTIDAS LISTA
        $aulasAssistidasLista = $aulasCursoIds->count() > 0
            ? DB::table('aulas')
                ->join('aulas_assistidas', 'aulas.id', '=', 'aulas_assistidas.aula_id')
                ->where('aulas_assistidas.aluno_id', $alunoId)
                ->where('aulas_assistidas.assistido', true)
                ->whereIn('aulas.id', $aulasCursoIds)
                ->select('aulas.*')
                ->limit(3)
                ->get()
            : collect();

        // TESTES DISPONÍVEIS/PENDENTES
        $listaTestes = $avaliacoesCursoIds->count() > 0
            ? DB::table('avaliacoes')
                ->leftJoin('aulas_assistidas', function ($join) use ($alunoId) {
                    $join->on('avaliacoes.aula_id', '=', 'aulas_assistidas.aula_id')
                        ->where('aulas_assistidas.aluno_id', $alunoId);
                })
                ->whereIn('avaliacoes.id', $avaliacoesCursoIds)
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
                ->get()
            : collect();

        // AVISOS ATIVOS PARA O ALUNO
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
