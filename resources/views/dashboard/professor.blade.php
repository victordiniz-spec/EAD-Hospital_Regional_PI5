@extends('layout.app')

@section('title', 'Dashboard Professor')

@section('content')

@php
    use Illuminate\Support\Facades\DB;
    use Carbon\Carbon;

    Carbon::setLocale('pt_BR');

    $mesesNomes = [
        1 => 'JAN',
        2 => 'FEV',
        3 => 'MAR',
        4 => 'ABR',
        5 => 'MAI',
        6 => 'JUN',
        7 => 'JUL',
        8 => 'AGO',
        9 => 'SET',
        10 => 'OUT',
        11 => 'NOV',
        12 => 'DEZ',
    ];

    /*
    |--------------------------------------------------------------------------
    | FUNÇÕES AUXILIARES
    |--------------------------------------------------------------------------
    | Corrige dados irreais:
    | - prova final não entra como pós-teste;
    | - tentativas repetidas contam apenas uma vez;
    | - notas 0 a 10 viram porcentagem.
    */
    $tabelaExiste = function (string $tabela): bool {
        return DB::getSchemaBuilder()->hasTable($tabela);
    };

    $colunaExiste = function (string $tabela, string $coluna) use ($tabelaExiste): bool {
        return $tabelaExiste($tabela) && DB::getSchemaBuilder()->hasColumn($tabela, $coluna);
    };

    $normalizarNotaPercentual = function ($nota): ?float {
        if ($nota === null || $nota === '') {
            return null;
        }

        $nota = (float) $nota;

        // Se a nota estiver em escala 0 a 10, transforma em porcentagem.
        // Exemplo: 10 = 100%, 7 = 70%.
        if ($nota <= 10) {
            return round($nota * 10, 2);
        }

        return round($nota, 2);
    };

    $formatarPercentualProfessor = function ($valor) {
        return number_format((float) ($valor ?? 0), 1, ',', '.') . '%';
    };

    $primeiraColunaNota = function () use ($colunaExiste): ?string {
        foreach (['porcentagem', 'nota', 'pontuacao', 'valor', 'media', 'resultado'] as $coluna) {
            if ($colunaExiste('notas', $coluna)) {
                return $coluna;
            }
        }

        return null;
    };

    $notaRegistroPercentual = function ($registroNota) use ($normalizarNotaPercentual) {
        if (!$registroNota) {
            return null;
        }

        foreach (['porcentagem', 'nota', 'pontuacao', 'valor', 'media', 'resultado'] as $coluna) {
            if (isset($registroNota->{$coluna}) && $registroNota->{$coluna} !== null) {
                return $normalizarNotaPercentual($registroNota->{$coluna});
            }
        }

        return null;
    };

    $cursoAtualIdAluno = function ($alunoId) use ($tabelaExiste, $colunaExiste) {
        $cursoAtualId = null;

        if ($tabelaExiste('matriculas')) {
            $cursoAtualId = DB::table('matriculas')
                ->where('aluno_id', $alunoId)
                ->orderByDesc('id')
                ->value('curso_id');
        }

        if (!$cursoAtualId && $tabelaExiste('cursos')) {
            $queryCurso = DB::table('cursos');

            if ($colunaExiste('cursos', 'publicado')) {
                $queryCurso->where('publicado', true);
            } elseif ($colunaExiste('cursos', 'ativo')) {
                $queryCurso->where('ativo', true);
            } elseif ($colunaExiste('cursos', 'status')) {
                $queryCurso->whereIn('status', ['publicado', 'ativo', 'aprovado']);
            }

            $cursoAtualId = $queryCurso
                ->orderByDesc('id')
                ->value('id');
        }

        return $cursoAtualId ? (int) $cursoAtualId : null;
    };

    $aulasIdsCurso = function ($cursoId) use ($tabelaExiste, $colunaExiste) {
        $aulasIds = collect();

        if (!$cursoId || !$tabelaExiste('aulas')) {
            return $aulasIds;
        }

        if ($tabelaExiste('modulos')) {
            $modulosIds = DB::table('modulos')
                ->where('curso_id', $cursoId)
                ->pluck('id');

            if ($modulosIds->count() > 0) {
                $aulasIds = DB::table('aulas')
                    ->whereIn('modulo_id', $modulosIds)
                    ->pluck('id');
            }
        }

        if ($colunaExiste('aulas', 'curso_id')) {
            $aulasDiretas = DB::table('aulas')
                ->where('curso_id', $cursoId)
                ->pluck('id');

            $aulasIds = $aulasIds
                ->merge($aulasDiretas)
                ->unique()
                ->values();
        }

        return $aulasIds;
    };

    $posTestesIdsCurso = function ($aulasIds) use ($tabelaExiste, $colunaExiste) {
        if (!$aulasIds || $aulasIds->count() === 0 || !$tabelaExiste('avaliacoes')) {
            return collect();
        }

        $query = DB::table('avaliacoes')
            ->whereIn('aula_id', $aulasIds);

        if ($colunaExiste('avaliacoes', 'tipo')) {
            $query->where(function ($subQuery) {
                $subQuery->whereNull('tipo')
                    ->orWhereIn('tipo', [
                        'normal',
                        'pos_teste',
                        'pós-teste',
                        'pos-teste',
                        'posteste',
                    ]);
            });

            // Segurança extra: prova final nunca entra como pós-teste.
            $query->where(function ($subQuery) {
                $subQuery->whereNull('tipo')
                    ->orWhereNotIn('tipo', [
                        'final',
                        'prova_final',
                        'prova-final',
                        'finalizacao',
                    ]);
            });
        }

        return $query->pluck('id')->unique()->values();
    };

    /*
    |--------------------------------------------------------------------------
    | MÉTRICAS REAIS DO PROFESSOR
    |--------------------------------------------------------------------------
    */
    $totalAlunos = $tabelaExiste('users')
        ? DB::table('users')
            ->where('status', 'aprovado')
            ->whereIn('tipo', ['residente', 'preceptor'])
            ->count()
        : 0;

    $totalAulas = $tabelaExiste('aulas')
        ? DB::table('aulas')->count()
        : 0;

    $posTestesTodosIds = collect();

    if ($tabelaExiste('avaliacoes')) {
        $queryPosTestesTodos = DB::table('avaliacoes')
            ->whereNotNull('aula_id');

        if ($colunaExiste('avaliacoes', 'tipo')) {
            $queryPosTestesTodos->where(function ($query) {
                $query->whereNull('tipo')
                    ->orWhereIn('tipo', [
                        'normal',
                        'pos_teste',
                        'pós-teste',
                        'pos-teste',
                        'posteste',
                    ]);
            });

            $queryPosTestesTodos->where(function ($query) {
                $query->whereNull('tipo')
                    ->orWhereNotIn('tipo', [
                        'final',
                        'prova_final',
                        'prova-final',
                        'finalizacao',
                    ]);
            });
        }

        $posTestesTodosIds = $queryPosTestesTodos
            ->pluck('id')
            ->unique()
            ->values();
    }

    $totalProvas = $posTestesTodosIds->count();

    $colunaNota = $primeiraColunaNota();

    $mediaGeral = 0;

    if ($colunaNota && $posTestesTodosIds->count() > 0 && $tabelaExiste('notas')) {
        $notasGeral = DB::table('notas')
            ->whereIn('avaliacao_id', $posTestesTodosIds)
            ->whereNotNull($colunaNota)
            ->pluck($colunaNota)
            ->map(fn ($nota) => $normalizarNotaPercentual($nota))
            ->filter(fn ($nota) => $nota !== null);

        $mediaGeral = $notasGeral->count() > 0
            ? round($notasGeral->avg(), 1)
            : 0;
    }

    /*
    |--------------------------------------------------------------------------
    | GRÁFICO REAL DOS ÚLTIMOS 6 MESES
    |--------------------------------------------------------------------------
    */
    $dadosGrafico = [];

    for ($i = 5; $i >= 0; $i--) {
        $inicio = now()->copy()->subMonths($i)->startOfMonth();
        $fim = now()->copy()->subMonths($i)->endOfMonth();

        $aulasAssistidasMes = $tabelaExiste('aulas_assistidas')
            ? DB::table('aulas_assistidas')
                ->where('assistido', true)
                ->whereBetween('created_at', [$inicio, $fim])
                ->distinct('aula_id')
                ->count('aula_id')
            : 0;

        $posTestesFeitosMes = 0;

        if ($posTestesTodosIds->count() > 0 && $tabelaExiste('notas')) {
            $posTestesFeitosMes = DB::table('notas')
                ->whereIn('avaliacao_id', $posTestesTodosIds)
                ->whereBetween('created_at', [$inicio, $fim])
                ->get(['aluno_id', 'avaliacao_id'])
                ->unique(fn ($item) => $item->aluno_id . '-' . $item->avaliacao_id)
                ->count();
        }

        $totalMes = $aulasAssistidasMes + $posTestesFeitosMes;

        $dadosGrafico[] = [
            'mes' => $mesesNomes[(int) $inicio->format('n')],
            'aulas' => $aulasAssistidasMes,
            'testes' => $posTestesFeitosMes,
            'total' => $totalMes,
        ];
    }

    $maiorValorGrafico = collect($dadosGrafico)->max('total') ?: 1;
    $totalAtividadesPeriodo = collect($dadosGrafico)->sum('total');
    $melhorMes = collect($dadosGrafico)->sortByDesc('total')->first();

    $totalAvisos = isset($avisosRecentes) ? $avisosRecentes->count() : 0;
    $totalPendentes = isset($usuariosPendentes) ? $usuariosPendentes->count() : 0;

    $usuariosAprovadosRecentes = $tabelaExiste('users')
        ? DB::table('users')
            ->where('status', 'aprovado')
            ->whereIn('tipo', ['residente', 'preceptor'])
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get()
        : collect();

    $totalAprovadosHistorico = $usuariosAprovadosRecentes->count();

    /*
    |--------------------------------------------------------------------------
    | PÓDIO DOS ALUNOS
    |--------------------------------------------------------------------------
    | Substitui "Videoaulas Recentes", porque professor não assiste aula.
    | Ranking usa progresso, média de pós-testes e prova final.
    */
    $alunosRanking = $tabelaExiste('users')
        ? DB::table('users')
            ->where('status', 'aprovado')
            ->whereIn('tipo', ['residente', 'preceptor'])
            ->orderBy('name')
            ->get()
        : collect();

    $rankingAlunos = collect();

    foreach ($alunosRanking as $alunoRanking) {
        $cursoId = $cursoAtualIdAluno($alunoRanking->id);
        $aulasIds = $aulasIdsCurso($cursoId);
        $posIds = $posTestesIdsCurso($aulasIds);

        $totalAulasAluno = $aulasIds->count();

        $aulasAssistidasAluno = 0;

        if ($totalAulasAluno > 0 && $tabelaExiste('aulas_assistidas')) {
            $aulasAssistidasAluno = DB::table('aulas_assistidas')
                ->where('aluno_id', $alunoRanking->id)
                ->whereIn('aula_id', $aulasIds)
                ->where('assistido', true)
                ->distinct('aula_id')
                ->count('aula_id');

            $aulasAssistidasAluno = min($aulasAssistidasAluno, $totalAulasAluno);
        }

        $totalPosAluno = $posIds->count();
        $posFeitosAluno = 0;
        $mediaAluno = 0;

        if ($totalPosAluno > 0 && $tabelaExiste('notas')) {
            $posFeitosAluno = DB::table('notas')
                ->where('aluno_id', $alunoRanking->id)
                ->whereIn('avaliacao_id', $posIds)
                ->pluck('avaliacao_id')
                ->unique()
                ->count();

            $posFeitosAluno = min($posFeitosAluno, $totalPosAluno);

            if ($colunaNota) {
                $notasAluno = DB::table('notas')
                    ->where('aluno_id', $alunoRanking->id)
                    ->whereIn('avaliacao_id', $posIds)
                    ->whereNotNull($colunaNota)
                    ->pluck($colunaNota)
                    ->map(fn ($nota) => $normalizarNotaPercentual($nota))
                    ->filter(fn ($nota) => $nota !== null);

                $mediaAluno = $notasAluno->count() > 0
                    ? round($notasAluno->avg(), 1)
                    : 0;
            }
        }

        $progressoAluno = ($totalAulasAluno + $totalPosAluno) > 0
            ? round((($aulasAssistidasAluno + $posFeitosAluno) / ($totalAulasAluno + $totalPosAluno)) * 100)
            : 0;

        $notaFinalAluno = null;

        if ($tabelaExiste('avaliacoes') && $tabelaExiste('notas')) {
            $queryFinal = DB::table('avaliacoes')
                ->where('tipo', 'final');

            if ($cursoId && $colunaExiste('avaliacoes', 'curso_id')) {
                $queryFinal->where('curso_id', $cursoId);
            }

            $provaFinalAluno = $queryFinal
                ->orderByDesc('id')
                ->first();

            if ($provaFinalAluno) {
                $registroFinalAluno = DB::table('notas')
                    ->where('aluno_id', $alunoRanking->id)
                    ->where('avaliacao_id', $provaFinalAluno->id)
                    ->orderByDesc('created_at')
                    ->orderByDesc('id')
                    ->first();

                $notaFinalAluno = $notaRegistroPercentual($registroFinalAluno);
            }
        }

        $notaFinalParaRanking = $notaFinalAluno ?? 0;

        $pontuacaoRanking = round(
            ($progressoAluno * 0.45)
            + ($mediaAluno * 0.35)
            + ($notaFinalParaRanking * 0.20),
            1
        );

        $rankingAlunos->push((object) [
            'id' => $alunoRanking->id,
            'name' => $alunoRanking->name,
            'email' => $alunoRanking->email,
            'inicial' => strtoupper(mb_substr($alunoRanking->name, 0, 1)),
            'progresso' => $progressoAluno,
            'media' => $mediaAluno,
            'nota_final' => $notaFinalAluno,
            'aulas' => $aulasAssistidasAluno . '/' . $totalAulasAluno,
            'postestes' => $posFeitosAluno . '/' . $totalPosAluno,
            'pontuacao' => $pontuacaoRanking,
        ]);
    }

    $rankingAlunos = $rankingAlunos
        ->sortByDesc('pontuacao')
        ->values();

    $podioAlunos = $rankingAlunos
        ->take(3)
        ->values();
@endphp

<style>
    html, body {
        background: #F3F7F3 !important;
        margin: 0;
        padding: 0;
        width: 100%;
        min-height: 100%;
    }

    #app {
        background: #F3F7F3 !important;
        min-height: 100vh;
        width: 100%;
    }

    .historico-cascata {
        max-height: 0;
        overflow: hidden;
        opacity: 0;
        transform: translateY(-8px);
        transition: all 0.35s ease;
    }

    .historico-cascata.aberto {
        max-height: 900px;
        opacity: 1;
        transform: translateY(0);
    }

    .icone-seta-historico {
        transition: transform 0.25s ease;
    }

    .icone-seta-historico.aberto {
        transform: rotate(180deg);
    }

    /*
    |--------------------------------------------------------------------------
    | AJUSTES RESPONSIVOS DO DASHBOARD
    |--------------------------------------------------------------------------
    */
    .grafico-engajamento {
        width: 100%;
        max-width: 100%;
        overflow: hidden;
    }

    .grafico-coluna {
        min-width: 0;
    }

    @media (max-width: 640px) {
        section {
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }

        .grafico-card-professor {
            min-height: auto !important;
            overflow: hidden !important;
        }

        .grafico-engajamento {
            height: 250px !important;
            gap: 0.35rem !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        .grafico-barra-professor {
            height: 145px !important;
            max-width: 30px !important;
        }

        .grafico-tooltip-professor {
            display: none !important;
        }

        .grafico-legenda-professor {
            gap: 0.75rem !important;
            font-size: 11px !important;
        }

        .grafico-mes-professor {
            font-size: 9px !important;
        }
    }

    .card-podio {
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .card-podio:hover {
        transform: translateY(-6px);
        box-shadow: 0 18px 35px rgba(0, 77, 58, 0.13);
    }

    .base-podio {
        background: linear-gradient(180deg, #EAF5EF 0%, #DCE7DE 100%);
    }

    .trofeu-podio {
        filter: drop-shadow(0 8px 14px rgba(0, 77, 58, 0.16));
    }

</style>

<div class="flex min-h-screen w-full bg-[#F3F7F3] text-[#003C2F] overflow-x-hidden">

    @include('partials.sidebar-professor')

    <!-- CONTEÚDO -->
    <main class="flex-1 min-w-0 w-full bg-[#F3F7F3] overflow-x-hidden">

        @include('partials.navbar')

        <section class="p-4 sm:p-6 lg:p-8">

            <!-- ALERTAS -->
            @if(session('success'))
                <div class="bg-green-100 text-green-700 p-4 mb-5 rounded-2xl border border-green-200 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 text-red-700 p-4 mb-5 rounded-2xl border border-red-200 shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 text-red-700 p-4 mb-5 rounded-2xl border border-red-200 shadow-sm">
                    <p class="font-semibold mb-2">Corrija os erros abaixo:</p>
                    <ul class="list-disc pl-5 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- CABEÇALHO -->
            <div class="mb-7 flex flex-col xl:flex-row xl:items-end xl:justify-between gap-5">

                <div>
                    <div class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-wider text-[#00A63E] mb-2">
                        <span class="w-2 h-2 rounded-full bg-[#00A63E]"></span>
                        Sistema operacional
                    </div>

                    <h1 class="text-3xl sm:text-4xl font-extrabold text-[#003C2F] tracking-tight">
                        Visão Geral Administrativa
                    </h1>

                    <p class="text-sm text-[#60756B] mt-2 max-w-2xl">
                        Acompanhe métricas de engajamento, produção de conteúdo, comunicados institucionais e solicitações de acesso.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">

                    <a href="{{ route('controle.usuarios') }}"
                       class="inline-flex items-center justify-center gap-2 bg-white border border-[#DCE7DE] text-[#003C2F] px-4 py-3 rounded-2xl shadow-sm hover:shadow-md hover:border-[#00A63E]/40 transition">

                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="w-5 h-5 text-[#00A63E]"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="1.8"
                                  d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0zM4.5 20.25a8.25 8.25 0 0 1 15 0"/>
                        </svg>

                        Gerenciar usuários
                    </a>

                    <button onclick="abrirModalAviso()"
                            class="inline-flex items-center justify-center gap-2 bg-[#004D3A] text-white px-4 py-3 rounded-2xl shadow-sm hover:bg-[#003C2F] transition">

                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="w-5 h-5"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="1.8"
                                  d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>

                        Criar aviso
                    </button>

                </div>

            </div>

            <!-- CARDS MÉTRICAS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-7">

                <!-- Usuários -->
                <div class="bg-white rounded-3xl p-5 shadow-sm border border-[#E3EBE4] hover:shadow-lg transition">
                    <div class="flex items-start justify-between mb-5">
                        <div class="w-12 h-12 rounded-2xl bg-[#EAF5EF] flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-6 h-6 text-[#004D3A]"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M18 18.72a8.94 8.94 0 0 0-6-2.22 8.94 8.94 0 0 0-6 2.22M15 11.25a3 3 0 1 0-6 0 3 3 0 0 0 6 0z"/>
                            </svg>
                        </div>

                        <span class="text-[11px] font-bold bg-green-100 text-green-700 px-2.5 py-1 rounded-full">
                            Ativos
                        </span>
                    </div>

                    <p class="text-sm text-[#60756B]">Total Usuários</p>
                    <h3 class="text-3xl font-extrabold mt-1 text-[#003C2F]">{{ $totalAlunos }}</h3>

                    <div class="mt-4 h-1.5 bg-[#E8EFE9] rounded-full overflow-hidden">
                        <div class="h-full bg-[#004D3A] rounded-full" style="width: {{ $totalAlunos > 0 ? 100 : 0 }}%;"></div>
                    </div>
                </div>

                <!-- Aulas -->
                <div class="bg-white rounded-3xl p-5 shadow-sm border border-[#E3EBE4] hover:shadow-lg transition">
                    <div class="flex items-start justify-between mb-5">
                        <div class="w-12 h-12 rounded-2xl bg-[#EAF5EF] flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-6 h-6 text-[#00A63E]"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M5.25 5.653c0-1.427 1.54-2.33 2.79-1.637l9.54 5.347c1.26.707 1.26 2.567 0 3.274l-9.54 5.347c-1.25.693-2.79-.21-2.79-1.637V5.653z"/>
                            </svg>
                        </div>

                        <span class="text-[11px] font-bold bg-green-100 text-green-700 px-2.5 py-1 rounded-full">
                            Publicadas
                        </span>
                    </div>

                    <p class="text-sm text-[#60756B]">Aulas Publicadas</p>
                    <h3 class="text-3xl font-extrabold mt-1 text-[#003C2F]">{{ $totalAulas }}</h3>

                    <div class="mt-4 h-1.5 bg-[#E8EFE9] rounded-full overflow-hidden">
                        <div class="h-full bg-[#00A63E] rounded-full" style="width: {{ $totalAulas > 0 ? 100 : 0 }}%;"></div>
                    </div>
                </div>

                <!-- Pós-testes -->
                <div class="bg-white rounded-3xl p-5 shadow-sm border border-[#E3EBE4] hover:shadow-lg transition">
                    <div class="flex items-start justify-between mb-5">
                        <div class="w-12 h-12 rounded-2xl bg-[#EAF5EF] flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-6 h-6 text-[#7CA982]"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M9 12h6m-6 4h6M9 8h6M5 4h14v16H5z"/>
                            </svg>
                        </div>

                        <span class="text-[11px] font-bold bg-gray-100 text-gray-600 px-2.5 py-1 rounded-full">
                            Testes
                        </span>
                    </div>

                    <p class="text-sm text-[#60756B]">Pós-testes</p>
                    <h3 class="text-3xl font-extrabold mt-1 text-[#003C2F]">{{ $totalProvas }}</h3>

                    <div class="mt-4 h-1.5 bg-[#E8EFE9] rounded-full overflow-hidden">
                        <div class="h-full bg-[#7CA982] rounded-full" style="width: {{ $totalProvas > 0 ? 100 : 0 }}%;"></div>
                    </div>
                </div>

                <!-- Média -->
                <div class="bg-white rounded-3xl p-5 shadow-sm border border-[#E3EBE4] hover:shadow-lg transition">
                    <div class="flex items-start justify-between mb-5">
                        <div class="w-12 h-12 rounded-2xl bg-[#EAF5EF] flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-6 h-6 text-[#004D3A]"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/>
                            </svg>
                        </div>

                        <span class="text-[11px] font-bold bg-green-100 text-green-700 px-2.5 py-1 rounded-full">
                            Média
                        </span>
                    </div>

                    <p class="text-sm text-[#60756B]">Média Geral</p>
                    <h3 class="text-3xl font-extrabold mt-1 text-[#003C2F]">
                        {{ $formatarPercentualProfessor($mediaGeral) }}
                    </h3>

                    <div class="mt-4 h-1.5 bg-[#E8EFE9] rounded-full overflow-hidden">
                        <div class="h-full bg-[#004D3A] rounded-full" style="width: {{ min(100, max(0, $mediaGeral)) }}%;"></div>
                    </div>
                </div>

            </div>

            <!-- GRID PRINCIPAL -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-7 mb-7">

                <!-- DESEMPENHO REAL -->
                <div class="xl:col-span-2 bg-white rounded-3xl p-4 sm:p-6 shadow-sm border border-[#E3EBE4] min-h-[380px] grafico-card-professor overflow-hidden">

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                        <div>
                            <h2 class="font-extrabold text-[#003C2F] text-lg">
                                Desempenho Geral de Engajamento
                            </h2>
                            <p class="text-xs text-[#60756B]">
                                Dados reais dos últimos 6 meses: aulas assistidas + pós-testes feitos.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <span class="px-4 py-2 rounded-xl bg-[#F1F6F2] text-[#003C2F] text-xs font-bold">
                                {{ $totalAtividadesPeriodo }} atividade(s)
                            </span>

                            <span class="px-4 py-2 rounded-xl bg-[#004D3A] text-white text-xs font-bold">
                                Melhor: {{ $melhorMes['mes'] ?? '-' }}
                            </span>
                        </div>
                    </div>

                    @if($totalAtividadesPeriodo > 0)

                        <div class="grafico-engajamento h-72 grid grid-cols-6 items-end gap-1 sm:gap-3 px-0 sm:px-6 pt-6">

                            @foreach($dadosGrafico as $item)

                                @php
                                    $alturaTotal = $maiorValorGrafico > 0
                                        ? max(6, round(($item['total'] / $maiorValorGrafico) * 100))
                                        : 0;

                                    $alturaAulas = $item['total'] > 0
                                        ? round(($item['aulas'] / $item['total']) * $alturaTotal)
                                        : 0;

                                    $alturaTestes = $item['total'] > 0
                                        ? $alturaTotal - $alturaAulas
                                        : 0;
                                @endphp

                                <div class="grafico-coluna min-w-0 flex flex-col items-center justify-end gap-2 sm:gap-3 h-full group">

                                    <div class="grafico-tooltip-professor text-center opacity-0 group-hover:opacity-100 transition bg-[#003C2F] text-white rounded-xl px-3 py-2 shadow-lg text-xs">
                                        <p class="font-bold">{{ $item['total'] }} total</p>
                                        <p>{{ $item['aulas'] }} aula(s)</p>
                                        <p>{{ $item['testes'] }} teste(s)</p>
                                    </div>

                                    <div class="grafico-barra-professor w-full max-w-[30px] sm:max-w-[46px] bg-[#E8EFE9] rounded-full flex items-end overflow-hidden h-[145px] sm:h-[190px] border border-[#DCE7DE]">

                                        <div class="w-full flex flex-col justify-end" style="height: {{ $alturaTotal }}%;">

                                            @if($alturaTestes > 0)
                                                <div class="w-full bg-[#00A63E]" style="height: {{ $alturaTestes }}%;"></div>
                                            @endif

                                            @if($alturaAulas > 0)
                                                <div class="w-full bg-[#004D3A]" style="height: {{ $alturaAulas }}%;"></div>
                                            @endif

                                        </div>

                                    </div>

                                    <div class="text-center">
                                        <span class="grafico-mes-professor block text-[10px] font-bold text-[#60756B]">
                                            {{ $item['mes'] }}
                                        </span>

                                        <span class="block text-[11px] font-extrabold text-[#003C2F]">
                                            {{ $item['total'] }}
                                        </span>
                                    </div>

                                </div>

                            @endforeach

                        </div>

                        <div class="grafico-legenda-professor mt-5 flex flex-wrap items-center justify-center gap-4 text-xs text-[#60756B]">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-[#004D3A]"></span>
                                Aulas assistidas
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-[#00A63E]"></span>
                                Pós-testes feitos
                            </div>
                        </div>

                    @else

                        <div class="h-72 flex items-center justify-center bg-[#F8FBF8] border border-dashed border-[#DCE7DE] rounded-3xl">
                            <div class="text-center max-w-md px-4">
                                <div class="w-16 h-16 rounded-full bg-[#EAF5EF] mx-auto mb-4 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="w-8 h-8 text-[#004D3A]"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="1.8"
                                              d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125z"/>
                                    </svg>
                                </div>

                                <h3 class="font-extrabold text-[#003C2F]">
                                    Ainda não há dados de engajamento
                                </h3>

                                <p class="text-sm text-[#60756B] mt-2">
                                    Quando os alunos assistirem aulas ou fizerem pós-testes, o gráfico será preenchido automaticamente.
                                </p>
                            </div>
                        </div>

                    @endif

                </div>

                <!-- AVISOS RECENTES -->
                <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-[#E3EBE4]">

                    <div class="flex items-center justify-between mb-5">
                        <h2 class="font-extrabold text-[#003C2F] text-lg">
                            Avisos Recentes
                        </h2>

                        <a href="{{ route('avisos') }}"
                           class="text-[11px] font-bold text-[#00A63E] hover:text-[#004D3A] transition">
                            Ver todos
                        </a>
                    </div>

                    <div class="space-y-5">
                        @forelse($avisosRecentes->take(4) as $aviso)

                            <div class="relative pl-5 border-l-2
                                @if(($aviso->categoria ?? '') === 'urgente') border-red-400
                                @elseif(($aviso->categoria ?? '') === 'informativo') border-[#00A63E]
                                @else border-[#9DB7A4]
                                @endif">

                                <div class="flex items-start justify-between gap-3">
                                    <span class="text-[10px] font-bold uppercase px-2 py-1 rounded
                                        @if(($aviso->categoria ?? '') === 'urgente') bg-red-100 text-red-700
                                        @elseif(($aviso->categoria ?? '') === 'informativo') bg-green-100 text-green-700
                                        @else bg-gray-100 text-gray-600
                                        @endif">
                                        {{ strtoupper($aviso->categoria ?? 'INFO') }}
                                    </span>

                                    <span class="text-[10px] text-[#8A9B92] whitespace-nowrap">
                                        {{ $aviso->created_at ? Carbon::parse($aviso->created_at)->locale('pt_BR')->diffForHumans() : '' }}
                                    </span>
                                </div>

                                <h3 class="font-bold text-sm text-[#003C2F] mt-2 break-words">
                                    {{ $aviso->titulo }}
                                </h3>

                                <p class="text-xs text-[#60756B] mt-1 break-words">
                                    {{ $aviso->mensagem ?? $aviso->descricao ?? '' }}
                                </p>
                            </div>

                        @empty
                            <div class="text-center py-8 text-[#60756B]">
                                <div class="w-12 h-12 rounded-full bg-[#F1F6F2] flex items-center justify-center mx-auto mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="w-6 h-6"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="1.8"
                                              d="M14.857 17.082a23.848 23.848 0 0 1-5.714 0M18 8a6 6 0 1 0-12 0c0 7-3 7-3 7h18s-3 0-3-7"/>
                                    </svg>
                                </div>

                                <p class="text-sm">Nenhum aviso encontrado.</p>
                            </div>
                        @endforelse
                    </div>

                    <button onclick="abrirModalAviso()"
                            class="mt-6 w-full border border-dashed border-[#AFC5B5] text-[#004D3A] rounded-2xl py-3 text-sm font-bold hover:bg-[#F1F6F2] transition flex items-center justify-center gap-2">
                        <span>＋</span>
                        Criar novo aviso
                    </button>
                </div>

            </div>

            <!-- AULAS RECENTES + SOLICITAÇÕES -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-7">

                <!-- PÓDIO DOS ALUNOS -->
                <div class="xl:col-span-2 bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-[#E3EBE4] overflow-hidden">

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                        <div>
                            <h2 class="font-extrabold text-[#003C2F] text-lg">
                                Pódio dos Alunos
                            </h2>
                            <p class="text-xs text-[#60756B]">
                                Ranking divertido baseado em progresso, média dos pós-testes e nota da prova final.
                            </p>
                        </div>

                        <a href="{{ route('acompanhamento.residentes') }}"
                           class="inline-flex items-center justify-center gap-2 bg-[#004D3A] text-white px-4 py-2.5 rounded-2xl text-sm font-bold hover:bg-[#003C2F] transition">
                            Ver acompanhamento
                        </a>
                    </div>

                    @if($podioAlunos->count() > 0)

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-end">

                            @php
                                $ordemPodio = [
                                    1 => ['posicao' => 2, 'altura' => 'h-24', 'emoji' => '🥈', 'titulo' => '2º lugar', 'cor' => 'text-slate-600', 'bg' => 'bg-slate-100'],
                                    0 => ['posicao' => 1, 'altura' => 'h-32', 'emoji' => '🏆', 'titulo' => '1º lugar', 'cor' => 'text-yellow-700', 'bg' => 'bg-yellow-100'],
                                    2 => ['posicao' => 3, 'altura' => 'h-20', 'emoji' => '🥉', 'titulo' => '3º lugar', 'cor' => 'text-orange-700', 'bg' => 'bg-orange-100'],
                                ];
                            @endphp

                            @foreach($ordemPodio as $indiceAluno => $configPodio)
                                @php
                                    $alunoPodio = $podioAlunos->get($indiceAluno);
                                @endphp

                                @if($alunoPodio)
                                    <div class="card-podio rounded-3xl border border-[#E3EBE4] bg-[#F8FBF8] p-4 text-center {{ $configPodio['posicao'] === 1 ? 'lg:-mt-5' : '' }}">

                                        <div class="mx-auto w-16 h-16 rounded-3xl {{ $configPodio['bg'] }} flex items-center justify-center text-3xl trofeu-podio mb-3">
                                            {{ $configPodio['emoji'] }}
                                        </div>

                                        <span class="inline-flex px-3 py-1 rounded-full {{ $configPodio['bg'] }} {{ $configPodio['cor'] }} text-xs font-extrabold mb-3">
                                            {{ $configPodio['titulo'] }}
                                        </span>

                                        <div class="w-14 h-14 rounded-2xl bg-[#004D3A] text-white flex items-center justify-center font-extrabold text-xl mx-auto mb-3">
                                            {{ $alunoPodio->inicial }}
                                        </div>

                                        <h3 class="font-extrabold text-[#003C2F] text-base leading-tight min-h-[42px]">
                                            {{ $alunoPodio->name }}
                                        </h3>

                                        <p class="text-xs text-[#60756B] mt-1 break-all">
                                            {{ $alunoPodio->email }}
                                        </p>

                                        <div class="grid grid-cols-3 gap-2 mt-4 text-center">
                                            <div class="bg-white border border-[#DCE7DE] rounded-2xl p-2">
                                                <p class="text-[9px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                                    Progresso
                                                </p>
                                                <p class="text-sm font-extrabold text-[#003C2F]">
                                                    {{ $alunoPodio->progresso }}%
                                                </p>
                                            </div>

                                            <div class="bg-white border border-[#DCE7DE] rounded-2xl p-2">
                                                <p class="text-[9px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                                    Média
                                                </p>
                                                <p class="text-sm font-extrabold text-blue-600">
                                                    {{ $formatarPercentualProfessor($alunoPodio->media) }}
                                                </p>
                                            </div>

                                            <div class="bg-white border border-[#DCE7DE] rounded-2xl p-2">
                                                <p class="text-[9px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                                    Final
                                                </p>
                                                <p class="text-sm font-extrabold {{ ($alunoPodio->nota_final ?? 0) >= 70 ? 'text-green-700' : 'text-red-600' }}">
                                                    {{ $alunoPodio->nota_final === null ? '-' : $formatarPercentualProfessor($alunoPodio->nota_final) }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="mt-4">
                                            <div class="h-2 bg-[#DCE7DE] rounded-full overflow-hidden">
                                                <div class="h-full bg-[#00A63E] rounded-full" style="width: {{ min(100, $alunoPodio->pontuacao) }}%;"></div>
                                            </div>

                                            <p class="text-xs text-[#60756B] mt-2">
                                                Pontuação do ranking:
                                                <strong class="text-[#004D3A]">{{ $formatarPercentualProfessor($alunoPodio->pontuacao) }}</strong>
                                            </p>
                                        </div>

                                        <div class="base-podio {{ $configPodio['altura'] }} rounded-2xl mt-4 flex items-center justify-center">
                                            <span class="text-4xl font-black text-[#004D3A]">
                                                {{ $configPodio['posicao'] }}
                                            </span>
                                        </div>
                                    </div>
                                @else
                                    <div class="hidden lg:block"></div>
                                @endif
                            @endforeach

                        </div>

                        <div class="mt-6 bg-[#F8FBF8] border border-[#E3EBE4] rounded-3xl p-4">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div>
                                    <h3 class="font-extrabold text-[#003C2F]">
                                        Como o ranking é calculado?
                                    </h3>
                                    <p class="text-xs text-[#60756B] mt-1">
                                        45% progresso + 35% média dos pós-testes + 20% prova final.
                                    </p>
                                </div>

                                <div class="text-xs text-[#60756B]">
                                    Pós-testes repetidos contam apenas uma vez.
                                </div>
                            </div>
                        </div>

                    @else

                        <div class="bg-[#F8FBF8] border border-dashed border-[#DCE7DE] rounded-3xl p-8 text-center">
                            <div class="w-16 h-16 rounded-full bg-[#EAF5EF] flex items-center justify-center mx-auto mb-4 text-3xl">
                                🏆
                            </div>

                            <h3 class="font-extrabold text-[#003C2F]">
                                Ainda não há alunos no ranking
                            </h3>

                            <p class="text-sm text-[#60756B] mt-2">
                                Quando os alunos começarem a assistir aulas e fazer pós-testes, o pódio será preenchido automaticamente.
                            </p>
                        </div>

                    @endif

                </div>

                <!-- SOLICITAÇÕES PENDENTES -->
                <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-[#E3EBE4]">

                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h2 class="font-extrabold text-[#003C2F] text-lg">
                                Acessos Pendentes
                            </h2>
                            <p class="text-xs text-[#60756B]">
                                Solicitações aguardando aprovação.
                            </p>
                        </div>

                        <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-bold">
                            {{ $totalPendentes }}
                        </span>
                    </div>

                    @if($usuariosPendentes->count() > 0)
                        <div class="space-y-4">

                            @foreach($usuariosPendentes as $index => $user)
                                <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-4 {{ $index >= 3 ? 'hidden extra-user' : '' }}">

                                    <div class="flex items-start gap-3 mb-4">
                                        <div class="w-10 h-10 rounded-xl bg-[#9DB7A4] text-white flex items-center justify-center font-bold">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>

                                        <div class="min-w-0">
                                            <p class="font-bold text-[#003C2F] break-words">
                                                {{ $user->name }}
                                            </p>
                                            <p class="text-xs text-[#60756B] break-words">
                                                {{ $user->email }}
                                            </p>
                                            <p class="text-xs text-[#60756B] mt-1">
                                                CPF: {{ $user->cpf }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex flex-col sm:flex-row gap-2">
                                        <form method="POST" action="{{ route('usuario.aprovar', $user->id) }}" class="w-full">
                                            @csrf
                                            <button class="w-full bg-[#00A63E] hover:bg-[#008F35] text-white px-3 py-2 rounded-xl text-sm font-bold transition">
                                                Aprovar
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('usuario.rejeitar', $user->id) }}" class="w-full">
                                            @csrf
                                            <button class="w-full bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 px-3 py-2 rounded-xl text-sm font-bold transition">
                                                Rejeitar
                                            </button>
                                        </form>
                                    </div>

                                </div>
                            @endforeach

                        </div>

                        @if($usuariosPendentes->count() > 3)
                            <button onclick="toggleUsuarios()" id="btnVerMais"
                                    class="mt-4 w-full border border-[#AFC5B5] text-[#004D3A] rounded-2xl py-3 text-sm font-bold hover:bg-[#F1F6F2] transition">
                                Ver mais
                            </button>
                        @endif
                    @else
                        <div class="text-center bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-6">
                            <div class="w-14 h-14 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-7 h-7 text-green-700"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="1.8"
                                          d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                                </svg>
                            </div>

                            <h3 class="font-bold text-[#003C2F]">
                                Tudo em dia
                            </h3>

                            <p class="text-sm text-[#60756B] mt-1">
                                Nenhum usuário aguardando aprovação no momento.
                            </p>
                        </div>
                    @endif

                    <!-- BOTÃO DO HISTÓRICO -->
                    <button onclick="toggleHistoricoAprovados()"
                            id="btnHistoricoAprovados"
                            class="mt-5 w-full bg-[#F8FBF8] border border-[#DCE7DE] text-[#004D3A] rounded-2xl py-3 text-sm font-bold hover:bg-[#F1F6F2] transition flex items-center justify-center gap-2">

                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="w-5 h-5 text-[#00A63E]"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="1.8"
                                  d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                        </svg>

                        <span id="textoBtnHistorico">Ver histórico de aprovados</span>

                        <svg id="setaHistorico"
                             xmlns="http://www.w3.org/2000/svg"
                             class="w-4 h-4 icone-seta-historico"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                        </svg>
                    </button>

                    <!-- HISTÓRICO EM CASCATA -->
                    <div id="historicoAprovados" class="historico-cascata">

                        <div class="mt-4 bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-4">

                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="font-extrabold text-[#003C2F] text-base">
                                        Últimos Aprovados
                                    </h3>
                                    <p class="text-xs text-[#60756B]">
                                        Histórico recente de acessos liberados.
                                    </p>
                                </div>

                                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold">
                                    {{ $totalAprovadosHistorico }}
                                </span>
                            </div>

                            @if($usuariosAprovadosRecentes->count() > 0)

                                <div class="space-y-3">

                                    @foreach($usuariosAprovadosRecentes as $aprovado)
                                        <div class="bg-white border border-[#E3EBE4] rounded-2xl p-3 hover:bg-[#F8FBF8] transition">

                                            <div class="flex items-start gap-3">

                                                <div class="w-10 h-10 rounded-xl bg-green-600 text-white flex items-center justify-center font-bold shrink-0">
                                                    {{ strtoupper(substr($aprovado->name, 0, 1)) }}
                                                </div>

                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-start justify-between gap-2">
                                                        <div class="min-w-0">
                                                            <p class="font-bold text-[#003C2F] text-sm break-words">
                                                                {{ $aprovado->name }}
                                                            </p>

                                                            <p class="text-xs text-[#60756B] break-words">
                                                                {{ $aprovado->email }}
                                                            </p>
                                                        </div>

                                                        <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 px-2 py-1 rounded-full text-[10px] font-bold whitespace-nowrap">
                                                            <span class="w-1.5 h-1.5 bg-green-600 rounded-full"></span>
                                                            OK
                                                        </span>
                                                    </div>

                                                    <div class="mt-2 flex flex-wrap items-center gap-2">
                                                        <span class="inline-flex bg-[#EAF5EF] text-[#004D3A] px-2.5 py-1 rounded-full text-[10px] font-bold uppercase">
                                                            {{ $aprovado->tipo ?? 'usuário' }}
                                                        </span>

                                                        <span class="text-[11px] text-[#8A9B92]">
                                                            {{ $aprovado->updated_at ? Carbon::parse($aprovado->updated_at)->locale('pt_BR')->diffForHumans() : '-' }}
                                                        </span>
                                                    </div>
                                                </div>

                                            </div>

                                        </div>
                                    @endforeach

                                </div>

                                <a href="{{ route('controle.usuarios') }}"
                                   class="mt-4 w-full inline-flex items-center justify-center gap-2 border border-[#AFC5B5] text-[#004D3A] rounded-2xl py-3 text-sm font-bold hover:bg-white transition">
                                    Ver todos os usuários
                                </a>

                            @else

                                <div class="text-center bg-white border border-[#E3EBE4] rounded-2xl p-5">
                                    <div class="w-12 h-12 rounded-full bg-[#EAF5EF] flex items-center justify-center mx-auto mb-3">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="w-6 h-6 text-[#004D3A]"
                                             fill="none"
                                             viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="1.8"
                                                  d="M18 18.72a8.94 8.94 0 0 0-6-2.22 8.94 8.94 0 0 0-6 2.22M15 11.25a3 3 0 1 0-6 0 3 3 0 0 0 6 0z"/>
                                        </svg>
                                    </div>

                                    <h3 class="font-bold text-[#003C2F]">
                                        Nenhum acesso aprovado ainda
                                    </h3>

                                    <p class="text-sm text-[#60756B] mt-1">
                                        Quando o administrador aprovar usuários, eles aparecerão aqui.
                                    </p>
                                </div>

                            @endif

                        </div>

                    </div>

                </div>

            </div>

        </section>

    </main>

</div>

<!-- MODAL AVISO -->
<div id="modalAviso" class="fixed inset-0 hidden items-center justify-center z-50"
     style="background: rgba(0,0,0,0.45); backdrop-filter: blur(4px);">

    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden"
         style="max-height: 90vh; overflow-y: auto;">

        <div class="px-5 sm:px-8 pt-8 pb-2 flex items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-[#003C2F]">
                    Gerenciar Avisos Institucionais
                </h2>
                <p class="text-sm text-[#60756B] mt-1">
                    Crie, publique e acompanhe comunicados recentes.
                </p>
            </div>

            <button type="button"
                    onclick="fecharModalAviso()"
                    class="w-10 h-10 rounded-xl bg-[#F1F6F2] text-[#003C2F] flex items-center justify-center hover:bg-[#E6EFE8] transition">
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="w-5 h-5"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="1.8"
                          d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="px-5 sm:px-8 pb-8 pt-4">

            <form method="POST" id="formAviso" action="{{ route('avisos.store') }}">
                @csrf
                <input type="hidden" name="_method" id="methodAviso" value="POST">

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-[#60756B] uppercase tracking-wider mb-1.5">
                            Título do Aviso
                        </label>

                        <input
                            id="tituloAviso"
                            type="text"
                            name="titulo"
                            placeholder="Ex: Atualização do Protocolo de Triagem"
                            class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-[#F8FBF8] text-[#003C2F] text-sm placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#60756B] uppercase tracking-wider mb-1.5">
                            Categoria
                        </label>

                        <select
                            id="categoriaAviso"
                            name="categoria"
                            class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-[#F8FBF8] text-[#003C2F] text-sm focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition">
                            <option value="urgente">Urgente</option>
                            <option value="informativo">Informativo</option>
                        </select>
                    </div>

                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-[#60756B] uppercase tracking-wider mb-1.5">
                        Mensagem / Descrição
                    </label>

                    <textarea
                        id="mensagemAviso"
                        name="mensagem"
                        rows="4"
                        placeholder="Descreva os detalhes do aviso aqui..."
                        class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-[#F8FBF8] text-[#003C2F] text-sm placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition resize-none"
                    ></textarea>
                </div>

                <div class="flex items-center gap-3 mb-6">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="publicar_agora" id="publicarAgora" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer
                            peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#00A63E]
                            peer-checked:bg-[#00A63E]
                            after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                            after:bg-white after:border after:border-gray-300 after:rounded-full
                            after:h-5 after:w-5 after:transition-all
                            peer-checked:after:translate-x-full peer-checked:after:border-white">
                        </div>
                    </label>

                    <span class="text-sm font-bold text-[#003C2F]">Publicar Agora</span>
                </div>

                <!-- HISTÓRICO -->
                <div class="mb-6">
                    <h3 class="text-lg font-extrabold text-[#003C2F] mb-3">Histórico Recente</h3>

                    <div class="border border-[#DCE7DE] rounded-2xl overflow-x-auto">
                        <table class="w-full min-w-[560px] text-sm">
                            <thead>
                                <tr class="bg-[#F8FBF8] border-b border-[#DCE7DE]">
                                    <th class="text-left px-4 py-3 text-xs font-bold text-[#60756B] uppercase tracking-wider">Título</th>
                                    <th class="text-left px-4 py-3 text-xs font-bold text-[#60756B] uppercase tracking-wider">Status</th>
                                    <th class="text-left px-4 py-3 text-xs font-bold text-[#60756B] uppercase tracking-wider">Data</th>
                                    <th class="text-right px-4 py-3 text-xs font-bold text-[#60756B] uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-[#EEF3EF]">
                                @forelse ($avisosRecentes as $aviso)
                                    <tr class="hover:bg-[#F8FBF8] transition">
                                        <td class="px-4 py-3 text-[#003C2F] font-bold">
                                            {{ $aviso->titulo }}
                                        </td>

                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold
                                                @if (($aviso->status ?? 'publicado') === 'publicado') bg-green-100 text-green-700
                                                @else bg-yellow-100 text-yellow-700
                                                @endif">
                                                {{ strtoupper($aviso->status ?? 'PUBLICADO') }}
                                            </span>
                                        </td>

                                        <td class="px-4 py-3 text-[#60756B] text-xs">
                                            {{ $aviso->created_at ? Carbon::parse($aviso->created_at)->locale('pt_BR')->diffForHumans() : '-' }}
                                        </td>

                                        <td class="px-4 py-3">
                                            <div class="flex justify-end gap-2">

                                                <button
                                                    type="button"
                                                    onclick='editarAviso(@json($aviso->id), @json($aviso->titulo), @json($aviso->mensagem ?? $aviso->descricao ?? ""), @json($aviso->categoria))'
                                                    class="p-2 rounded-xl hover:bg-green-100 text-[#60756B] hover:text-[#004D3A] transition"
                                                    title="Editar">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </button>

                                                <form method="POST" action="{{ route('avisos.destroy', $aviso->id) }}" class="inline">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button
                                                        type="submit"
                                                        onclick="return confirm('Deseja excluir este aviso?')"
                                                        class="p-2 rounded-xl hover:bg-red-50 text-[#60756B] hover:text-red-600 transition"
                                                        title="Excluir">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>

                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-[#60756B]">
                                            Nenhum aviso recente encontrado.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row justify-end gap-3">
                    <button
                        type="button"
                        onclick="fecharModalAviso()"
                        class="px-6 py-3 rounded-2xl border border-[#DCE7DE] text-[#60756B] text-sm font-bold hover:bg-[#F8FBF8] transition">
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="px-6 py-3 rounded-2xl bg-[#004D3A] hover:bg-[#003C2F] text-white text-sm font-bold transition shadow-sm">
                        Salvar e Publicar
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<script>
    function toggleUsuarios() {
        const extras = document.querySelectorAll('.extra-user');
        const btn = document.getElementById('btnVerMais');

        if (!extras.length || !btn) return;

        const existeOculto = Array.from(extras).some(el => el.classList.contains('hidden'));

        extras.forEach(el => {
            el.classList.toggle('hidden');
        });

        btn.innerText = existeOculto ? 'Ver menos' : 'Ver mais';
    }

    function toggleHistoricoAprovados() {
        const historico = document.getElementById('historicoAprovados');
        const seta = document.getElementById('setaHistorico');
        const texto = document.getElementById('textoBtnHistorico');

        if (!historico) return;

        const vaiAbrir = !historico.classList.contains('aberto');

        historico.classList.toggle('aberto');

        if (seta) {
            seta.classList.toggle('aberto');
        }

        if (texto) {
            texto.innerText = vaiAbrir ? 'Ocultar histórico de aprovados' : 'Ver histórico de aprovados';
        }
    }

    function abrirModalAviso() {
        const modal = document.getElementById('modalAviso');
        const form = document.getElementById('formAviso');
        const method = document.getElementById('methodAviso');

        if (!modal || !form || !method) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        form.reset();
        form.action = "{{ route('avisos.store') }}";
        method.value = "POST";
    }

    function fecharModalAviso() {
        const modal = document.getElementById('modalAviso');
        const form = document.getElementById('formAviso');
        const method = document.getElementById('methodAviso');

        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        if (form) {
            form.reset();
            form.action = "{{ route('avisos.store') }}";
        }

        if (method) {
            method.value = "POST";
        }
    }

    function editarAviso(id, titulo, mensagem, categoria) {
        abrirModalAviso();

        const tituloInput = document.getElementById('tituloAviso');
        const mensagemInput = document.getElementById('mensagemAviso');
        const categoriaInput = document.getElementById('categoriaAviso');
        const form = document.getElementById('formAviso');
        const method = document.getElementById('methodAviso');

        if (tituloInput) tituloInput.value = titulo ?? '';
        if (mensagemInput) mensagemInput.value = mensagem ?? '';
        if (categoriaInput) categoriaInput.value = categoria ?? 'informativo';

        if (form) form.action = "/avisos/" + id;
        if (method) method.value = "PUT";
    }

    const modalAviso = document.getElementById('modalAviso');

    if (modalAviso) {
        modalAviso.addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalAviso();
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            fecharModalAviso();
        }
    });
</script>

@endsection