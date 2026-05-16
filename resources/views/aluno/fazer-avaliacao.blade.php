@extends('layout.app')

@section('title', 'Minhas Videoaulas')

@section('content')

@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;

    $usuarioLogado = auth()->user();
    $alunoId = auth()->id();

    /*
    |--------------------------------------------------------------------------
    | CURSO ATUAL DO PERÍODO
    |--------------------------------------------------------------------------
    | Regra nova:
    | - O aluno NÃO escolhe curso.
    | - O sistema mostra apenas o curso atual/publicado.
    | - Se houver matrícula do aluno, usa o curso matriculado.
    | - Se não houver matrícula, usa o curso publicado/ativo mais recente.
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

    $cursoAtualId = $cursoAtual->id ?? null;

    /*
    |--------------------------------------------------------------------------
    | MÓDULOS E AULAS DO CURSO
    |--------------------------------------------------------------------------
    */

    $modulosCurso = collect();

    if ($cursoAtual && Schema::hasTable('modulos')) {
        $modulosCurso = DB::table('modulos')
            ->where('curso_id', $cursoAtual->id)
            ->orderBy(Schema::hasColumn('modulos', 'ordem') ? 'ordem' : 'id')
            ->orderBy('id')
            ->get();

        foreach ($modulosCurso as $modulo) {
            if (Schema::hasTable('aulas')) {
                $aulasQuery = DB::table('aulas')
                    ->where('modulo_id', $modulo->id);

                if (Schema::hasColumn('aulas', 'ordem')) {
                    $aulasQuery->orderBy('ordem');
                }

                $modulo->aulas = $aulasQuery
                    ->orderBy('id')
                    ->get();
            } else {
                $modulo->aulas = collect();
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CÁLCULO DE PROGRESSO DO CURSO INTEIRO
    |--------------------------------------------------------------------------
    | Cada aula conta como etapa.
    | Cada pós-teste conta como etapa.
    | Exemplo:
    | - Aula assistida = 1 etapa
    | - Pós-teste feito = 1 etapa
    |
    | A prova final só libera quando o aluno atingir 70% do CURSO.
    */

    $aulasConteudo = collect();

    $totalEtapasCurso = 0;
    $etapasConcluidasCurso = 0;

    foreach ($modulosCurso as $moduloIndex => $modulo) {
        $aulasModulo = collect($modulo->aulas ?? []);

        $totalEtapasModulo = 0;
        $etapasConcluidasModulo = 0;

        foreach ($aulasModulo as $aulaIndex => $aula) {
            $videoAula = $aula->video_url ?? $aula->video ?? null;

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
                $aulasAssistidasQuery = DB::table('aulas_assistidas')
                    ->where('aluno_id', $alunoId)
                    ->where('aula_id', $aula->id);

                if (Schema::hasColumn('aulas_assistidas', 'assistido')) {
                    $aulasAssistidasQuery->where('assistido', true);
                }

                $aulaAssistida = $aulasAssistidasQuery->exists();
            }

            $posTesteConcluido = false;

            if ($avaliacaoId && Schema::hasTable('notas')) {
                $posTesteConcluido = DB::table('notas')
                    ->where('aluno_id', $alunoId)
                    ->where('avaliacao_id', $avaliacaoId)
                    ->exists();
            }

            $atividadeConcluida = $aulaAssistida && (!$avaliacaoId || $posTesteConcluido);

            // Etapa 1: assistir aula
            $totalEtapasModulo++;
            $totalEtapasCurso++;

            if ($aulaAssistida) {
                $etapasConcluidasModulo++;
                $etapasConcluidasCurso++;
            }

            // Etapa 2: fazer pós-teste, caso exista
            if ($avaliacaoId) {
                $totalEtapasModulo++;
                $totalEtapasCurso++;

                if ($posTesteConcluido) {
                    $etapasConcluidasModulo++;
                    $etapasConcluidasCurso++;
                }
            }

            $aulasConteudo->push((object) [
                'id' => $aula->id,
                'titulo' => $aula->titulo ?? 'Aula sem título',
                'descricao' => $aula->descricao ?? null,
                'video_url' => $videoAula,
                'modulo_id' => $modulo->id,
                'modulo_nome' => $modulo->nome ?? 'Módulo sem nome',
                'modulo_numero' => $moduloIndex + 1,
                'ordem' => $aulaIndex + 1,
                'avaliacao_id' => $avaliacaoId,
                'aula_assistida' => $aulaAssistida,
                'pos_teste_concluido' => $posTesteConcluido,
                'atividade_concluida' => $atividadeConcluida,
            ]);
        }

        $modulo->total_etapas = $totalEtapasModulo;
        $modulo->etapas_concluidas = $etapasConcluidasModulo;
        $modulo->progresso_calculado = $totalEtapasModulo > 0
            ? round(($etapasConcluidasModulo / $totalEtapasModulo) * 100)
            : 0;
    }

    $progressoCurso = $totalEtapasCurso > 0
        ? round(($etapasConcluidasCurso / $totalEtapasCurso) * 100)
        : 0;

    $provaFinalLiberada = $progressoCurso >= 70;

    /*
    |--------------------------------------------------------------------------
    | PROVA FINAL E CERTIFICADO
    |--------------------------------------------------------------------------
    | A prova final libera com 70% do curso.
    | O certificado libera se a nota da prova final for 70% ou mais.
    */

    $avaliacaoFinal = null;
    $notaFinal = null;
    $notaFinalPercentual = null;
    $certificadoLiberado = false;

    if (Schema::hasTable('avaliacoes')) {
        $avaliacaoFinalQuery = DB::table('avaliacoes');

        if (Schema::hasColumn('avaliacoes', 'tipo')) {
            $avaliacaoFinalQuery->where('tipo', 'final');
        }

        if ($cursoAtualId && Schema::hasColumn('avaliacoes', 'curso_id')) {
            $avaliacaoFinalQuery->where('curso_id', $cursoAtualId);
        }

        $avaliacaoFinal = $avaliacaoFinalQuery
            ->orderBy('id', 'desc')
            ->first();
    }

    if ($avaliacaoFinal && Schema::hasTable('notas')) {
        $notaFinalRegistro = DB::table('notas')
            ->where('aluno_id', $alunoId)
            ->where('avaliacao_id', $avaliacaoFinal->id)
            ->orderBy('id', 'desc')
            ->first();

        if ($notaFinalRegistro) {
            if (isset($notaFinalRegistro->nota)) {
                $notaFinal = (float) $notaFinalRegistro->nota;
            } elseif (isset($notaFinalRegistro->pontuacao)) {
                $notaFinal = (float) $notaFinalRegistro->pontuacao;
            } elseif (isset($notaFinalRegistro->valor)) {
                $notaFinal = (float) $notaFinalRegistro->valor;
            }

            if ($notaFinal !== null) {
                // Se a nota estiver em escala 0 a 10, converte para percentual.
                // Se já estiver em 0 a 100, mantém.
                $notaFinalPercentual = $notaFinal <= 10
                    ? round($notaFinal * 10)
                    : round($notaFinal);

                $certificadoLiberado = $notaFinalPercentual >= 70;
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | AULA ATUAL
    |--------------------------------------------------------------------------
    */

    $aulaAtualId = request('aula_id');

    $aulaAtual = null;

    if ($aulaAtualId) {
        $aulaAtual = $aulasConteudo->firstWhere('id', (int) $aulaAtualId);
    }

    if (!$aulaAtual) {
        $aulaAtual = $aulasConteudo->firstWhere('atividade_concluida', false) ?? $aulasConteudo->first();
    }

    $moduloAtual = null;

    if ($aulaAtual) {
        $moduloAtual = $modulosCurso->firstWhere('id', $aulaAtual->modulo_id);
    }

    $totalModulos = $modulosCurso->count();
    $totalAulas = $aulasConteudo->count();
    $totalAulasAssistidas = $aulasConteudo->where('aula_assistida', true)->count();
    $totalTestesConcluidos = $aulasConteudo->where('pos_teste_concluido', true)->count();
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

    @media (max-width: 1024px) {
        .area-aluno-video {
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }
    }

    @media (max-width: 640px) {
        .titulo-aula-mobile {
            font-size: 1.55rem !important;
            line-height: 2rem !important;
        }

        .card-video-mobile {
            border-radius: 1.25rem !important;
        }
    }
</style>

<div class="flex min-h-screen w-full bg-[#F3F7F3] text-[#003C2F] overflow-x-hidden">

    @include('partials.sidebar-aluno')

    <main class="flex-1 min-w-0 w-full bg-[#F3F7F3] overflow-x-hidden">

        @include('partials.navbar')

        <section class="area-aluno-video p-4 sm:p-6 lg:p-8">

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

            <!-- CABEÇALHO -->
            <div class="mb-7 flex flex-col xl:flex-row xl:items-end xl:justify-between gap-5">

                <div class="min-w-0">
                    <div class="inline-flex items-center gap-2 text-[11px] font-extrabold uppercase tracking-widest text-[#00A63E] mb-2">
                        <span class="w-2 h-2 rounded-full bg-[#00A63E]"></span>
                        Ambiente do aluno
                    </div>

                    <h1 class="text-3xl sm:text-4xl font-extrabold text-[#003C2F] tracking-tight break-words">
                        Minhas Videoaulas
                    </h1>

                    <p class="text-sm text-[#60756B] mt-2 max-w-3xl">
                        Acompanhe o curso atual do período, conclua as aulas, realize os pós-testes e libere sua prova final.
                    </p>
                </div>

                <div class="bg-white border border-[#E3EBE4] rounded-3xl px-5 py-4 shadow-sm">
                    <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                        Curso atual
                    </p>

                    <p class="text-xl font-extrabold text-[#004D3A] mt-1 break-words max-w-[260px]">
                        {{ $cursoAtual->nome ?? 'Nenhum curso publicado' }}
                    </p>
                </div>

            </div>

            @if($cursoAtual)

                <!-- RESUMO DO CURSO -->
                <div class="mb-7 grid grid-cols-1 xl:grid-cols-12 gap-5">

                    <div class="xl:col-span-8 bg-white border border-[#E3EBE4] rounded-3xl shadow-sm p-5 sm:p-6">

                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-5">

                            <div class="min-w-0">

                                <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                    Curso do período
                                </p>

                                <h2 class="text-2xl sm:text-4xl font-extrabold text-[#003C2F] tracking-tight mt-1 break-words">
                                    {{ $cursoAtual->nome }}
                                </h2>

                                <p class="text-sm text-[#60756B] mt-3 max-w-3xl leading-relaxed break-words">
                                    {{ $cursoAtual->descricao ?? 'Acompanhe suas aulas, módulos, pós-testes e atividades disponíveis.' }}
                                </p>

                            </div>

                            <div class="bg-[#EAF5EF] border border-[#DCE7DE] rounded-3xl px-5 py-4 shrink-0">
                                <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                    Progresso do curso
                                </p>

                                <p class="text-4xl font-extrabold text-[#004D3A] mt-1">
                                    {{ $progressoCurso }}%
                                </p>
                            </div>

                        </div>

                        <div class="mt-6">
                            <div class="flex items-center justify-between text-xs font-bold text-[#004D3A] mb-2">
                                <span>{{ $etapasConcluidasCurso }} de {{ $totalEtapasCurso }} etapas concluídas</span>
                                <span>{{ $progressoCurso }}%</span>
                            </div>

                            <div class="h-3 bg-[#E7EEE9] rounded-full overflow-hidden">
                                <div class="h-full bg-[#005543] rounded-full transition-all duration-500"
                                     style="width: {{ $progressoCurso }}%;">
                                </div>
                            </div>

                            <p class="text-xs text-[#60756B] mt-3">
                                A prova final será liberada quando você concluir pelo menos <strong>70% do curso completo</strong>.
                            </p>
                        </div>

                    </div>

                    <div class="xl:col-span-4 grid grid-cols-2 gap-4">

                        <div class="bg-white border border-[#E3EBE4] rounded-3xl shadow-sm p-5">
                            <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                Módulos
                            </p>

                            <p class="text-3xl font-extrabold text-[#004D3A] mt-2">
                                {{ $totalModulos }}
                            </p>
                        </div>

                        <div class="bg-white border border-[#E3EBE4] rounded-3xl shadow-sm p-5">
                            <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                Aulas
                            </p>

                            <p class="text-3xl font-extrabold text-[#004D3A] mt-2">
                                {{ $totalAulas }}
                            </p>
                        </div>

                        <div class="bg-white border border-[#E3EBE4] rounded-3xl shadow-sm p-5">
                            <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                Assistidas
                            </p>

                            <p class="text-3xl font-extrabold text-[#004D3A] mt-2">
                                {{ $totalAulasAssistidas }}
                            </p>
                        </div>

                        <div class="bg-white border border-[#E3EBE4] rounded-3xl shadow-sm p-5">
                            <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                Pós-testes
                            </p>

                            <p class="text-3xl font-extrabold text-[#004D3A] mt-2">
                                {{ $totalTestesConcluidos }}
                            </p>
                        </div>

                    </div>

                </div>

                <!-- PROVA FINAL / CERTIFICADO -->
                <div class="mb-7 grid grid-cols-1 lg:grid-cols-2 gap-5">

                    <div class="bg-white border border-[#E3EBE4] rounded-3xl shadow-sm p-5 sm:p-6">

                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                    Prova final
                                </p>

                                <h3 class="text-xl font-extrabold text-[#003C2F] mt-1">
                                    Liberação por progresso do curso
                                </h3>

                                <p class="text-sm text-[#60756B] mt-2">
                                    Você precisa concluir 70% do curso completo para acessar a prova final.
                                </p>
                            </div>

                            <div class="rounded-2xl px-4 py-3 {{ $provaFinalLiberada ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                <p class="text-2xl font-extrabold">
                                    {{ $provaFinalLiberada ? 'OK' : '70%' }}
                                </p>
                            </div>
                        </div>

                        @if($provaFinalLiberada)
                            <a href="{{ route('prova.final') }}"
                               class="mt-5 inline-flex w-full justify-center items-center bg-[#005543] hover:bg-[#004636] text-white px-5 py-3 rounded-2xl font-extrabold transition">
                                Acessar prova final
                            </a>
                        @else
                            <button type="button"
                                    disabled
                                    class="mt-5 w-full bg-gray-100 text-gray-500 px-5 py-3 rounded-2xl font-extrabold cursor-not-allowed">
                                Prova final bloqueada
                            </button>
                        @endif

                    </div>

                    <div class="bg-white border border-[#E3EBE4] rounded-3xl shadow-sm p-5 sm:p-6">

                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                    Certificado
                                </p>

                                <h3 class="text-xl font-extrabold text-[#003C2F] mt-1">
                                    Liberação por nota da prova final
                                </h3>

                                <p class="text-sm text-[#60756B] mt-2">
                                    O certificado será liberado após nota mínima de 70% na prova final.
                                </p>
                            </div>

                            <div class="rounded-2xl px-4 py-3 {{ $certificadoLiberado ? 'bg-green-100 text-green-700' : 'bg-red-50 text-red-600' }}">
                                <p class="text-2xl font-extrabold">
                                    {{ $notaFinalPercentual !== null ? $notaFinalPercentual . '%' : '-' }}
                                </p>
                            </div>
                        </div>

                        @if($certificadoLiberado)
                            <a href="{{ route('certificado.aluno') }}"
                               class="mt-5 inline-flex w-full justify-center items-center bg-[#005543] hover:bg-[#004636] text-white px-5 py-3 rounded-2xl font-extrabold transition">
                                Acessar certificado
                            </a>
                        @else
                            <button type="button"
                                    disabled
                                    class="mt-5 w-full bg-gray-100 text-gray-500 px-5 py-3 rounded-2xl font-extrabold cursor-not-allowed">
                                Certificado bloqueado
                            </button>
                        @endif

                    </div>

                </div>

                @if($aulaAtual)

                    <!-- ÁREA PRINCIPAL -->
                    <div class="grid grid-cols-1 xl:grid-cols-12 gap-7">

                        <!-- PLAYER / AULA ATUAL -->
                        <section class="xl:col-span-8 min-w-0">

                            <div class="bg-white border border-[#E3EBE4] rounded-3xl shadow-sm overflow-hidden">

                                <div class="p-5 sm:p-6 border-b border-[#E3EBE4]">

                                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">

                                        <div class="min-w-0">

                                            <div class="flex flex-wrap items-center gap-2 mb-3">

                                                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#EAF5EF] text-[#004D3A] text-[11px] font-extrabold">
                                                    Módulo {{ str_pad($aulaAtual->modulo_numero, 2, '0', STR_PAD_LEFT) }}
                                                </span>

                                                @if($aulaAtual->aula_assistida)
                                                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-green-100 text-green-700 text-[11px] font-extrabold">
                                                        Aula assistida
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 text-[11px] font-extrabold">
                                                        Em andamento
                                                    </span>
                                                @endif

                                                @if($aulaAtual->avaliacao_id)
                                                    @if($aulaAtual->pos_teste_concluido)
                                                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-[11px] font-extrabold">
                                                            Pós-teste concluído
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-[11px] font-extrabold">
                                                            Possui pós-teste
                                                        </span>
                                                    @endif
                                                @endif

                                            </div>

                                            <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                                {{ $aulaAtual->modulo_nome }}
                                            </p>

                                            <h2 class="titulo-aula-mobile mt-2 text-2xl sm:text-3xl font-extrabold leading-tight text-[#003C2F] break-words">
                                                Aula {{ $aulaAtual->ordem }}: {{ $aulaAtual->titulo }}
                                            </h2>

                                            <p class="text-sm text-[#60756B] mt-3 leading-relaxed">
                                                Assista à aula e conclua as etapas para avançar no curso.
                                            </p>

                                        </div>

                                        <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-3xl p-4 min-w-[160px]">
                                            <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                                Progresso da aula
                                            </p>

                                            <p class="text-3xl font-extrabold text-[#004D3A] mt-1">
                                                {{ $aulaAtual->atividade_concluida ? '100' : ($aulaAtual->aula_assistida ? '50' : '0') }}%
                                            </p>
                                        </div>

                                    </div>

                                </div>

                                <div class="p-4 sm:p-6">

                                    <button type="button"
                                            data-video="{{ $aulaAtual->video_url }}"
                                            data-aula="{{ $aulaAtual->id }}"
                                            data-avaliacao="{{ $aulaAtual->avaliacao_id }}"
                                            onclick="abrirModal(this.dataset.video, this.dataset.aula, this.dataset.avaliacao)"
                                            class="card-video-mobile group relative w-full aspect-video bg-black rounded-3xl shadow-sm overflow-hidden flex items-center justify-center"
                                            aria-label="Assistir aula {{ $aulaAtual->titulo }}">

                                        <div class="absolute inset-0 bg-gradient-to-br from-[#003C2F] via-black to-[#0B1120]"></div>

                                        <div class="relative flex flex-col items-center justify-center gap-4 text-white">
                                            <span class="w-20 h-20 rounded-full border-4 border-white/70 bg-white/10 flex items-center justify-center transition group-hover:scale-105 group-hover:bg-white/20">
                                                <svg class="w-10 h-10 text-white ml-1" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M8 5v14l11-7z"/>
                                                </svg>
                                            </span>

                                            <span class="text-sm font-extrabold">
                                                Assistir aula
                                            </span>
                                        </div>

                                    </button>

                                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-5">

                                        <article class="lg:col-span-2 bg-[#F8FBF8] border border-[#E3EBE4] rounded-3xl p-5">
                                            <h3 class="text-sm font-extrabold text-[#004D3A] mb-2">
                                                Sobre esta aula
                                            </h3>

                                            <p class="text-sm leading-relaxed text-[#60756B]">
                                                {{ $aulaAtual->descricao ?: 'Nesta aula, acompanhe o conteúdo do módulo e finalize as etapas para liberar as próximas atividades do curso.' }}
                                            </p>
                                        </article>

                                        <aside class="bg-[#005543] text-white rounded-3xl p-5 flex flex-col justify-between gap-4">

                                            <div>
                                                <p class="text-[11px] uppercase font-extrabold tracking-widest text-white/70">
                                                    Sua atividade
                                                </p>

                                                <div class="mt-4 flex items-center justify-between text-xs font-bold">
                                                    <span>Etapa atual</span>
                                                    <span>{{ $aulaAtual->atividade_concluida ? 'Concluída' : 'Pendente' }}</span>
                                                </div>

                                                <div class="mt-2 h-2 bg-white/20 rounded-full overflow-hidden">
                                                    <div class="h-full bg-[#90D8C6] rounded-full"
                                                         style="width: {{ $aulaAtual->atividade_concluida ? '100' : ($aulaAtual->aula_assistida ? '50' : '0') }}%;">
                                                    </div>
                                                </div>
                                            </div>

                                            @if($aulaAtual->avaliacao_id && $aulaAtual->aula_assistida && !$aulaAtual->pos_teste_concluido)
                                                <button type="button"
                                                        onclick="fazerPosTeste('{{ $aulaAtual->avaliacao_id }}')"
                                                        class="bg-white text-[#005543] rounded-2xl px-4 py-3 text-xs font-extrabold hover:bg-[#ECF7F3] transition">
                                                    Realizar pós-teste
                                                </button>
                                            @elseif($aulaAtual->avaliacao_id && $aulaAtual->pos_teste_concluido)
                                                <button type="button"
                                                        onclick="verResultadoPosTeste('{{ $aulaAtual->avaliacao_id }}')"
                                                        class="bg-white text-[#005543] rounded-2xl px-4 py-3 text-xs font-extrabold hover:bg-[#ECF7F3] transition">
                                                    Ver resultado
                                                </button>
                                            @else
                                                <button type="button"
                                                        data-video="{{ $aulaAtual->video_url }}"
                                                        data-aula="{{ $aulaAtual->id }}"
                                                        data-avaliacao="{{ $aulaAtual->avaliacao_id }}"
                                                        onclick="abrirModal(this.dataset.video, this.dataset.aula, this.dataset.avaliacao)"
                                                        class="bg-white text-[#005543] rounded-2xl px-4 py-3 text-xs font-extrabold hover:bg-[#ECF7F3] transition">
                                                    Assistir aula
                                                </button>
                                            @endif

                                        </aside>

                                    </div>

                                </div>

                            </div>

                        </section>

                        <!-- TRILHA DO CURSO -->
                        <aside class="xl:col-span-4 space-y-5">

                            <div class="bg-white rounded-3xl border border-[#E3EBE4] shadow-sm overflow-hidden">

                                <div class="p-5 border-b border-[#E3EBE4]">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <h2 class="text-lg font-extrabold leading-tight text-[#004D3A]">
                                                Conteúdo do curso
                                            </h2>

                                            <p class="text-xs text-[#60756B] mt-1">
                                                Módulos, aulas e progresso do aluno.
                                            </p>
                                        </div>

                                        <div class="bg-[#EAF5EF] text-[#004D3A] rounded-2xl px-3 py-2 text-[10px] leading-tight font-extrabold text-center">
                                            {{ $etapasConcluidasCurso }}/{{ $totalEtapasCurso }}<br>
                                            etapas
                                        </div>
                                    </div>
                                </div>

                                <div class="p-4 space-y-4 max-h-[680px] overflow-y-auto">

                                    @foreach($modulosCurso as $moduloIndex => $modulo)

                                        @php
                                            $aulasModuloLista = $aulasConteudo
                                                ->where('modulo_id', $modulo->id)
                                                ->values();
                                        @endphp

                                        <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-3xl overflow-hidden">

                                            <div class="p-4 border-b border-[#E3EBE4]">

                                                <div class="flex items-start justify-between gap-3">

                                                    <div class="min-w-0">
                                                        <p class="text-[10px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                                            Módulo {{ str_pad($moduloIndex + 1, 2, '0', STR_PAD_LEFT) }}
                                                        </p>

                                                        <h3 class="text-sm font-extrabold text-[#003C2F] mt-1 break-words">
                                                            {{ $modulo->nome }}
                                                        </h3>
                                                    </div>

                                                    <span class="bg-white border border-[#DCE7DE] text-[#004D3A] rounded-xl px-2 py-1 text-[10px] font-extrabold shrink-0">
                                                        {{ $modulo->progresso_calculado ?? 0 }}%
                                                    </span>

                                                </div>

                                                <div class="mt-3 h-2 bg-[#E7EEE9] rounded-full overflow-hidden">
                                                    <div class="h-full bg-[#005543] rounded-full"
                                                         style="width: {{ $modulo->progresso_calculado ?? 0 }}%;">
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="p-2 space-y-2">

                                                @forelse($aulasModuloLista as $itemAula)

                                                    @php
                                                        $statusLabel = $itemAula->atividade_concluida
                                                            ? 'Concluída'
                                                            : ($itemAula->aula_assistida ? 'Pós-teste pendente' : ($itemAula->id === $aulaAtual->id ? 'Assistindo agora' : 'Pendente'));

                                                        $statusClasses = $itemAula->atividade_concluida
                                                            ? 'text-green-700'
                                                            : ($itemAula->id === $aulaAtual->id ? 'text-[#004D3A]' : 'text-[#60756B]');

                                                        $itemAtivo = $itemAula->id === $aulaAtual->id;
                                                    @endphp

                                                    <a href="{{ url()->current() }}?aula_id={{ $itemAula->id }}"
                                                       class="w-full flex items-center gap-3 p-3 rounded-2xl text-left transition {{ $itemAtivo ? 'bg-white border border-[#DCE7DE]' : 'hover:bg-white' }}">

                                                        <div class="w-14 h-14 rounded-2xl bg-[#D7DFD9] shrink-0 overflow-hidden relative">
                                                            <div class="absolute inset-0 bg-gradient-to-br from-[#003F35] via-[#0A6755] to-[#D6DDD8]"></div>

                                                            <div class="absolute inset-0 flex items-center justify-center">
                                                                <span class="w-8 h-8 rounded-full border border-white/70 bg-black/20 flex items-center justify-center">
                                                                    <svg class="w-4 h-4 text-white ml-0.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                                        <path d="M8 5v14l11-7z"/>
                                                                    </svg>
                                                                </span>
                                                            </div>
                                                        </div>

                                                        <div class="min-w-0 flex-1">
                                                            <p class="text-[10px] uppercase font-extrabold {{ $statusClasses }}">
                                                                {{ $statusLabel }}
                                                            </p>

                                                            <p class="text-xs font-extrabold text-[#173F36] leading-snug break-words">
                                                                {{ $itemAula->ordem }}. {{ $itemAula->titulo }}
                                                            </p>

                                                            <p class="text-[10px] text-[#73827D] mt-1">
                                                                {{ $itemAula->avaliacao_id ? 'Com pós-teste' : 'Sem pós-teste' }}
                                                            </p>
                                                        </div>

                                                    </a>

                                                @empty

                                                    <p class="text-xs text-[#60756B] p-3">
                                                        Nenhuma aula cadastrada neste módulo.
                                                    </p>

                                                @endforelse

                                            </div>

                                        </div>

                                    @endforeach

                                </div>

                            </div>

                        </aside>

                    </div>

                @else

                    <div class="bg-white rounded-3xl border border-[#E3EBE4] shadow-sm p-8 text-center">

                        <div class="w-20 h-20 rounded-full bg-[#EAF5EF] text-[#004D3A] flex items-center justify-center mx-auto mb-5 text-3xl">
                            📚
                        </div>

                        <h2 class="text-2xl font-extrabold text-[#004D3A]">
                            Este curso ainda não possui aulas.
                        </h2>

                        <p class="mt-2 text-sm text-[#60756B]">
                            Assim que o administrador cadastrar módulos e aulas, eles aparecerão aqui.
                        </p>

                    </div>

                @endif

            @else

                <div class="max-w-2xl mx-auto bg-white rounded-3xl border border-[#E3EBE4] shadow-sm p-8 text-center">

                    <div class="w-20 h-20 rounded-full bg-[#EAF5EF] text-[#004D3A] flex items-center justify-center mx-auto mb-5 text-3xl">
                        📚
                    </div>

                    <h1 class="text-2xl font-extrabold text-[#004D3A]">
                        Nenhum curso publicado ainda.
                    </h1>

                    <p class="mt-2 text-sm text-[#60756B]">
                        Assim que o professor publicar o curso do período, ele aparecerá aqui automaticamente.
                    </p>

                </div>

            @endif

        </section>

    </main>

</div>

<!-- MODAL DE VÍDEO -->
<div id="modalVideo" class="fixed inset-0 bg-black/70 hidden items-center justify-center z-50 px-4">
    <div class="bg-white w-[980px] max-w-full rounded-3xl p-4 relative border border-[#DFE8E1] shadow-2xl">

        <button onclick="fecharModal()"
                class="absolute top-3 right-4 text-3xl leading-none text-[#52645E] hover:text-red-600 transition z-10">
            ×
        </button>

        <iframe id="videoFrame"
                class="w-full h-[230px] sm:h-[420px] lg:h-[520px] rounded-2xl bg-black"
                src=""
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen>
        </iframe>

        <div class="mt-4 flex flex-col sm:flex-row sm:justify-between gap-3">
            <button onclick="fecharModal()"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-3 rounded-2xl font-bold transition">
                Fechar
            </button>

            <button onclick="marcarAssistida()"
                    class="bg-[#005543] hover:bg-[#004636] text-white px-4 py-3 rounded-2xl font-bold transition">
                Concluir aula
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let aulaIdAtual = null;
let avaliacaoIdAtual = null;

function normalizarUrlYoutube(url) {
    if (!url) return '';

    let video = String(url).trim();

    if (video.includes('watch?v=')) {
        video = video.replace('watch?v=', 'embed/');
    }

    if (video.includes('youtu.be/')) {
        video = video.replace('youtu.be/', 'www.youtube.com/embed/');
    }

    if (video.includes('&')) {
        video = video.split('&')[0];
    }

    return video;
}

function abrirModal(url, aulaId, avaliacaoId = null) {
    aulaIdAtual = aulaId;
    avaliacaoIdAtual = avaliacaoId && avaliacaoId !== 'null' && avaliacaoId !== '' ? avaliacaoId : null;

    const video = normalizarUrlYoutube(url);

    if (!video) {
        Swal.fire({
            icon: 'error',
            title: 'Vídeo não encontrado',
            text: 'Esta aula não possui link de vídeo cadastrado.',
            confirmButtonColor: '#dc2626'
        });
        return;
    }

    const modal = document.getElementById('modalVideo');
    const frame = document.getElementById('videoFrame');

    if (!modal || !frame) return;

    frame.src = video;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function fecharModal() {
    const modal = document.getElementById('modalVideo');
    const frame = document.getElementById('videoFrame');

    if (!modal || !frame) return;

    modal.classList.add('hidden');
    modal.classList.remove('flex');
    frame.src = "";
}

function fazerPosTeste(avaliacaoId) {
    if (!avaliacaoId || avaliacaoId === 'null') {
        Swal.fire({
            icon: 'info',
            title: 'Sem pós-teste',
            text: 'Esta aula ainda não possui pós-teste cadastrado.',
            confirmButtonColor: '#2563eb'
        });
        return;
    }

    window.location.href = '/avaliacoes/' + avaliacaoId;
}

function marcarAssistida() {
    if (!aulaIdAtual) return;

    fetch('/assistir-aula/' + aulaIdAtual)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao marcar aula como assistida.');
            }

            return response.json();
        })
        .then(() => {
            fecharModal();

            if (avaliacaoIdAtual) {
                Swal.fire({
                    icon: 'success',
                    title: 'Aula concluída!',
                    text: 'Deseja fazer o pós-teste agora?',
                    showCancelButton: true,
                    confirmButtonText: 'Sim, fazer agora',
                    cancelButtonText: 'Depois',
                    confirmButtonColor: '#005543',
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
                    confirmButtonColor: '#005543'
                }).then(() => {
                    location.reload();
                });
            }
        })
        .catch(() => {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Não foi possível concluir a aula. Tente novamente.',
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
            confirmButtonColor: '#2563eb'
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
                        extra += ' - Correta';
                    }

                    if (marcada && !correta) {
                        fundo = '#fee2e2';
                        borda = '#ef4444';
                        extra += ' - Sua resposta';
                    }

                    if (marcada && correta) {
                        extra += ' - Sua resposta';
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
                confirmButtonColor: '#005543',
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

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        fecharModal();
    }
});
</script>

@endsection