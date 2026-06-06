@extends('layout.app')

@section('title', 'Dashboard Aluno')

@section('content')

@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;

    $alunoLogado = auth()->user();
    $alunoId = auth()->id();

    /*
    |--------------------------------------------------------------------------
    | CURSO ATUAL DO ALUNO
    |--------------------------------------------------------------------------
    | Prioridade:
    | 1. Curso vinculado pela matrícula do aluno
    | 2. Curso publicado/ativo mais recente
    | 3. Último curso cadastrado
    |
    | Assim a home do aluno passa a puxar apenas o curso correto do período,
    | e não mistura módulos/aulas de outros cursos.
    */

    $cursoAtual = null;
    $cursoAtualId = null;

    if (Schema::hasTable('matriculas') && Schema::hasTable('cursos')) {
        $cursoMatriculadoId = DB::table('matriculas')
            ->where('aluno_id', $alunoId)
            ->orderBy('id', 'desc')
            ->value('curso_id');

        if ($cursoMatriculadoId) {
            $cursoAtual = DB::table('cursos')
                ->where('id', $cursoMatriculadoId)
                ->first();
        }
    }

    if (!$cursoAtual && Schema::hasTable('cursos')) {
        $queryCurso = DB::table('cursos');

        if (Schema::hasColumn('cursos', 'publicado')) {
            $queryCurso->where('publicado', true);
        } elseif (Schema::hasColumn('cursos', 'ativo')) {
            $queryCurso->where('ativo', true);
        } elseif (Schema::hasColumn('cursos', 'status')) {
            $queryCurso->whereIn('status', ['publicado', 'ativo', 'aprovado']);
        }

        $cursoAtual = $queryCurso
            ->orderBy('id', 'desc')
            ->first();
    }

    if (!$cursoAtual && Schema::hasTable('cursos')) {
        $cursoAtual = DB::table('cursos')
            ->orderBy('id', 'desc')
            ->first();
    }

    $cursoAtualId = $cursoAtual->id ?? null;

    /*
    |--------------------------------------------------------------------------
    | MÓDULOS E AULAS DO CURSO ATUAL
    |--------------------------------------------------------------------------
    */

    $modulos = collect();
    $aulasCurso = collect();

    if ($cursoAtualId && Schema::hasTable('modulos') && Schema::hasTable('aulas')) {
        $modulosQuery = DB::table('modulos')
            ->where('curso_id', $cursoAtualId);

        if (Schema::hasColumn('modulos', 'ordem')) {
            $modulosQuery->orderBy('ordem');
        }

        $modulos = $modulosQuery
            ->orderBy('id')
            ->get();

        foreach ($modulos as $modulo) {
            $aulasModuloQuery = DB::table('aulas')
                ->where('modulo_id', $modulo->id);

            if (Schema::hasColumn('aulas', 'ordem')) {
                $aulasModuloQuery->orderBy('ordem');
            }

            $modulo->aulas = $aulasModuloQuery
                ->orderBy('id')
                ->get();

            foreach ($modulo->aulas as $aula) {
                $aulasCurso->push($aula);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PROGRESSO DO CURSO ATUAL
    |--------------------------------------------------------------------------
    | Cálculo por etapas do curso inteiro:
    | - assistir aula = 1 etapa
    | - concluir pós-teste da aula = 1 etapa
    */

    $totalEtapasCurso = 0;
    $etapasConcluidasCurso = 0;
    $aulasAssistidasGeral = 0;
    $testesPendentesGeral = 0;
    $notasAluno = collect();

    foreach ($aulasCurso as $aula) {
        $avaliacaoId = null;

        if (Schema::hasTable('avaliacoes')) {
            $avaliacaoQuery = DB::table('avaliacoes')
                ->where('aula_id', $aula->id);

            if (Schema::hasColumn('avaliacoes', 'tipo')) {
                $avaliacaoQuery->where(function ($query) {
                    $query->where('tipo', 'normal')
                          ->orWhere('tipo', 'pos_teste')
                          ->orWhere('tipo', 'pós-teste')
                          ->orWhereNull('tipo');
                });
            }

            $avaliacaoId = $avaliacaoQuery
                ->orderBy('id')
                ->value('id');
        }

        $aulaAssistida = false;

        if (Schema::hasTable('aulas_assistidas')) {
            $aulaAssistidaQuery = DB::table('aulas_assistidas')
                ->where('aluno_id', $alunoId)
                ->where('aula_id', $aula->id);

            if (Schema::hasColumn('aulas_assistidas', 'assistido')) {
                $aulaAssistidaQuery->where('assistido', true);
            }

            $aulaAssistida = $aulaAssistidaQuery->exists();
        }

        $posTesteConcluido = false;

        if ($avaliacaoId && Schema::hasTable('notas')) {
            $notaRegistro = DB::table('notas')
                ->where('aluno_id', $alunoId)
                ->where('avaliacao_id', $avaliacaoId)
                ->orderBy('id', 'desc')
                ->first();

            $posTesteConcluido = (bool) $notaRegistro;

            if ($notaRegistro && isset($notaRegistro->nota)) {
                $notasAluno->push((float) $notaRegistro->nota);
            }
        }

        $totalEtapasCurso++;

        if ($aulaAssistida) {
            $etapasConcluidasCurso++;
            $aulasAssistidasGeral++;
        }

        if ($avaliacaoId) {
            $totalEtapasCurso++;

            if ($posTesteConcluido) {
                $etapasConcluidasCurso++;
            } elseif ($aulaAssistida) {
                $testesPendentesGeral++;
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PROVA FINAL COMO ETAPA DO PROGRESSO
    |--------------------------------------------------------------------------
    | A prova final também faz parte do progresso total do curso.
    | Ela só conta como concluída quando o aluno realiza e alcança pelo menos 70%.
    */
    $provaFinalCurso = null;
    $provaFinalFeita = false;
    $notaFinalPercentual = null;
    $provaFinalAprovada = false;

    if (Schema::hasTable('avaliacoes')) {
        $queryProvaFinal = DB::table('avaliacoes');

        if (Schema::hasColumn('avaliacoes', 'tipo')) {
            $queryProvaFinal->where('tipo', 'final');
        }

        if ($cursoAtualId && Schema::hasColumn('avaliacoes', 'curso_id')) {
            $queryProvaFinal->where('curso_id', $cursoAtualId);
        }

        $provaFinalCurso = $queryProvaFinal
            ->orderByDesc('id')
            ->first();
    }

    if ($provaFinalCurso && Schema::hasTable('notas')) {
        $resultadoProvaFinal = DB::table('notas')
            ->where('aluno_id', $alunoId)
            ->where('avaliacao_id', $provaFinalCurso->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        if ($resultadoProvaFinal) {
            $provaFinalFeita = true;

            foreach (['porcentagem', 'nota', 'pontuacao', 'valor', 'media', 'resultado'] as $colunaNotaFinal) {
                if (isset($resultadoProvaFinal->{$colunaNotaFinal}) && $resultadoProvaFinal->{$colunaNotaFinal} !== null) {
                    $notaFinalPercentual = (float) $resultadoProvaFinal->{$colunaNotaFinal};

                    if ($notaFinalPercentual <= 10) {
                        $notaFinalPercentual *= 10;
                    }

                    $notaFinalPercentual = round($notaFinalPercentual, 2);
                    break;
                }
            }

            $provaFinalAprovada = $notaFinalPercentual !== null && $notaFinalPercentual >= 70;
        }
    }

    // A prova final sempre acrescenta uma etapa ao curso.
    $totalEtapasCurso++;

    if ($provaFinalAprovada) {
        $etapasConcluidasCurso++;
    }

    $totalModulos = $modulos->count();
    $totalAvisos = isset($avisosRecentes) ? $avisosRecentes->count() : 0;
    $totalAulasGeral = $aulasCurso->count();

    $progressoGeral = $totalEtapasCurso > 0
        ? round(($etapasConcluidasCurso / $totalEtapasCurso) * 100)
        : 0;

    /*
    |--------------------------------------------------------------------------
    | MÉDIA DOS PÓS-TESTES
    |--------------------------------------------------------------------------
    | Corrige notas salvas em escala de 0 a 10 para porcentagem.
    */
    $notasAlunoPercentuais = $notasAluno->map(function ($nota) {
        $nota = (float) $nota;
        return $nota <= 10 ? $nota * 10 : $nota;
    });

    $mediaGeral = $notasAlunoPercentuais->count() > 0
        ? round($notasAlunoPercentuais->avg(), 1)
        : (isset($media) ? $media : 0);

    /*
    |--------------------------------------------------------------------------
    | AVISO URGENTE PARA POPUP
    |--------------------------------------------------------------------------
    | O DashboardController já manda apenas avisos ativos e ordenados.
    | Aqui pegamos o primeiro urgente para abrir em popup ao aluno entrar.
    */
    $avisoUrgentePopup = null;

    if (isset($avisosRecentes)) {
        $avisoUrgentePopup = $avisosRecentes
            ->filter(function ($aviso) {
                return strtolower($aviso->categoria ?? $aviso->tipo ?? '') === 'urgente';
            })
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | AULA ATUAL, ÚLTIMA AULA E PRÓXIMA AULA
    |--------------------------------------------------------------------------
    | A aula atual é a primeira pendente dentro da ordem do curso.
    | A última aula é a última que o aluno já concluiu.
    | A próxima aula é a aula seguinte à aula atual, quando existir.
    */

    $aulasLinhaTempo = collect();
    $ultimaAulaConcluida = null;
    $aulaAtualAluno = null;
    $proximaAulaAluno = null;

    foreach ($modulos as $moduloLinha) {
        foreach (($moduloLinha->aulas ?? collect()) as $aulaLinha) {
            $avaliacaoLinhaId = null;

            if (Schema::hasTable('avaliacoes')) {
                $avaliacaoLinhaQuery = DB::table('avaliacoes')
                    ->where('aula_id', $aulaLinha->id);

                if (Schema::hasColumn('avaliacoes', 'tipo')) {
                    $avaliacaoLinhaQuery->where(function ($query) {
                        $query->where('tipo', 'normal')
                              ->orWhere('tipo', 'pos_teste')
                              ->orWhere('tipo', 'pós-teste')
                              ->orWhereNull('tipo');
                    });
                }

                $avaliacaoLinhaId = $avaliacaoLinhaQuery
                    ->orderBy('id')
                    ->value('id');
            }

            $assistidaLinha = false;

            if (Schema::hasTable('aulas_assistidas')) {
                $assistidaLinhaQuery = DB::table('aulas_assistidas')
                    ->where('aluno_id', $alunoId)
                    ->where('aula_id', $aulaLinha->id);

                if (Schema::hasColumn('aulas_assistidas', 'assistido')) {
                    $assistidaLinhaQuery->where('assistido', true);
                }

                $assistidaLinha = $assistidaLinhaQuery->exists();
            }

            $posTesteLinhaConcluido = false;

            if ($avaliacaoLinhaId && Schema::hasTable('notas')) {
                $posTesteLinhaConcluido = DB::table('notas')
                    ->where('aluno_id', $alunoId)
                    ->where('avaliacao_id', $avaliacaoLinhaId)
                    ->exists();
            }

            $aulaLinhaConcluida = $assistidaLinha && (!$avaliacaoLinhaId || $posTesteLinhaConcluido);

            $itemLinha = (object) [
                'id' => $aulaLinha->id,
                'titulo' => $aulaLinha->titulo ?? 'Aula sem título',
                'descricao' => $aulaLinha->descricao ?? '',
                'modulo_id' => $aulaLinha->modulo_id ?? null,
                'modulo_nome' => $moduloLinha->nome ?? 'Módulo',
                'avaliacao_id' => $avaliacaoLinhaId,
                'assistida' => $assistidaLinha,
                'pos_teste_concluido' => $posTesteLinhaConcluido,
                'concluida' => $aulaLinhaConcluida,
            ];

            $aulasLinhaTempo->push($itemLinha);

            if ($aulaLinhaConcluida) {
                $ultimaAulaConcluida = $itemLinha;
            }

            if (!$aulaAtualAluno && !$aulaLinhaConcluida) {
                $aulaAtualAluno = $itemLinha;
            }
        }
    }

    if (!$aulaAtualAluno && $aulasLinhaTempo->count() > 0) {
        $aulaAtualAluno = $aulasLinhaTempo->last();
    }

    if ($aulaAtualAluno) {
        $indiceAtual = $aulasLinhaTempo->search(function ($item) use ($aulaAtualAluno) {
            return (int) $item->id === (int) $aulaAtualAluno->id;
        });

        if ($indiceAtual !== false) {
            $proximaAulaAluno = $aulasLinhaTempo->get($indiceAtual + 1);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CALENDÁRIO DO ALUNO
    |--------------------------------------------------------------------------
    | Visual no mesmo estilo da imagem enviada. Como as aulas ainda não possuem
    | data agendada, o calendário mostra o dia atual e um marcador de atividade.
    | Depois, se você criar uma coluna data_aula ou data_liberacao, dá para
    | colocar aulas diretamente nos dias.
    */

    $mesCalendarioParam = request('mes');

    try {
        $dataCalendario = $mesCalendarioParam
            ? \Carbon\Carbon::createFromFormat('Y-m', $mesCalendarioParam, 'America/Sao_Paulo')->startOfMonth()
            : \Carbon\Carbon::now('America/Sao_Paulo')->startOfMonth();
    } catch (\Throwable $e) {
        $dataCalendario = \Carbon\Carbon::now('America/Sao_Paulo')->startOfMonth();
    }

    $hojeBrasil = \Carbon\Carbon::now('America/Sao_Paulo')->startOfDay();
    $inicioGradeCalendario = $dataCalendario->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
    $semanasCalendario = [];
    $cursorCalendario = $inicioGradeCalendario->copy();

    for ($semana = 0; $semana < 6; $semana++) {
        $diasSemana = [];

        for ($dia = 0; $dia < 7; $dia++) {
            $dataDia = $cursorCalendario->copy();

            $diasSemana[] = [
                'data' => $dataDia,
                'numero' => $dataDia->day,
                'mes_atual' => $dataDia->month === $dataCalendario->month,
                'hoje' => $dataDia->isSameDay($hojeBrasil),
                'tem_atividade' => $dataDia->isSameDay($hojeBrasil) && $aulaAtualAluno,
                'qtd_atividades' => $dataDia->isSameDay($hojeBrasil) && $aulaAtualAluno ? 1 : 0,
            ];

            $cursorCalendario->addDay();
        }

        $semanasCalendario[] = $diasSemana;
    }

    $mesAnteriorCalendario = $dataCalendario->copy()->subMonth()->format('Y-m');
    $proximoMesCalendario = $dataCalendario->copy()->addMonth()->format('Y-m');

    $mesesPtBrCalendario = [
        1 => 'Janeiro',
        2 => 'Fevereiro',
        3 => 'Março',
        4 => 'Abril',
        5 => 'Maio',
        6 => 'Junho',
        7 => 'Julho',
        8 => 'Agosto',
        9 => 'Setembro',
        10 => 'Outubro',
        11 => 'Novembro',
        12 => 'Dezembro',
    ];

    $tituloMesCalendario = ($mesesPtBrCalendario[(int) $dataCalendario->format('n')] ?? $dataCalendario->translatedFormat('F'))
        . ' '
        . $dataCalendario->format('Y');

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

    .calendario-aluno-grade {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 6px;
    }

    .calendario-aluno-dia-nome {
        text-align: center;
        color: #60756B;
        font-weight: 800;
        font-size: 12px;
        letter-spacing: .08em;
        text-transform: uppercase;
        padding: 6px 0 10px;
    }

    .calendario-aluno-dia {
        min-height: 74px;
        border: 2px solid #D9E1DC;
        border-radius: 10px;
        background: #FFFFFF;
        padding: 10px;
        position: relative;
        transition: .2s ease;
    }

    .calendario-aluno-dia:hover {
        border-color: #00A63E;
        box-shadow: 0 8px 22px rgba(0, 77, 58, .08);
        transform: translateY(-1px);
    }

    .calendario-aluno-dia-fora {
        background: #FAFAFA;
        color: #C5CCC8;
        border-color: #E4E8E5;
    }

    .calendario-aluno-dia-hoje {
        border-color: #ff4b00;
        color: #ff4b00;
    }

    .calendario-aluno-bolinha {
        position: absolute;
        left: 10px;
        bottom: 8px;
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        border-radius: 999px;
        background: #7DB7DA;
        color: white;
        font-size: 11px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }


    /*
    |--------------------------------------------------------------------------
    | CALENDÁRIO NO MODO ESCURO
    |--------------------------------------------------------------------------
    */
    html.dark .calendario-aluno-dia-nome {
        color: #CBD5E1 !important;
    }

    html.dark .calendario-aluno-dia {
        background: #0B1220 !important;
        border-color: #334155 !important;
        color: #F8FAFC !important;
    }

    html.dark .calendario-aluno-dia span {
        color: #F8FAFC !important;
    }

    html.dark .calendario-aluno-dia:hover {
        border-color: #00A63E !important;
        box-shadow: 0 8px 22px rgba(0, 166, 62, .16) !important;
    }

    html.dark .calendario-aluno-dia-fora {
        background: #111827 !important;
        border-color: #243044 !important;
        color: #64748B !important;
    }

    html.dark .calendario-aluno-dia-fora span {
        color: #64748B !important;
    }

    html.dark .calendario-aluno-dia-hoje {
        border-color: #FF6B2C !important;
        color: #FFB38A !important;
    }

    html.dark .calendario-aluno-dia-hoje span {
        color: #FFB38A !important;
    }

    html.dark .calendario-aluno-bolinha {
        background: #38BDF8 !important;
        color: #FFFFFF !important;
        box-shadow: 0 0 0 2px rgba(56, 189, 248, .15);
    }

    html.dark .calendario-aluno-mes-titulo {
        color: #F8FAFC !important;
    }

    @media (max-width: 640px) {
        .calendario-aluno-grade {
            gap: 4px;
        }

        .calendario-aluno-dia {
            min-height: 54px;
            padding: 7px;
            border-radius: 8px;
            font-size: 12px;
        }

        .calendario-aluno-dia-nome {
            font-size: 10px;
        }

        .calendario-aluno-bolinha {
            left: 6px;
            bottom: 5px;
            min-width: 16px;
            height: 16px;
            font-size: 9px;
        }
    }

</style>

<div class="flex min-h-screen w-full bg-[#F3F7F3] text-[#003C2F] overflow-x-hidden">

    @include('partials.sidebar-aluno')

    <main class="flex-1 min-w-0 w-full bg-[#F3F7F3] overflow-x-hidden">

        @include('partials.navbar')

        <section class="p-4 sm:p-6 lg:p-8">

            <!-- CABEÇALHO -->
            <div class="mb-7">

                <div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-5">

                    <div>

                        <h1 class="text-3xl sm:text-4xl font-extrabold text-[#003C2F] tracking-tight">
                            Bem-vindo, {{ $alunoLogado->name }}
                        </h1>

                        <p class="text-sm text-[#60756B] mt-2 max-w-2xl">
                            Continue seus estudos, acompanhe seu progresso e veja os comunicados mais recentes da plataforma.
                        </p>

                        @if($cursoAtual)
                            <p class="text-sm text-[#004D3A] mt-2 font-extrabold max-w-2xl">
                                Curso atual: {{ $cursoAtual->nome }}
                            </p>
                        @else
                            <p class="text-sm text-red-600 mt-2 font-extrabold max-w-2xl">
                                Nenhum curso publicado ou vinculado ao aluno.
                            </p>
                        @endif
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">

                        <a href="{{ route('aluno.aulas') }}"
                           class="bg-[#004D3A] hover:bg-[#003C2F] text-white px-5 py-3 rounded-2xl transition flex items-center justify-center gap-2 text-sm font-extrabold shadow-sm">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-5 h-5"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M5.25 5.653c0-1.427 1.54-2.33 2.79-1.637l9.54 5.347c1.26.707 1.26 2.567 0 3.274l-9.54 5.347c-1.25.693-2.79-.21-2.79-1.637V5.653z"/>
                            </svg>

                            Continuar aulas
                        </a>

                        <a href="{{ route('certificado.aluno') }}"
                           class="bg-white border border-[#DCE7DE] text-[#004D3A] px-5 py-3 rounded-2xl hover:bg-[#F8FBF8] transition flex items-center justify-center gap-2 text-sm font-extrabold shadow-sm">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-5 h-5"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                            </svg>

                            Meu certificado
                        </a>

                    </div>

                </div>

            </div>

            <!-- ALERTAS -->
            @if(session('success'))
                <div class="mb-5 bg-green-100 text-green-700 px-4 py-3 rounded-2xl border border-green-200 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-5 bg-red-100 text-red-700 px-4 py-3 rounded-2xl border border-red-200 shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            <!-- RESUMO -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">

                <!-- PROGRESSO -->
                <div class="bg-white rounded-3xl p-5 shadow-sm border border-[#E3EBE4] border-l-4 border-l-[#004D3A] hover:shadow-lg transition">

                    <div class="flex items-start justify-between mb-5">
                        <div class="w-12 h-12 rounded-2xl bg-[#EAF5EF] flex items-center justify-center text-[#004D3A]">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-6 h-6"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125z"/>
                            </svg>
                        </div>

                        <span class="text-[11px] font-bold bg-green-100 text-green-700 px-2.5 py-1 rounded-full">
                            {{ number_format($progressoGeral, 0) }}%
                        </span>
                    </div>

                    <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                        Progresso geral
                    </p>

                    <h3 class="text-3xl font-extrabold mt-2 text-[#003C2F]">
                        {{ number_format($progressoGeral, 0) }}%
                    </h3>

                    <p class="text-xs text-[#60756B] mt-2">
                        Considera videoaulas, pós-testes e aprovação na prova final.
                    </p>

                    <div class="mt-4 h-2 bg-[#E8EFE9] rounded-full overflow-hidden">
                        <div class="h-full bg-[#004D3A] rounded-full"
                             style="width: {{ min(100, max(0, $progressoGeral)) }}%;">
                        </div>
                    </div>
                </div>

                <!-- AULAS -->
                <div class="bg-white rounded-3xl p-5 shadow-sm border border-[#E3EBE4] border-l-4 border-l-[#00A63E] hover:shadow-lg transition">

                    <div class="flex items-start justify-between mb-5">
                        <div class="w-12 h-12 rounded-2xl bg-[#EAF5EF] flex items-center justify-center text-[#00A63E]">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-6 h-6"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M15.75 10.5 19.5 6.75m0 0-3.75-3.75M19.5 6.75H8.25A3.75 3.75 0 0 0 4.5 10.5v0A3.75 3.75 0 0 0 8.25 14.25H15.75"/>
                            </svg>
                        </div>
                    </div>

                    <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                        Aulas assistidas
                    </p>

                    <h3 class="text-3xl font-extrabold mt-2 text-[#003C2F]">
                        {{ $aulasAssistidasGeral }} / {{ $totalAulasGeral }}
                    </h3>

                    <p class="text-xs text-[#60756B] mt-2">
                        Continue assistindo para liberar os próximos testes.
                    </p>
                </div>

                <!-- TESTES -->
                <div class="bg-white rounded-3xl p-5 shadow-sm border border-[#E3EBE4] border-l-4 border-l-yellow-500 hover:shadow-lg transition">

                    <div class="flex items-start justify-between mb-5">
                        <div class="w-12 h-12 rounded-2xl bg-yellow-50 flex items-center justify-center text-yellow-600">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-6 h-6"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M9 12h6m-6 4h6M9 8h6M5 4h14v16H5z"/>
                            </svg>
                        </div>
                    </div>

                    <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                        Testes pendentes
                    </p>

                    <h3 class="text-3xl font-extrabold mt-2 text-yellow-600">
                        {{ $testesPendentesGeral }}
                    </h3>

                    <p class="text-xs text-[#60756B] mt-2">
                        Pós-testes disponíveis após concluir aulas.
                    </p>

                    <p class="text-[11px] font-bold mt-2 {{ $provaFinalAprovada ? 'text-green-700' : 'text-[#60756B]' }}">
                        Prova final: {{ $provaFinalAprovada ? 'aprovada' : ($provaFinalFeita ? 'realizada, aguardando aprovação' : 'pendente') }}
                    </p>
                </div>

                <!-- MÉDIA -->
                <div class="bg-white rounded-3xl p-5 shadow-sm border border-[#E3EBE4] border-l-4 border-l-blue-500 hover:shadow-lg transition">

                    <div class="flex items-start justify-between mb-5">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-6 h-6"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M16.5 18.75h-9m9-3.75h-9m9-3.75h-9m12-5.25H4.5A1.5 1.5 0 0 0 3 7.5v9A1.5 1.5 0 0 0 4.5 18h15a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 19.5 6Z"/>
                            </svg>
                        </div>
                    </div>

                    <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                        Média
                    </p>

                    <h3 class="text-3xl font-extrabold mt-2 text-blue-600">
                        {{ number_format($mediaGeral, 1) }}
                    </h3>

                    <p class="text-xs text-[#60756B] mt-2">
                        Desempenho atual nas avaliações.
                    </p>
                </div>

            </div>


            <!-- CALENDÁRIO E CONTINUIDADE DO CURSO -->
            <div class="grid grid-cols-1 2xl:grid-cols-12 gap-7 mb-8">

                <!-- CALENDÁRIO -->
                <div class="2xl:col-span-7 bg-white border border-[#E3EBE4] rounded-3xl shadow-sm p-5 sm:p-6">

                    <div class="flex items-center justify-between gap-4 mb-5">
                        <h2 class="text-2xl font-extrabold text-[#003C2F]">
                            Calendário
                        </h2>

                        <div class="flex items-center gap-3">
                            <span class="calendario-aluno-mes-titulo font-extrabold text-[#111827]">
                                {{ $tituloMesCalendario }}
                            </span>

                            <a href="{{ request()->fullUrlWithQuery(['mes' => $mesAnteriorCalendario]) }}"
                               class="w-8 h-8 rounded-full hover:bg-[#F1F6F2] text-[#8A9B92] flex items-center justify-center transition"
                               title="Mês anterior">
                                ‹
                            </a>

                            <a href="{{ request()->fullUrlWithQuery(['mes' => $proximoMesCalendario]) }}"
                               class="w-8 h-8 rounded-full hover:bg-[#F1F6F2] text-[#8A9B92] flex items-center justify-center transition"
                               title="Próximo mês">
                                ›
                            </a>
                        </div>
                    </div>

                    <div class="calendario-aluno-grade">
                        <div class="calendario-aluno-dia-nome">DOM</div>
                        <div class="calendario-aluno-dia-nome">SEG</div>
                        <div class="calendario-aluno-dia-nome">TER</div>
                        <div class="calendario-aluno-dia-nome">QUA</div>
                        <div class="calendario-aluno-dia-nome">QUI</div>
                        <div class="calendario-aluno-dia-nome">SEX</div>
                        <div class="calendario-aluno-dia-nome">SÁB</div>

                        @foreach($semanasCalendario as $semanaCalendario)
                            @foreach($semanaCalendario as $diaCalendario)
                                <div class="calendario-aluno-dia
                                    {{ !$diaCalendario['mes_atual'] ? 'calendario-aluno-dia-fora' : '' }}
                                    {{ $diaCalendario['hoje'] ? 'calendario-aluno-dia-hoje' : '' }}">
                                    <span class="font-bold">
                                        {{ $diaCalendario['numero'] }}
                                    </span>

                                    @if($diaCalendario['tem_atividade'])
                                        <span class="calendario-aluno-bolinha">
                                            {{ $diaCalendario['qtd_atividades'] }}
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        @endforeach
                    </div>

                    <div class="mt-5 bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-4">
                        <p class="text-sm font-extrabold text-[#003C2F]">
                            Atividade em destaque no dia de hoje
                        </p>

                        <p class="text-xs text-[#60756B] mt-1 leading-relaxed">
                            @if($aulaAtualAluno)
                                Continue a aula <strong>{{ $aulaAtualAluno->titulo }}</strong>.
                            @else
                                Nenhuma atividade pendente encontrada para este curso.
                            @endif
                        </p>
                    </div>

                </div>

                <!-- CONTINUIDADE -->
                <div class="2xl:col-span-5 space-y-4">

                    <div class="bg-[#004D3A] text-white rounded-3xl p-6 shadow-sm relative overflow-hidden">
                        <div class="absolute -right-10 -top-10 w-36 h-36 rounded-full border-[22px] border-white/10"></div>

                        <div class="relative z-10">
                            <p class="text-[11px] uppercase tracking-widest text-white/70 font-extrabold">
                                Aula atual
                            </p>

                            <h2 class="text-2xl font-extrabold mt-2 leading-tight">
                                {{ $aulaAtualAluno->titulo ?? 'Nenhuma aula pendente' }}
                            </h2>

                            <p class="text-sm text-white/75 mt-2">
                                {{ $aulaAtualAluno ? 'Continue seus estudos a partir desta aula.' : 'Você concluiu ou ainda não possui aulas vinculadas.' }}
                            </p>

                            <a href="{{ route('aluno.aulas') }}"
                               class="mt-5 inline-flex w-full items-center justify-center bg-white text-[#004D3A] px-5 py-3 rounded-2xl font-extrabold hover:bg-[#EAF5EF] transition">
                                Continuar agora
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 2xl:grid-cols-1 gap-4">

                        <div class="bg-white border border-[#E3EBE4] rounded-3xl p-5 shadow-sm">
                            <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                Última aula feita
                            </p>

                            <h3 class="text-lg font-extrabold text-[#003C2F] mt-2 leading-tight">
                                {{ $ultimaAulaConcluida->titulo ?? 'Nenhuma aula concluída ainda' }}
                            </h3>

                            <p class="text-xs text-[#60756B] mt-2">
                                {{ $ultimaAulaConcluida ? 'Última atividade concluída no curso.' : 'Assim que assistir uma aula, ela aparecerá aqui.' }}
                            </p>
                        </div>

                        <div class="bg-white border border-[#E3EBE4] rounded-3xl p-5 shadow-sm">
                            <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                Próxima aula
                            </p>

                            <h3 class="text-lg font-extrabold text-[#003C2F] mt-2 leading-tight">
                                {{ $proximaAulaAluno->titulo ?? 'Sem próxima aula no momento' }}
                            </h3>

                            <p class="text-xs text-[#60756B] mt-2">
                                {{ $proximaAulaAluno ? 'Próxima etapa recomendada para continuar seu avanço.' : 'Continue a aula atual para avançar no curso.' }}
                            </p>
                        </div>

                    </div>

                </div>

            </div>


            <div class="grid grid-cols-1 lg:grid-cols-3 gap-7">


                    <!-- CARD DE AÇÃO -->
                    <div class="bg-[#004D3A] rounded-3xl p-6 shadow-sm text-white relative overflow-hidden">

                        <div class="absolute -right-12 -top-12 w-40 h-40 rounded-full border-[24px] border-white/10"></div>

                        <div class="relative z-10">

                            <div class="w-14 h-14 rounded-2xl bg-white/15 flex items-center justify-center mb-5">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-7 h-7"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="1.8"
                                          d="M12 6v6l4 2"/>
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="1.8"
                                          d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                                </svg>
                            </div>

                            <h2 class="text-2xl font-extrabold leading-tight">
                                Continue de onde parou
                            </h2>

                            <p class="text-sm text-white/75 mt-2 leading-relaxed">
                                Acesse suas aulas e finalize as atividades pendentes para liberar a prova final e o certificado.
                            </p>

                            <a href="{{ route('aluno.aulas') }}"
                               class="mt-6 inline-flex w-full items-center justify-center bg-white text-[#004D3A] px-5 py-3 rounded-2xl font-extrabold hover:bg-[#EAF5EF] transition">
                                Ver minhas aulas
                            </a>

                        </div>

                    </div>

                    <!-- AVISOS -->
                    <div class="bg-white border border-[#E3EBE4] rounded-3xl shadow-sm overflow-hidden">

                        <div class="p-5 border-b border-[#E3EBE4] flex items-center justify-between gap-3">

                            <div>
                                <h2 class="text-xl font-extrabold text-[#003C2F]">
                                    Avisos
                                </h2>

                                <p class="text-xs text-[#60756B] mt-1">
                                    Comunicados ativos da plataforma.
                                </p>
                            </div>

                            <span class="bg-[#EAF5EF] text-[#004D3A] px-3 py-1 rounded-full text-xs font-extrabold">
                                {{ $totalAvisos }}
                            </span>

                        </div>

                        <div class="p-4 space-y-3">

                            @if(isset($avisosRecentes) && $avisosRecentes->count() > 0)

                                @foreach($avisosRecentes as $aviso)

                                    @php
                                        $categoriaAviso = strtolower($aviso->categoria ?? $aviso->tipo ?? 'importante');
                                        $urgente = $categoriaAviso === 'urgente';
                                        $mensagemAviso = $aviso->mensagem ?? $aviso->descricao ?? '';
                                    @endphp

                                    <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-3xl p-4 border-l-4 {{ $urgente ? 'border-l-red-500' : 'border-l-[#004D3A]' }}">

                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-extrabold
                                                {{ $urgente ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                                {{ $urgente ? 'URGENTE' : 'IMPORTANTE' }}
                                            </span>
                                        </div>

                                        <p class="font-extrabold text-[#003C2F] leading-tight">
                                            {{ $aviso->titulo }}
                                        </p>

                                        <p class="text-sm text-[#60756B] mt-2 leading-relaxed">
                                            {{ $mensagemAviso }}
                                        </p>

                                        @if(isset($aviso->expires_at) && $aviso->expires_at)
                                            <p class="text-[11px] text-[#8A9B92] mt-3 font-semibold">
                                                Disponível até {{ \Carbon\Carbon::parse($aviso->expires_at)->format('d/m/Y H:i') }}
                                            </p>
                                        @endif

                                    </div>

                                @endforeach

                            @else

                                <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-3xl p-6 text-center text-[#60756B]">
                                    Nenhum aviso recente.
                                </div>

                            @endif

                        </div>

                    </div>

                    <!-- CERTIFICADO -->
                    <div class="bg-white border border-[#E3EBE4] rounded-3xl shadow-sm p-6">

                        <div class="w-14 h-14 rounded-2xl bg-[#EAF5EF] text-[#004D3A] flex items-center justify-center mb-5">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-7 h-7"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                            </svg>
                        </div>

                        <h2 class="text-xl font-extrabold text-[#003C2F]">
                            Certificado
                        </h2>

                        <p class="text-sm text-[#60756B] mt-2 leading-relaxed">
                            Seu certificado será liberado após concluir todos os requisitos do curso.
                        </p>

                        <a href="{{ route('certificado.aluno') }}"
                           class="mt-5 inline-flex w-full items-center justify-center bg-[#004D3A] hover:bg-[#003C2F] text-white px-5 py-3 rounded-2xl font-extrabold transition">
                            Acessar certificado
                        </a>

                    </div>


            </div>

        </section>

    </main>

</div>


<!-- MODAL AVISO URGENTE -->
@if($avisoUrgentePopup)
    <div id="modalAvisoUrgente"
         class="fixed inset-0 hidden items-center justify-center bg-black/60 backdrop-blur-sm z-[95] px-4">

        <div class="bg-white w-full max-w-xl rounded-3xl shadow-2xl border border-red-100 overflow-hidden">

            <div class="bg-red-600 text-white p-6 relative overflow-hidden">
                <div class="absolute -right-10 -top-10 w-36 h-36 rounded-full border-[24px] border-white/10"></div>
                <div class="absolute right-16 bottom-4 w-16 h-16 rounded-full bg-white/10"></div>

                <div class="relative z-10 flex items-start gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-white/15 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="w-8 h-8"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="1.8"
                                  d="M12 9v3.75m0 3.75h.008v.008H12V16.5zm9-4.5a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                        </svg>
                    </div>

                    <div>
                        <p class="text-xs uppercase tracking-widest font-extrabold text-white/80">
                            Aviso urgente
                        </p>

                        <h2 class="text-2xl font-extrabold mt-1 leading-tight">
                            {{ $avisoUrgentePopup->titulo }}
                        </h2>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <p class="text-[#60756B] leading-relaxed">
                    {{ $avisoUrgentePopup->mensagem ?? $avisoUrgentePopup->descricao ?? '' }}
                </p>

                @if(isset($avisoUrgentePopup->expires_at) && $avisoUrgentePopup->expires_at)
                    <div class="mt-5 bg-red-50 border border-red-100 text-red-700 rounded-2xl p-4 text-sm">
                        Este aviso ficará disponível até
                        <strong>{{ \Carbon\Carbon::parse($avisoUrgentePopup->expires_at)->format('d/m/Y H:i') }}</strong>.
                    </div>
                @endif

                <div class="mt-6 flex flex-col sm:flex-row gap-3">
                    <button type="button"
                            onclick="fecharAvisoUrgente()"
                            class="w-full bg-red-600 hover:bg-red-700 text-white px-5 py-3 rounded-2xl font-extrabold transition">
                        Entendi
                    </button>

                    <button type="button"
                            onclick="fecharAvisoUrgenteHoje()"
                            class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-3 rounded-2xl font-bold transition">
                        Não mostrar novamente
                    </button>
                </div>
            </div>

        </div>

    </div>
@endif

<!-- MODAL VIDEO -->
<div id="modalVideo"
     class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-[90] px-4">

    <div class="bg-white w-full max-w-4xl rounded-3xl p-4 sm:p-5 relative border border-[#E3EBE4] shadow-2xl">

        <div class="flex items-center justify-between mb-4">

            <div>
                <h2 class="text-xl font-extrabold text-[#003C2F]">
                    Videoaula
                </h2>

                <p class="text-xs text-[#60756B] mt-1">
                    Assista à aula e marque como concluída.
                </p>
            </div>

            <button onclick="fecharModal()"
                    class="w-10 h-10 rounded-xl bg-[#F1F6F2] hover:bg-[#E6EFE8] text-[#003C2F] flex items-center justify-center transition">
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

        <iframe
            id="videoFrame"
            class="w-full h-[240px] sm:h-[420px] rounded-2xl bg-black"
            src=""
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen>
        </iframe>

        <div class="mt-4 flex flex-col sm:flex-row sm:justify-between gap-3">
            <button onclick="fecharModal()"
                    class="bg-[#F1F6F2] hover:bg-[#E6EFE8] text-[#60756B] px-5 py-3 rounded-2xl font-bold transition">
                Fechar
            </button>

            <button onclick="marcarAssistida()"
                    id="btnConcluirAulaVideo"
                    class="bg-[#004D3A] hover:bg-[#003C2F] text-white px-5 py-3 rounded-2xl font-extrabold transition">
                Concluir aula
            </button>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let aulaIdAtual = null;
let avaliacaoIdAtual = null;
let tempoInicioVideoAtual = null;
let tempoMinimoVideoAtual = 0;
let tempoMaximoVideoAtual = 0;
let intervaloTempoVideoAtual = null;

/*
|--------------------------------------------------------------------------
| POPUP DE AVISO URGENTE
|--------------------------------------------------------------------------
*/
document.addEventListener('DOMContentLoaded', function () {
    const modalAviso = document.getElementById('modalAvisoUrgente');

    if (!modalAviso) return;

    const avisoId = "{{ $avisoUrgentePopup->id ?? '' }}";
    const alunoId = "{{ auth()->id() }}";
    const chave = 'aviso_urgente_lido_' + alunoId + '_' + avisoId;

    if (!localStorage.getItem(chave)) {
        setTimeout(() => {
            modalAviso.classList.remove('hidden');
            modalAviso.classList.add('flex');
        }, 500);
    }
});

function fecharAvisoUrgente() {
    const modalAviso = document.getElementById('modalAvisoUrgente');

    if (!modalAviso) return;

    modalAviso.classList.add('hidden');
    modalAviso.classList.remove('flex');
}

function fecharAvisoUrgenteHoje() {
    const modalAviso = document.getElementById('modalAvisoUrgente');

    if (!modalAviso) return;

    const avisoId = "{{ $avisoUrgentePopup->id ?? '' }}";
    const alunoId = "{{ auth()->id() }}";
    const chave = 'aviso_urgente_lido_' + alunoId + '_' + avisoId;

    localStorage.setItem(chave, '1');

    modalAviso.classList.add('hidden');
    modalAviso.classList.remove('flex');
}

/*
|--------------------------------------------------------------------------
| VIDEOAULAS E PÓS-TESTE
|--------------------------------------------------------------------------
*/
function normalizarUrlYoutube(url) {
    if (!url) return '';

    let video = String(url).trim();

    if (video.includes('watch?v=')) {
        video = video.replace('watch?v=', 'embed/');
    }

    if (video.includes('youtu.be/')) {
        video = video.replace('youtu.be/', 'www.youtube.com/embed/');
    }

    return video;
}

function abrirModal(url, aulaId, avaliacaoId = null, tempoMinimoVideo = 0, tempoMaximoVideo = 0) {
    aulaIdAtual = aulaId;
    avaliacaoIdAtual = avaliacaoId && avaliacaoId !== 'null' && avaliacaoId !== '' ? avaliacaoId : null;
    tempoInicioVideoAtual = Date.now();
    tempoMinimoVideoAtual = parseInt(tempoMinimoVideo || 0) * 60;
    tempoMaximoVideoAtual = parseInt(tempoMaximoVideo || 0) * 60;

    const video = normalizarUrlYoutube(url);

    if (!video) {
        Swal.fire({
            icon: 'error',
            title: 'Vídeo não encontrado',
            text: 'Esta aula não possui link de vídeo cadastrado.',
            confirmButtonColor: '#004D3A'
        });
        return;
    }

    const modal = document.getElementById('modalVideo');
    const frame = document.getElementById('videoFrame');

    frame.src = video;
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    iniciarContadorTempoVideo();
}

function fecharModal() {
    const modal = document.getElementById('modalVideo');
    const frame = document.getElementById('videoFrame');

    modal.classList.add('hidden');
    modal.classList.remove('flex');
    frame.src = "";

    if (intervaloTempoVideoAtual) {
        clearInterval(intervaloTempoVideoAtual);
        intervaloTempoVideoAtual = null;
    }
}

function fazerPosTeste(avaliacaoId) {
    if (!avaliacaoId || avaliacaoId === 'null') {
        Swal.fire({
            icon: 'info',
            title: 'Sem pós-teste',
            text: 'Esta aula ainda não possui pós-teste cadastrado.',
            confirmButtonColor: '#004D3A'
        });
        return;
    }

    window.location.href = '/avaliacoes/' + avaliacaoId;
}

function tempoAssistidoVideoSegundos() {
    if (!tempoInicioVideoAtual) return 0;

    return Math.floor((Date.now() - tempoInicioVideoAtual) / 1000);
}

function formatarTempoVideo(segundos) {
    segundos = Math.max(0, parseInt(segundos || 0));

    const minutos = Math.floor(segundos / 60);
    const resto = segundos % 60;

    return String(minutos).padStart(2, '0') + ':' + String(resto).padStart(2, '0');
}

function iniciarContadorTempoVideo() {
    const botao = document.getElementById('btnConcluirAulaVideo');

    if (intervaloTempoVideoAtual) {
        clearInterval(intervaloTempoVideoAtual);
    }

    intervaloTempoVideoAtual = setInterval(() => {
        if (!botao) return;

        const assistido = tempoAssistidoVideoSegundos();

        if (tempoMinimoVideoAtual > 0 && assistido < tempoMinimoVideoAtual) {
            botao.innerText = 'Concluir aula - aguarde ' + formatarTempoVideo(tempoMinimoVideoAtual - assistido);
        } else {
            botao.innerText = 'Concluir aula';
        }

        if (tempoMaximoVideoAtual > 0 && assistido >= tempoMaximoVideoAtual) {
            marcarAssistida();
        }
    }, 1000);
}

function marcarAssistida() {
    if (!aulaIdAtual) return;

    const tempoAssistido = tempoAssistidoVideoSegundos();

    if (tempoMinimoVideoAtual > 0 && tempoAssistido < tempoMinimoVideoAtual) {
        Swal.fire({
            icon: 'warning',
            title: 'Tempo mínimo não atingido',
            text: 'Você precisa assistir pelo menos ' + formatarTempoVideo(tempoMinimoVideoAtual) + ' desta videoaula. Ainda falta ' + formatarTempoVideo(tempoMinimoVideoAtual - tempoAssistido) + '.',
            confirmButtonColor: '#004D3A'
        });
        return;
    }

    fetch('/assistir-aula/' + aulaIdAtual + '?tempo_assistido_segundos=' + tempoAssistido, {
        headers: {
            'Accept': 'application/json'
        }
    })
        .then(async response => {
            const data = await response.json().catch(() => ({}));

            if (!response.ok || data.success === false) {
                throw new Error(data.message || 'Erro ao marcar aula como assistida.');
            }

            return data;
        })
        .then(() => {
            fecharModal();

            if (avaliacaoIdAtual) {
                Swal.fire({
                    icon: 'success',
                    title: 'Aula concluída!',
                    text: 'Você liberou o pós-teste. Deseja fazer agora?',
                    showCancelButton: true,
                    confirmButtonText: 'Sim, fazer agora',
                    cancelButtonText: 'Depois',
                    confirmButtonColor: '#004D3A',
                    cancelButtonColor: '#64748b'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fazerPosTeste(avaliacaoIdAtual);
                    } else {
                        location.reload();
                    }
                });
            } else {
                Swal.fire({
                    icon: 'success',
                    title: 'Aula concluída!',
                    text: 'Não há pós-teste cadastrado para esta aula.',
                    confirmButtonColor: '#004D3A'
                }).then(() => {
                    location.reload();
                });
            }
        })
        .catch((error) => {
            Swal.fire({
                icon: 'error',
                title: 'Aula ainda não liberada',
                text: error.message || 'Não foi possível concluir a aula. Tente novamente.',
                confirmButtonColor: '#dc2626'
            });
        });
}

function verResultadoPosTeste(avaliacaoId) {
    if (!avaliacaoId || avaliacaoId === 'null') {
        Swal.fire({
            icon: 'info',
            title: 'Sem pós-teste',
            text: 'Esta aula ainda não possui pós-teste cadastrado.',
            confirmButtonColor: '#004D3A'
        });
        return;
    }

    fetch('/avaliacoes/' + avaliacaoId + '/resultado')
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: data.message || 'Não foi possível carregar o resultado.',
                    confirmButtonColor: '#dc2626'
                });
                return;
            }

            let html = `
                <div id="conteudoResultadoPDF" style="text-align:left; font-family: Arial, sans-serif;">
                    <div style="background:#f1f5f9; border-radius:14px; padding:16px; margin-bottom:16px;">
                        <h2 style="margin:0; color:#0f172a; font-size:20px;">
                            ${data.avaliacao.titulo || 'Pós-teste'}
                        </h2>
                        <p style="margin:8px 0 0; color:#475569;">
                            Nota: <strong>${data.nota !== null ? Number(data.nota).toFixed(1) : 'Não registrada'}</strong>
                        </p>
                    </div>
            `;

            data.perguntas.forEach((pergunta, index) => {
                html += `
                    <div style="border:1px solid #e2e8f0; border-radius:14px; padding:14px; margin-bottom:14px;">
                        <p style="font-weight:bold; color:#0f172a; margin-bottom:10px;">
                            ${index + 1}. ${pergunta.pergunta}
                        </p>
                `;

                pergunta.respostas.forEach(resposta => {
                    const correta = resposta.correta === true || resposta.correta === 1 || resposta.correta === '1';
                    const marcada = Number(pergunta.resposta_aluno_id) === Number(resposta.id);

                    let fundo = '#ffffff';
                    let borda = '#e2e8f0';
                    let extra = '';

                    if (correta) {
                        fundo = '#dcfce7';
                        borda = '#22c55e';
                        extra += ' ✅ Correta';
                    }

                    if (marcada && !correta) {
                        fundo = '#fee2e2';
                        borda = '#ef4444';
                        extra += ' ❌ Sua resposta';
                    }

                    if (marcada && correta) {
                        extra += ' — Sua resposta';
                    }

                    html += `
                        <div style="background:${fundo}; border:1px solid ${borda}; border-radius:10px; padding:10px; margin-bottom:8px; color:#334155;">
                            ${resposta.resposta}
                            <strong style="color:#0f172a;">${extra}</strong>
                        </div>
                    `;
                });

                if (!pergunta.resposta_aluno_id) {
                    html += `
                        <p style="color:#dc2626; font-size:13px; margin-top:8px;">
                            Resposta do aluno não registrada para esta pergunta.
                        </p>
                    `;
                }

                html += `</div>`;
            });

            html += `</div>`;

            Swal.fire({
                title: 'Resultado do pós-teste',
                html: html,
                width: 850,
                showCancelButton: true,
                confirmButtonText: 'Gerar PDF',
                cancelButtonText: 'Fechar',
                confirmButtonColor: '#004D3A',
                cancelButtonColor: '#64748b'
            }).then((result) => {
                if (result.isConfirmed) {
                    gerarPDFResultado();
                }
            });
        })
        .catch(() => {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Não foi possível carregar o resultado do pós-teste.',
                confirmButtonColor: '#dc2626'
            });
        });
}

function gerarPDFResultado() {
    const conteudo = document.getElementById('conteudoResultadoPDF');

    if (!conteudo) {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Não foi possível gerar o PDF.',
            confirmButtonColor: '#dc2626'
        });
        return;
    }

    const janela = window.open('', '_blank');

    janela.document.write(`
        <html>
            <head>
                <title>Resultado do Pós-teste</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        padding: 30px;
                        color: #0f172a;
                    }

                    h1, h2, h3 {
                        color: #0f172a;
                    }

                    @media print {
                        button {
                            display: none;
                        }
                    }
                </style>
            </head>
            <body>
                ${conteudo.innerHTML}

                <script>
                    window.onload = function() {
                        window.print();
                    }
                <\/script>
            </body>
        </html>
    `);

    janela.document.close();
}

function toggleModulo(id) {
    const el = document.getElementById('modulo-' + id);

    if (el) {
        el.classList.toggle('hidden');
    }
}
</script>

@endsection