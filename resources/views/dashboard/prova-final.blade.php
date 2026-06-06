@extends('layout.app')

@section('title', 'Prova Final')

@section('content')

@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;

    $alunoId = auth()->id();

    /*
    |--------------------------------------------------------------------------
    | CURSO ATUAL DO ALUNO
    |--------------------------------------------------------------------------
    | A prova final deve considerar somente o curso atual do aluno.
    | Prioridade:
    | 1. Curso vinculado na tabela matriculas
    | 2. Curso publicado/ativo mais recente
    | 3. Último curso cadastrado
    */

    $cursoAtual = null;

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

    $cursoAtualId = $cursoAtual->id ?? null;

    /*
    |--------------------------------------------------------------------------
    | AULAS E PÓS-TESTES DO CURSO ATUAL
    |--------------------------------------------------------------------------
    */

    $modulosCursoIds = collect();
    $aulasCursoIds = collect();

    if ($cursoAtualId && Schema::hasTable('modulos')) {
        $modulosCursoIds = DB::table('modulos')
            ->where('curso_id', $cursoAtualId)
            ->pluck('id');
    }

    if ($modulosCursoIds->count() > 0 && Schema::hasTable('aulas')) {
        $aulasCursoIds = DB::table('aulas')
            ->whereIn('modulo_id', $modulosCursoIds)
            ->pluck('id');
    }

    // Caso exista curso_id diretamente na tabela aulas, usa também como segurança.
    if ($cursoAtualId && Schema::hasTable('aulas') && Schema::hasColumn('aulas', 'curso_id')) {
        $aulasPorCursoDireto = DB::table('aulas')
            ->where('curso_id', $cursoAtualId)
            ->pluck('id');

        $aulasCursoIds = $aulasCursoIds
            ->merge($aulasPorCursoDireto)
            ->unique()
            ->values();
    }

    $totalAulas = $aulasCursoIds->count();

    $totalAulasAssistidas = $totalAulas > 0 && Schema::hasTable('aulas_assistidas')
        ? DB::table('aulas_assistidas')
            ->where('aluno_id', $alunoId)
            ->whereIn('aula_id', $aulasCursoIds)
            ->where('assistido', true)
            ->distinct('aula_id')
            ->count('aula_id')
        : 0;

    $avaliacoesPosTesteIds = collect();

    if ($totalAulas > 0 && Schema::hasTable('avaliacoes')) {
        $avaliacoesPosTesteQuery = DB::table('avaliacoes')
            ->whereIn('aula_id', $aulasCursoIds);

        if (Schema::hasColumn('avaliacoes', 'tipo')) {
            $avaliacoesPosTesteQuery->where(function ($query) {
                $query->where('tipo', 'normal')
                      ->orWhere('tipo', 'pos_teste')
                      ->orWhere('tipo', 'pós-teste')
                      ->orWhereNull('tipo');
            });
        }

        $avaliacoesPosTesteIds = $avaliacoesPosTesteQuery->pluck('id');
    }

    $totalPosTestes = $avaliacoesPosTesteIds->count();

    $totalPosTestesFeitos = $totalPosTestes > 0 && Schema::hasTable('notas')
        ? DB::table('notas')
            ->where('aluno_id', $alunoId)
            ->whereIn('avaliacao_id', $avaliacoesPosTesteIds)
            ->distinct('avaliacao_id')
            ->count('avaliacao_id')
        : 0;

    /*
    |--------------------------------------------------------------------------
    | MÉDIA DOS PONTOS DO CURSO
    |--------------------------------------------------------------------------
    | A prova final só será liberada se o aluno concluir todo o curso e tiver
    | média mínima de 70% nos pós-testes/avaliações do curso.
    */
    $mediaPontosCurso = 0;
    $mediaPontosTexto = '0%';

    if ($totalPosTestes > 0 && Schema::hasTable('notas') && $avaliacoesPosTesteIds->count() > 0) {
        $colunaNota = null;

        foreach (['nota', 'pontuacao', 'media', 'resultado'] as $possivelColunaNota) {
            if (Schema::hasColumn('notas', $possivelColunaNota)) {
                $colunaNota = $possivelColunaNota;
                break;
            }
        }

        if ($colunaNota) {
            $notasCurso = DB::table('notas')
                ->where('aluno_id', $alunoId)
                ->whereIn('avaliacao_id', $avaliacoesPosTesteIds)
                ->whereNotNull($colunaNota)
                ->pluck($colunaNota)
                ->map(function ($nota) {
                    $notaNumerica = (float) $nota;

                    // Se a nota estiver no formato 0 a 10, converte para porcentagem.
                    return $notaNumerica <= 10 ? $notaNumerica * 10 : $notaNumerica;
                });

            if ($notasCurso->count() > 0) {
                $mediaPontosCurso = round($notasCurso->avg());
                $mediaPontosTexto = $mediaPontosCurso . '%';
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | REGRA DE LIBERAÇÃO
    |--------------------------------------------------------------------------
    | Agora a prova final libera somente quando:
    | 1. O curso tiver conteúdo cadastrado.
    | 2. O aluno concluir todas as videoaulas.
    | 3. O aluno realizar todos os pós-testes.
    | 4. O aluno tiver média mínima de 70% nos pontos do curso.
    */

    $totalEtapas = $totalAulas + $totalPosTestes;
    $etapasConcluidas = $totalAulasAssistidas + $totalPosTestesFeitos;

    $porcentagemConclusao = $totalEtapas > 0
        ? round(($etapasConcluidas / $totalEtapas) * 100)
        : 0;

    $cursoTemConteudo = $cursoAtual && $totalEtapas > 0;

    $aulasOk = $totalAulas > 0
        ? $totalAulasAssistidas >= $totalAulas
        : false;

    $posTestesOk = $totalPosTestes > 0
        ? $totalPosTestesFeitos >= $totalPosTestes
        : true;

    $mediaOk = $mediaPontosCurso >= 70;

    $cursoCompletoOk = $cursoTemConteudo && $aulasOk && $posTestesOk;

    $provaLiberada = $cursoCompletoOk && $mediaOk;

    $faltamPorcentagem = max(0, 100 - $porcentagemConclusao);
    $faltamMedia = max(0, 70 - $mediaPontosCurso);

    $tentativas = isset($avaliacao) && isset($avaliacao->tentativas)
        ? $avaliacao->tentativas
        : 2;

    $tempoMinimoProva = isset($avaliacao) && Schema::hasColumn('avaliacoes', 'tempo_minimo')
        ? (int) ($avaliacao->tempo_minimo ?? 0)
        : 0;

    $tempoLimiteProva = isset($avaliacao) && isset($avaliacao->tempo_limite)
        ? (int) ($avaliacao->tempo_limite ?? 60)
        : 60;

    /*
    |--------------------------------------------------------------------------
    | RESULTADO DA PROVA FINAL DO ALUNO
    |--------------------------------------------------------------------------
    */
    $resultadoFinalAluno = null;
    $provaFinalJaFeita = false;
    $notaFinalAluno = null;
    $notaFinalAlunoTexto = '-';
    $dataProvaFinalAluno = null;
    $aprovadoProvaFinal = false;

    if (isset($avaliacao) && Schema::hasTable('notas')) {
        $resultadoFinalAluno = DB::table('notas')
            ->where('aluno_id', $alunoId)
            ->where('avaliacao_id', $avaliacao->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        if ($resultadoFinalAluno) {
            $provaFinalJaFeita = true;

            foreach (['porcentagem', 'nota', 'pontuacao', 'valor', 'media', 'resultado'] as $colunaNotaFinal) {
                if (isset($resultadoFinalAluno->{$colunaNotaFinal}) && $resultadoFinalAluno->{$colunaNotaFinal} !== null) {
                    $notaFinalAluno = (float) $resultadoFinalAluno->{$colunaNotaFinal};

                    if ($notaFinalAluno <= 10) {
                        $notaFinalAluno *= 10;
                    }

                    $notaFinalAluno = round($notaFinalAluno, 2);
                    break;
                }
            }

            $notaFinalAlunoTexto = $notaFinalAluno !== null
                ? number_format($notaFinalAluno, 1, ',', '.') . '%'
                : '-';

            $dataProvaFinalAluno = $resultadoFinalAluno->created_at ?? null;
            $aprovadoProvaFinal = $notaFinalAluno !== null && $notaFinalAluno >= 70;
        }
    }

    $modoProvaFinal = isset($avaliacao)
        && $provaLiberada
        && !$provaFinalJaFeita
        && request('iniciar') === '1';

    $requisitosProva = [
        [
            'titulo' => 'Concluir 100% do curso',
            'ok' => $cursoCompletoOk,
            'descricao' => 'Você está com ' . $porcentagemConclusao . '%. Para liberar a prova final, precisa concluir todo o curso.',
        ],
        [
            'titulo' => 'Assistir todas as videoaulas',
            'ok' => $aulasOk,
            'descricao' => $totalAulasAssistidas . ' de ' . $totalAulas . ' aula(s) assistida(s).',
        ],
        [
            'titulo' => 'Realizar todos os pós-testes',
            'ok' => $posTestesOk,
            'descricao' => $totalPosTestesFeitos . ' de ' . $totalPosTestes . ' pós-teste(s) feito(s).',
        ],
        [
            'titulo' => 'Ter média mínima de 70% nos pontos',
            'ok' => $mediaOk,
            'descricao' => 'Sua média atual é ' . $mediaPontosTexto . '. Faltam ' . $faltamMedia . ' ponto(s) percentual(is) para atingir 70%.',
        ],
    ];

    $totalRequisitosProva = count($requisitosProva);
    $requisitosConcluidosProva = collect($requisitosProva)->where('ok', true)->count();
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

    .modo-prova-final-limpo {
        min-height: 100vh;
        background:
            radial-gradient(circle at top left, rgba(0, 166, 62, 0.08), transparent 30%),
            radial-gradient(circle at bottom right, rgba(0, 77, 58, 0.08), transparent 35%),
            #F3F7F3;
    }

    .topo-prova-limpa {
        position: sticky;
        top: 0;
        z-index: 60;
        backdrop-filter: blur(14px);
        background: rgba(243, 247, 243, 0.92);
        border-bottom: 1px solid #DCE7DE;
    }
</style>

<div class="{{ $modoProvaFinal ? 'modo-prova-final-limpo' : 'flex bg-[#F3F7F3]' }} min-h-screen w-full text-[#003C2F] overflow-x-hidden">

    @if(!$modoProvaFinal)
        @include('partials.sidebar-aluno')
    @endif

    <main class="{{ $modoProvaFinal ? 'w-full min-h-screen' : 'flex-1 min-w-0 w-full' }} bg-[#F3F7F3] overflow-x-hidden">

        @if(!$modoProvaFinal)
            @include('partials.navbar')
        @endif

        <section class="{{ $modoProvaFinal ? 'p-4 sm:p-6 lg:p-8 max-w-[1500px] mx-auto' : 'p-4 sm:p-6 lg:p-8' }}">

            @if($modoProvaFinal)
                <div class="topo-prova-limpa -mx-4 sm:-mx-6 lg:-mx-8 -mt-4 sm:-mt-6 lg:-mt-8 mb-7 px-4 sm:px-6 lg:px-8 py-4">
                    <div class="max-w-[1500px] mx-auto flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-white border border-[#DCE7DE] shadow-sm flex items-center justify-center">
                                <span class="text-[#004D3A] font-extrabold">IR</span>
                            </div>

                            <div>
                                <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                    Ambiente seguro de avaliação
                                </p>

                                <h2 class="text-lg sm:text-xl font-extrabold text-[#003C2F]">
                                    Prova Final — Integrar ReSaúde
                                </h2>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 text-xs sm:text-sm font-bold text-[#60756B] bg-white border border-[#DCE7DE] rounded-2xl px-4 py-3 shadow-sm">
                            <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                            Tela focada: sem menus durante a prova
                        </div>
                    </div>
                </div>
            @endif

            <!-- CABEÇALHO -->
            <div class="mb-7 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-5">

                <div>

                    <h1 class="text-3xl sm:text-4xl font-extrabold text-[#003C2F] tracking-tight">
                        Prova Final
                    </h1>

                    <p class="text-sm text-[#60756B] mt-2 max-w-2xl">
                        A avaliação final será liberada quando você concluir 100% do curso atual e atingir média mínima de 70% nos pós-testes.
                    </p>

                    @if($cursoAtual)
                        <p class="text-sm text-[#004D3A] mt-2 font-extrabold">
                            Curso atual: {{ $cursoAtual->nome }}
                        </p>
                    @else
                        <p class="text-sm text-red-600 mt-2 font-extrabold">
                            Nenhum curso atual foi encontrado para este aluno.
                        </p>
                    @endif
                </div>

                @if(isset($avaliacao))
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="bg-white border border-[#E3EBE4] rounded-3xl px-5 py-4 shadow-sm">
                            <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                Tempo mínimo
                            </p>

                            <p class="text-2xl font-extrabold text-[#004D3A] mt-1">
                                {{ $tempoMinimoProva }} min
                            </p>
                        </div>

                        <div class="bg-white border border-[#E3EBE4] rounded-3xl px-5 py-4 shadow-sm">
                            <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                Tempo máximo
                            </p>

                            <p class="text-2xl font-extrabold text-[#004D3A] mt-1">
                                {{ $tempoLimiteProva }} min
                            </p>
                        </div>
                    </div>
                @endif

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

            @if(!isset($avaliacao))

                <!-- SEM PROVA -->
                <div class="bg-white border border-[#E3EBE4] rounded-3xl p-8 shadow-sm text-center">

                    <div class="w-20 h-20 mx-auto rounded-full bg-yellow-100 text-yellow-700 flex items-center justify-center mb-5">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="w-10 h-10"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="1.8"
                                  d="M12 9v3.75m0 3.75h.008v.008H12V16.5zm9-4.5a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                        </svg>
                    </div>

                    <h2 class="text-2xl font-extrabold text-[#003C2F] mb-2">
                        Nenhuma prova disponível
                    </h2>

                    <p class="text-[#60756B] text-sm max-w-md mx-auto">
                        A prova final ainda não foi cadastrada pelo professor. Volte novamente mais tarde.
                    </p>

                    <a href="{{ route('dashboard.aluno') }}"
                       class="inline-flex items-center justify-center mt-6 bg-[#004D3A] text-white px-6 py-3 rounded-2xl font-bold hover:bg-[#003C2F] transition">
                        Voltar ao dashboard
                    </a>

                </div>

            @elseif(!$provaLiberada)

                <!-- PROVA BLOQUEADA -->
                <div class="grid grid-cols-1 xl:grid-cols-12 gap-7">

                    <div class="xl:col-span-8">

                        <div class="bg-white border border-[#E3EBE4] rounded-3xl p-6 sm:p-8 shadow-sm">

                            <div class="w-20 h-20 rounded-full bg-red-50 text-red-600 flex items-center justify-center mb-5">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-10 h-10"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="1.8"
                                          d="M16.5 10.5V6.75a4.5 4.5 0 0 0-9 0v3.75m-.75 11.25h10.5A2.25 2.25 0 0 0 19.5 19.5v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 12.75v6.75a2.25 2.25 0 0 0 2.25 2.25z"/>
                                </svg>
                            </div>

                            <h2 class="text-2xl sm:text-3xl font-extrabold text-[#003C2F] mb-3">
                                Prova final bloqueada
                            </h2>

                            <p class="text-[#60756B] text-sm leading-relaxed max-w-2xl">
                                Você ainda não atingiu os requisitos mínimos para realizar a prova final.
                                Para liberar, é necessário concluir 100% do curso e alcançar média mínima de 70% nos pontos.
                            </p>

                            @if($cursoAtual)
                                <p class="text-sm text-[#004D3A] font-extrabold mt-3">
                                    Curso atual: {{ $cursoAtual->nome }}
                                </p>
                            @endif

                            <div class="mt-6 bg-[#F8FBF8] border border-[#E3EBE4] rounded-3xl p-5">
                                <div class="flex items-center justify-between mb-3">
                                    <p class="text-sm font-extrabold text-[#003C2F]">
                                        Progresso de conclusão do curso
                                    </p>
                                    <p class="text-sm font-extrabold text-[#004D3A]">
                                        {{ $porcentagemConclusao }}%
                                    </p>
                                </div>

                                <div class="w-full h-3 bg-[#E8EFE9] rounded-full overflow-hidden">
                                    <div class="h-full bg-[#004D3A] rounded-full transition-all duration-700"
                                         style="width: {{ min(100, $porcentagemConclusao) }}%;">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-5">
                                    <div class="bg-white rounded-2xl border border-[#E3EBE4] p-4">
                                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                            Progresso atual
                                        </p>
                                        <p class="text-2xl font-extrabold mt-1 {{ $porcentagemConclusao >= 70 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $porcentagemConclusao }}%
                                        </p>
                                        <p class="text-xs text-[#60756B] mt-1">
                                            Necessário: 100% do curso
                                        </p>
                                    </div>

                                    <div class="bg-white rounded-2xl border border-[#E3EBE4] p-4">
                                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                            Falta para concluir
                                        </p>
                                        <p class="text-2xl font-extrabold mt-1 {{ $faltamPorcentagem <= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $faltamPorcentagem }}%
                                        </p>
                                        <p class="text-xs text-[#60756B] mt-1">
                                            Pontos percentuais para concluir 100%.
                                        </p>
                                    </div>

                                    <div class="bg-white rounded-2xl border border-[#E3EBE4] p-4">
                                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                            Videoaulas assistidas
                                        </p>
                                        <p class="text-2xl font-extrabold mt-1 {{ $aulasOk ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $totalAulasAssistidas }} / {{ $totalAulas }}
                                        </p>
                                    </div>

                                    <div class="bg-white rounded-2xl border border-[#E3EBE4] p-4">
                                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                            Pós-testes feitos
                                        </p>
                                        <p class="text-2xl font-extrabold mt-1 {{ $posTestesOk ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $totalPosTestesFeitos }} / {{ $totalPosTestes }}
                                        </p>
                                    </div>

                                    <div class="bg-white rounded-2xl border border-[#E3EBE4] p-4 sm:col-span-2">
                                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                            Média dos pontos
                                        </p>
                                        <p class="text-2xl font-extrabold mt-1 {{ $mediaOk ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $mediaPontosTexto }}
                                        </p>
                                        <p class="text-xs text-[#60756B] mt-1">
                                            Mínimo necessário: 70%.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-3 mt-6">
                                <a href="{{ route('aluno.aulas') }}"
                                   class="inline-flex items-center justify-center bg-[#004D3A] text-white px-6 py-3 rounded-2xl font-bold hover:bg-[#003C2F] transition">
                                    Continuar minhas aulas
                                </a>
                            </div>
                        </div>
                    </div>

                    <aside class="xl:col-span-4">
                        <div class="bg-white border border-[#E3EBE4] rounded-3xl p-6 shadow-sm">
                            <h3 class="text-xl font-extrabold text-[#003C2F] mb-4">
                                Requisitos para fazer a prova
                            </h3>

                            <div class="space-y-4">
                                @foreach($requisitosProva as $requisito)
                                    <div class="flex items-start gap-3">
                                        <div class="w-9 h-9 rounded-xl {{ $requisito['ok'] ? 'bg-green-100 text-green-700' : 'bg-red-50 text-red-600' }} flex items-center justify-center shrink-0 font-bold">
                                            {{ $requisito['ok'] ? '✓' : '!' }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-[#003C2F]">{{ $requisito['titulo'] }}</p>
                                            <p class="text-sm text-[#60756B]">{{ $requisito['descricao'] }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-6 bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-4">
                                <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">Resumo</p>
                                <p class="text-2xl font-extrabold text-[#004D3A] mt-1">{{ $requisitosConcluidosProva }} / {{ $totalRequisitosProva }}</p>
                                <p class="text-xs text-[#60756B] mt-1">requisito(s) em andamento/concluído(s).</p>
                            </div>
                        </div>
                    </aside>
                </div>

            @elseif($provaFinalJaFeita)

                <!-- RESULTADO DA PROVA FINAL -->
                <div class="grid grid-cols-1 xl:grid-cols-12 gap-7">
                    <div class="xl:col-span-8">
                        <div class="bg-white border border-[#E3EBE4] rounded-3xl p-6 sm:p-8 shadow-sm">

                            <div class="w-20 h-20 rounded-full {{ $aprovadoProvaFinal ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} flex items-center justify-center mb-5">
                                @if($aprovadoProvaFinal)
                                    <span class="text-4xl font-black">✓</span>
                                @else
                                    <span class="text-4xl font-black">!</span>
                                @endif
                            </div>

                            <p class="text-[11px] uppercase tracking-widest font-extrabold {{ $aprovadoProvaFinal ? 'text-green-700' : 'text-red-700' }}">
                                Resultado registrado
                            </p>

                            <h2 class="text-2xl sm:text-3xl font-extrabold text-[#003C2F] mt-2">
                                {{ $aprovadoProvaFinal ? 'Você foi aprovado na prova final!' : 'Prova final realizada' }}
                            </h2>

                            <p class="text-[#60756B] text-sm leading-relaxed max-w-2xl mt-3">
                                Sua prova final já foi concluída e o resultado está salvo no sistema.
                            </p>

                            @if($cursoAtual)
                                <p class="text-sm text-[#004D3A] font-extrabold mt-3">
                                    Curso atual: {{ $cursoAtual->nome }}
                                </p>
                            @endif

                            <div class="mt-7 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="rounded-3xl border {{ $aprovadoProvaFinal ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }} p-6">
                                    <p class="text-[11px] uppercase tracking-widest font-extrabold {{ $aprovadoProvaFinal ? 'text-green-700' : 'text-red-700' }}">
                                        Nota da prova final
                                    </p>

                                    <p class="text-4xl font-extrabold mt-2 {{ $aprovadoProvaFinal ? 'text-green-700' : 'text-red-700' }}">
                                        {{ $notaFinalAlunoTexto }}
                                    </p>

                                    <p class="text-xs mt-2 {{ $aprovadoProvaFinal ? 'text-green-700/80' : 'text-red-700/80' }}">
                                        Mínimo necessário: 70%.
                                    </p>
                                </div>

                                <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-3xl p-6">
                                    <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                        Situação
                                    </p>

                                    <p class="text-2xl font-extrabold mt-2 {{ $aprovadoProvaFinal ? 'text-green-700' : 'text-red-600' }}">
                                        {{ $aprovadoProvaFinal ? 'Aprovado' : 'Não aprovado' }}
                                    </p>

                                    <p class="text-xs text-[#60756B] mt-2">
                                        @if($dataProvaFinalAluno)
                                            Realizada em
                                            {{ \Carbon\Carbon::parse($dataProvaFinalAluno)->timezone('America/Sao_Paulo')->format('d/m/Y H:i') }}.
                                        @else
                                            Data não disponível.
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="mt-6 bg-[#F8FBF8] border border-[#E3EBE4] rounded-3xl p-5">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                    <div>
                                        <p class="font-extrabold text-[#003C2F]">
                                            {{ $aprovadoProvaFinal ? 'Etapa final concluída' : 'Resultado abaixo da média' }}
                                        </p>

                                        <p class="text-sm text-[#60756B] mt-1">
                                            @if($aprovadoProvaFinal)
                                                Consulte seu certificado para verificar a liberação.
                                            @else
                                                Consulte o professor sobre uma nova tentativa.
                                            @endif
                                        </p>
                                    </div>

                                    <a href="{{ route('certificado.aluno') }}"
                                       class="inline-flex items-center justify-center bg-[#004D3A] text-white px-5 py-3 rounded-2xl font-extrabold hover:bg-[#003C2F] transition">
                                        Ver certificado
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>

                    <aside class="xl:col-span-4">
                        <div class="bg-white border border-[#E3EBE4] rounded-3xl p-6 shadow-sm">
                            <h3 class="text-xl font-extrabold text-[#003C2F] mb-4">
                                Resumo da avaliação
                            </h3>

                            <div class="space-y-3">
                                <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-4 flex items-center justify-between gap-3">
                                    <span class="text-sm font-bold text-[#60756B]">Nota final</span>
                                    <strong class="{{ $aprovadoProvaFinal ? 'text-green-700' : 'text-red-600' }}">
                                        {{ $notaFinalAlunoTexto }}
                                    </strong>
                                </div>

                                <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-4 flex items-center justify-between gap-3">
                                    <span class="text-sm font-bold text-[#60756B]">Mínimo</span>
                                    <strong class="text-[#004D3A]">70%</strong>
                                </div>

                                <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-4 flex items-center justify-between gap-3">
                                    <span class="text-sm font-bold text-[#60756B]">Status</span>
                                    <strong class="{{ $aprovadoProvaFinal ? 'text-green-700' : 'text-red-600' }}">
                                        {{ $aprovadoProvaFinal ? 'Aprovado' : 'Não aprovado' }}
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>

            @elseif(!$modoProvaFinal)

                <!-- PROVA LIBERADA - TELA DE ENTRADA -->
                <div class="grid grid-cols-1 xl:grid-cols-12 gap-7">
                    <div class="xl:col-span-8">
                        <div class="bg-white border border-[#E3EBE4] rounded-3xl p-6 sm:p-8 shadow-sm">
                            <div class="w-20 h-20 rounded-full bg-green-100 text-green-700 flex items-center justify-center mb-5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                                </svg>
                            </div>

                            <h2 class="text-2xl sm:text-3xl font-extrabold text-[#003C2F] mb-3">Prova final liberada</h2>

                            <p class="text-[#60756B] text-sm leading-relaxed max-w-2xl">
                                Você concluiu todo o curso e atingiu a média mínima necessária para realizar a prova final.
                                Ao iniciar, a prova será aberta em uma tela limpa, sem menus, para evitar distrações e saídas acidentais.
                            </p>

                            @if($cursoAtual)
                                <p class="text-sm text-[#004D3A] font-extrabold mt-3">Curso atual: {{ $cursoAtual->nome }}</p>
                            @endif

                            <div class="mt-6 bg-[#F8FBF8] border border-[#E3EBE4] rounded-3xl p-5">
                                <div class="flex items-center justify-between mb-3">
                                    <p class="text-sm font-extrabold text-[#003C2F]">Progresso do curso</p>
                                    <p class="text-sm font-extrabold text-[#004D3A]">{{ $porcentagemConclusao }}%</p>
                                </div>

                                <div class="w-full h-3 bg-[#E8EFE9] rounded-full overflow-hidden">
                                    <div class="h-full bg-[#004D3A] rounded-full transition-all duration-700" style="width: {{ min(100, $porcentagemConclusao) }}%;"></div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-5">
                                    <div class="bg-white rounded-2xl border border-[#E3EBE4] p-4">
                                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">Tempo mínimo</p>
                                        <p class="text-2xl font-extrabold text-[#004D3A] mt-1">{{ $tempoMinimoProva }} min</p>
                                    </div>

                                    <div class="bg-white rounded-2xl border border-[#E3EBE4] p-4">
                                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">Tempo máximo</p>
                                        <p class="text-2xl font-extrabold text-[#004D3A] mt-1">{{ $tempoLimiteProva }} min</p>
                                    </div>

                                    <div class="bg-white rounded-2xl border border-[#E3EBE4] p-4">
                                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">Questões</p>
                                        <p class="text-2xl font-extrabold text-[#004D3A] mt-1">{{ $avaliacao->perguntas->count() }}</p>
                                    </div>

                                    <div class="bg-white rounded-2xl border border-[#E3EBE4] p-4">
                                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">Tentativas</p>
                                        <p class="text-2xl font-extrabold text-[#004D3A] mt-1">{{ $tentativas }}</p>
                                    </div>

                                    <div class="bg-white rounded-2xl border border-[#E3EBE4] p-4 sm:col-span-2">
                                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">Média dos pontos</p>
                                        <p class="text-2xl font-extrabold text-[#004D3A] mt-1">{{ $mediaPontosTexto }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-3 mt-6">
                                <a href="{{ route('prova.final') }}?iniciar=1" class="inline-flex items-center justify-center bg-[#004D3A] text-white px-6 py-3 rounded-2xl font-bold hover:bg-[#003C2F] transition">
                                    Fazer prova final
                                </a>
                            </div>
                        </div>
                    </div>

                    <aside class="xl:col-span-4">
                        <div class="bg-white border border-[#E3EBE4] rounded-3xl p-6 shadow-sm">
                            <h3 class="text-xl font-extrabold text-[#003C2F] mb-4">Requisitos</h3>
                            <div class="space-y-4">
                                @foreach($requisitosProva as $requisito)
                                    <div class="flex items-start gap-3">
                                        <div class="w-9 h-9 rounded-xl {{ $requisito['ok'] ? 'bg-green-100 text-green-700' : 'bg-red-50 text-red-600' }} flex items-center justify-center shrink-0 font-bold">
                                            {{ $requisito['ok'] ? '✓' : '!' }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-[#003C2F]">{{ $requisito['titulo'] }}</p>
                                            <p class="text-sm text-[#60756B]">{{ $requisito['descricao'] }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </aside>
                </div>

            @else

                <!-- PROVA LIBERADA -->
                <form action="{{ route('prova.final.responder') }}" method="POST" id="formProvaFinalAluno">
                    @csrf

                    <input type="hidden" name="avaliacao_id" value="{{ $avaliacao->id }}">

                    <!-- CRONÔMETRO DA PROVA -->
                    <div id="cronometroProvaFinal"
                         class="hidden fixed bottom-4 right-4 z-[70] bg-white border-2 border-[#004D3A] rounded-3xl shadow-2xl px-5 py-4 min-w-[220px]">

                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-2xl bg-[#EAF5EF] text-[#004D3A] flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-6 h-6"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="1.8"
                                          d="M12 6v6h4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                                </svg>
                            </div>

                            <div>
                                <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                    Tempo restante
                                </p>

                                <p id="tempoRestanteProva" class="text-2xl font-extrabold text-[#004D3A] leading-tight">
                                    --:--
                                </p>
                            </div>
                        </div>

                        <div class="mt-3 h-2 bg-[#E8EFE9] rounded-full overflow-hidden">
                            <div id="barraTempoProva"
                                 class="h-full bg-[#004D3A] rounded-full transition-all duration-500"
                                 style="width: 100%;">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 xl:grid-cols-12 gap-7">

                        <!-- INFORMAÇÕES -->
                        <aside class="xl:col-span-4 2xl:col-span-3">

                            <div class="bg-white rounded-3xl border border-[#E3EBE4] shadow-sm p-5 sm:p-6 sticky top-6">

                                <div class="flex items-start gap-3 mb-6">

                                    <div class="w-12 h-12 rounded-2xl bg-[#EAF5EF] text-[#004D3A] flex items-center justify-center shrink-0">
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

                                    <div>
                                        <h2 class="text-xl font-extrabold text-[#003C2F] leading-tight">
                                            {{ $avaliacao->titulo ?? 'Prova Final' }}
                                        </h2>

                                        <p class="text-xs text-[#60756B] mt-1">
                                            Avaliação final do curso.
                                        </p>
                                    </div>

                                </div>

                                <div class="space-y-3">

                                    <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-4 flex items-center justify-between gap-3">
                                        <span class="text-sm font-bold text-[#60756B]">
                                            Tempo limite
                                        </span>

                                        <span class="text-lg font-extrabold text-[#004D3A]">
                                            {{ $avaliacao->tempo_limite ?? 60 }} min
                                        </span>
                                    </div>

                                    <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-4 flex items-center justify-between gap-3">
                                        <span class="text-sm font-bold text-[#60756B]">
                                            Tentativas
                                        </span>

                                        <span class="text-lg font-extrabold text-[#004D3A]">
                                            {{ $tentativas }}
                                        </span>
                                    </div>

                                    <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-4 flex items-center justify-between gap-3">
                                        <span class="text-sm font-bold text-[#60756B]">
                                            Questões
                                        </span>

                                        <span class="text-lg font-extrabold text-[#004D3A]">
                                            {{ $avaliacao->perguntas->count() }}
                                        </span>
                                    </div>

                                </div>

                                <div class="mt-5 bg-green-50 border border-green-100 rounded-2xl p-4 text-green-800 text-xs leading-relaxed">
                                    Leia com atenção. O cronômetro começa somente quando você clicar em <strong>Sim, iniciar</strong>.
                                    Ao terminar o tempo, a prova será enviada automaticamente.
                                </div>

                            </div>

                        </aside>

                        <!-- QUESTÕES -->
                        <div class="xl:col-span-8 2xl:col-span-9">

                            <div class="space-y-5">

                                @foreach($avaliacao->perguntas as $index => $pergunta)

                                    <div class="bg-white border border-[#E3EBE4] rounded-3xl shadow-sm overflow-hidden">

                                        <div class="bg-[#F8FBF8] border-b border-[#E3EBE4] px-5 sm:px-6 py-4 flex items-center gap-3">

                                            <div class="w-10 h-10 rounded-full bg-[#004D3A] text-white flex items-center justify-center text-sm font-extrabold">
                                                {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                            </div>

                                            <div>
                                                <p class="text-[11px] uppercase tracking-widest font-extrabold text-[#60756B]">
                                                    Questão de múltipla escolha
                                                </p>

                                                <p class="text-xs text-[#8A9B92] mt-1">
                                                    Selecione uma alternativa.
                                                </p>
                                            </div>

                                        </div>

                                        <div class="p-5 sm:p-6">

                                            <div class="bg-[#F1F6F2] border border-[#DCE7DE] rounded-2xl p-4 mb-5">
                                                <p class="font-extrabold text-[#003C2F] leading-relaxed">
                                                    {{ $pergunta->pergunta }}
                                                </p>
                                            </div>

                                            <div class="space-y-3">

                                                @foreach($pergunta->respostas as $respostaIndex => $resposta)

                                                    @php
                                                        $letra = chr(65 + $respostaIndex);
                                                    @endphp

                                                    <label class="flex items-center gap-3 bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl px-4 py-3 cursor-pointer hover:bg-[#EAF5EF] transition group">

                                                        <span class="w-10 h-10 rounded-full bg-[#E8EFE9] text-[#004D3A] flex items-center justify-center font-extrabold shrink-0 group-hover:bg-[#DCE7DE]">
                                                            {{ $letra }}
                                                        </span>

                                                        <input type="radio"
                                                               name="respostas[{{ $pergunta->id }}]"
                                                               value="{{ $resposta->id }}"
                                                               required
                                                               class="hidden peer">

                                                        <span class="flex-1 text-sm text-[#003C2F]">
                                                            {{ $resposta->resposta }}
                                                        </span>

                                                        <span class="w-7 h-7 rounded-full border border-[#AFC5B5] flex items-center justify-center peer-checked:bg-[#00A63E] peer-checked:border-[#00A63E] shrink-0">
                                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                                 class="w-4 h-4 text-white hidden peer-checked:block"
                                                                 fill="none"
                                                                 viewBox="0 0 24 24"
                                                                 stroke="currentColor">
                                                                <path stroke-linecap="round"
                                                                      stroke-linejoin="round"
                                                                      stroke-width="2.5"
                                                                      d="m4.5 12.75 6 6 9-13.5"/>
                                                            </svg>
                                                        </span>

                                                    </label>

                                                @endforeach

                                            </div>

                                        </div>

                                    </div>

                                @endforeach

                            </div>

                            <!-- BARRA DE FINALIZAR -->
                            <div class="mt-8 bg-white border border-[#E3EBE4] rounded-3xl shadow-sm p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                                <div>
                                    <p class="font-extrabold text-[#003C2F]">
                                        Pronto para finalizar?
                                    </p>

                                    <p class="text-sm text-[#60756B]">
                                        Revise suas respostas antes de enviar a prova.
                                    </p>

                                    <p id="mensagemTempoMinimoFinalizar" class="text-sm text-yellow-700 font-extrabold mt-2">
                                        O botão será liberado após atingir o tempo mínimo da prova.
                                    </p>
                                </div>

                                <button type="button"
                                        id="btnFinalizarProva"
                                        onclick="confirmarEnvioProva()"
                                        disabled
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-[#004D3A] text-white px-7 py-3 rounded-2xl shadow-lg hover:bg-[#003C2F] transition text-sm font-extrabold disabled:opacity-50 disabled:cursor-not-allowed">
                                    Finalizar Prova
                                </button>

                            </div>

                        </div>

                    </div>

                </form>

            @endif

        </section>

    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(isset($avaliacao) && $provaLiberada && $modoProvaFinal)
<script>
    let intervaloCronometroProva = null;
    let tempoTotalProvaSegundos = {{ $tempoLimiteProva }} * 60;
    let tempoMinimoProvaSegundos = {{ $tempoMinimoProva }} * 60;
    let tempoRestanteProvaSegundos = tempoTotalProvaSegundos;
    let tempoDecorridoProvaSegundos = 0;
    let provaFinalIniciada = false;
    let provaFinalEnviando = false;

    function formatarTempoProva(segundos) {
        const minutos = Math.floor(segundos / 60);
        const restoSegundos = segundos % 60;

        return String(minutos).padStart(2, '0') + ':' + String(restoSegundos).padStart(2, '0');
    }

    function tempoMinimoAtingidoProva() {
        return tempoMinimoProvaSegundos <= 0 || tempoDecorridoProvaSegundos >= tempoMinimoProvaSegundos;
    }

    function segundosFaltantesTempoMinimoProva() {
        return Math.max(0, tempoMinimoProvaSegundos - tempoDecorridoProvaSegundos);
    }

    function atualizarBloqueioBotaoFinalizarProva() {
        const btn = document.getElementById('btnFinalizarProva');
        const mensagem = document.getElementById('mensagemTempoMinimoFinalizar');

        const atingido = tempoMinimoAtingidoProva();
        const faltam = segundosFaltantesTempoMinimoProva();

        if (btn) {
            btn.disabled = !atingido;
        }

        if (mensagem) {
            if (atingido) {
                mensagem.innerText = 'Tempo mínimo atingido. Você já pode finalizar a prova quando desejar.';
                mensagem.classList.remove('text-yellow-700');
                mensagem.classList.add('text-green-700');
            } else {
                mensagem.innerText = 'Aguarde mais ' + formatarTempoProva(faltam) + ' para liberar o botão de finalizar.';
                mensagem.classList.remove('text-green-700');
                mensagem.classList.add('text-yellow-700');
            }
        }
    }

    function atualizarVisualCronometroProva() {
        const tempoEl = document.getElementById('tempoRestanteProva');
        const barraEl = document.getElementById('barraTempoProva');
        const caixaEl = document.getElementById('cronometroProvaFinal');

        if (tempoEl) {
            tempoEl.innerText = formatarTempoProva(Math.max(0, tempoRestanteProvaSegundos));
        }

        const porcentagem = tempoTotalProvaSegundos > 0
            ? Math.max(0, Math.round((tempoRestanteProvaSegundos / tempoTotalProvaSegundos) * 100))
            : 0;

        if (barraEl) {
            barraEl.style.width = porcentagem + '%';

            if (porcentagem <= 20) {
                barraEl.classList.remove('bg-[#004D3A]', 'bg-yellow-500');
                barraEl.classList.add('bg-red-600');
            } else if (porcentagem <= 50) {
                barraEl.classList.remove('bg-[#004D3A]', 'bg-red-600');
                barraEl.classList.add('bg-yellow-500');
            } else {
                barraEl.classList.remove('bg-yellow-500', 'bg-red-600');
                barraEl.classList.add('bg-[#004D3A]');
            }
        }

        if (caixaEl) {
            if (porcentagem <= 20) {
                caixaEl.classList.remove('border-[#004D3A]', 'border-yellow-500');
                caixaEl.classList.add('border-red-600');
            } else if (porcentagem <= 50) {
                caixaEl.classList.remove('border-[#004D3A]', 'border-red-600');
                caixaEl.classList.add('border-yellow-500');
            } else {
                caixaEl.classList.remove('border-yellow-500', 'border-red-600');
                caixaEl.classList.add('border-[#004D3A]');
            }
        }

        atualizarBloqueioBotaoFinalizarProva();
    }

    function iniciarCronometroProvaFinal() {
        if (provaFinalIniciada) return;

        provaFinalIniciada = true;
        tempoRestanteProvaSegundos = tempoTotalProvaSegundos;
        tempoDecorridoProvaSegundos = 0;

        const cronometro = document.getElementById('cronometroProvaFinal');

        if (cronometro) {
            cronometro.classList.remove('hidden');
        }

        atualizarVisualCronometroProva();

        intervaloCronometroProva = setInterval(() => {
            tempoRestanteProvaSegundos--;
            tempoDecorridoProvaSegundos++;
            atualizarVisualCronometroProva();

            if (tempoRestanteProvaSegundos <= 0) {
                clearInterval(intervaloCronometroProva);
                enviarProvaPorTempoEsgotado();
            }
        }, 1000);
    }

    function enviarProvaPorTempoEsgotado() {
        if (provaFinalEnviando) return;

        provaFinalEnviando = true;

        const form = document.getElementById('formProvaFinalAluno');

        Swal.fire({
            icon: 'warning',
            title: 'Tempo esgotado!',
            text: 'O tempo da prova acabou. Suas respostas serão enviadas automaticamente.',
            confirmButtonText: 'Entendi',
            confirmButtonColor: '#dc2626',
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then(() => {
            if (form) {
                form.submit();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            icon: 'question',
            title: 'Deseja iniciar a prova final?',
            html: `
                <p style="color:#60756B; font-size:14px; line-height:1.6;">
                    A prova final está liberada porque você concluiu 100% do curso e atingiu média mínima de 70% nos pós-testes.
                    <br><br>
                    Ao clicar em <strong>Sim, iniciar</strong>, o cronômetro começará imediatamente.
                    <br><br>
                    Tempo mínimo: <strong>{{ $tempoMinimoProva }} minuto(s)</strong>.<br>
                    Tempo máximo: <strong>{{ $tempoLimiteProva }} minuto(s)</strong>.<br>
                    Tentativas: <strong>{{ $tentativas }}</strong>.
                </p>
            `,
            showCancelButton: true,
            confirmButtonText: 'Sim, iniciar',
            cancelButtonText: 'Voltar depois',
            confirmButtonColor: '#004D3A',
            cancelButtonColor: '#64748b',
            background: '#ffffff',
            color: '#003C2F',
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then((result) => {
            if (!result.isConfirmed) {
                window.location.href = "{{ route('prova.final') }}";
                return;
            }

            iniciarCronometroProvaFinal();
        });
    });

    function confirmarEnvioProva() {
        const form = document.getElementById('formProvaFinalAluno');

        if (!form) return;

        if (!tempoMinimoAtingidoProva()) {
            Swal.fire({
                icon: 'warning',
                title: 'Tempo mínimo não atingido',
                text: 'Você precisa aguardar mais ' + formatarTempoProva(segundosFaltantesTempoMinimoProva()) + ' antes de finalizar a prova.',
                confirmButtonText: 'Entendi',
                confirmButtonColor: '#004D3A'
            });

            return;
        }

        if (!form.reportValidity()) {
            Swal.fire({
                icon: 'info',
                title: 'Responda todas as questões',
                text: 'Antes de finalizar, marque uma alternativa em cada pergunta.',
                confirmButtonColor: '#004D3A'
            });

            return;
        }

        Swal.fire({
            icon: 'warning',
            title: 'Finalizar prova?',
            text: 'Após enviar, suas respostas serão registradas.',
            showCancelButton: true,
            confirmButtonText: 'Sim, finalizar',
            cancelButtonText: 'Revisar',
            confirmButtonColor: '#004D3A',
            cancelButtonColor: '#64748b'
        }).then((result) => {
            if (result.isConfirmed) {
                provaFinalEnviando = true;

                if (intervaloCronometroProva) {
                    clearInterval(intervaloCronometroProva);
                }

                form.submit();
            }
        });
    }

    window.addEventListener('beforeunload', function(e) {
        if (provaFinalIniciada && !provaFinalEnviando) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
</script>
@endif

@endsection