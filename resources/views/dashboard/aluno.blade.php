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

    $totalModulos = $modulos->count();
    $totalAvisos = isset($avisosRecentes) ? $avisosRecentes->count() : 0;
    $totalAulasGeral = $aulasCurso->count();

    $progressoGeral = $totalEtapasCurso > 0
        ? round(($etapasConcluidasCurso / $totalEtapasCurso) * 100)
        : 0;

    $mediaGeral = $notasAluno->count() > 0
        ? round($notasAluno->avg(), 1)
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

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-7">

                <!-- COLUNA PRINCIPAL -->
                <div class="xl:col-span-8 space-y-7">

                    <!-- MÓDULOS -->
                    @if(isset($modulos))
                        <div class="bg-white border border-[#E3EBE4] rounded-3xl shadow-sm overflow-hidden">

                            <div class="p-5 sm:p-6 border-b border-[#E3EBE4] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-2xl bg-[#EAF5EF] text-[#004D3A] flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="w-6 h-6"
                                             fill="none"
                                             viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="1.8"
                                                  d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25A8.966 8.966 0 0 1 18 3.75c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.966 8.966 0 0 0-6 2.292m0-14.25v14.25"/>
                                        </svg>
                                    </div>

                                    <div>
                                        <h2 class="text-xl font-extrabold text-[#003C2F]">
                                            Módulos do curso
                                        </h2>

                                        <p class="text-xs text-[#60756B] mt-1">
                                            Acompanhe suas aulas e pós-testes.
                                        </p>
                                    </div>
                                </div>

                                <span class="inline-flex items-center justify-center bg-[#EAF5EF] text-[#004D3A] px-4 py-2 rounded-full text-xs font-extrabold">
                                    {{ $totalModulos }} módulos
                                </span>

                            </div>

                            <div class="p-4 sm:p-6 space-y-4">

                                @forelse($modulos as $modulo)

                                    @php
                                        $aulasModulo = $modulo->aulas ?? collect();
                                        $totalAulasModulo = count($aulasModulo);
                                        $aulasConcluidasModulo = 0;
                                        $totalItensModulo = 0;
                                        $itensConcluidosModulo = 0;

                                        foreach ($aulasModulo as $aulaResumo) {
                                            $avaliacaoResumoId = DB::table('avaliacoes')
                                                ->where('aula_id', $aulaResumo->id)
                                                ->value('id');

                                            $assistidaResumo = DB::table('aulas_assistidas')
                                                ->where('aluno_id', auth()->id())
                                                ->where('aula_id', $aulaResumo->id)
                                                ->where('assistido', true)
                                                ->exists();

                                            $posResumo = false;

                                            if ($avaliacaoResumoId) {
                                                $posResumo = DB::table('notas')
                                                    ->where('aluno_id', auth()->id())
                                                    ->where('avaliacao_id', $avaliacaoResumoId)
                                                    ->exists();
                                            }

                                            $totalItensModulo++;

                                            if ($assistidaResumo) {
                                                $aulasConcluidasModulo++;
                                                $itensConcluidosModulo++;
                                            }

                                            if ($avaliacaoResumoId) {
                                                $totalItensModulo++;

                                                if ($posResumo) {
                                                    $itensConcluidosModulo++;
                                                }
                                            }
                                        }

                                        $progressoModulo = $totalItensModulo > 0
                                            ? round(($itensConcluidosModulo / $totalItensModulo) * 100)
                                            : 0;
                                    @endphp

                                    <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-3xl overflow-hidden">

                                        <!-- CABEÇALHO MÓDULO -->
                                        <button type="button"
                                                onclick="toggleModulo({{ $modulo->id }})"
                                                class="w-full p-5 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 text-left hover:bg-[#F1F6F2] transition">

                                            <div class="flex items-start gap-4">

                                                <div class="w-11 h-11 rounded-2xl bg-white border border-[#E3EBE4] text-[#004D3A] flex items-center justify-center shrink-0">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                         class="w-5 h-5"
                                                         fill="none"
                                                         viewBox="0 0 24 24"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round"
                                                              stroke-linejoin="round"
                                                              stroke-width="1.8"
                                                              d="M4.5 6.75A2.25 2.25 0 0 1 6.75 4.5h10.5A2.25 2.25 0 0 1 19.5 6.75v10.5A2.25 2.25 0 0 1 17.25 19.5H6.75A2.25 2.25 0 0 1 4.5 17.25V6.75z"/>
                                                    </svg>
                                                </div>

                                                <div>
                                                    <h3 class="font-extrabold text-[#003C2F] text-lg">
                                                        {{ $modulo->nome }}
                                                    </h3>

                                                    <p class="text-xs text-[#60756B] mt-1">
                                                        {{ $aulasConcluidasModulo }} de {{ $totalAulasModulo }} aulas assistidas
                                                    </p>
                                                </div>

                                            </div>

                                            <div class="w-full lg:w-56">

                                                <div class="flex justify-between items-center mb-2">
                                                    <span class="text-xs font-bold text-[#60756B]">
                                                        Progresso
                                                    </span>

                                                    <span class="text-xs font-extrabold text-[#004D3A]">
                                                        {{ $progressoModulo }}%
                                                    </span>
                                                </div>

                                                <div class="h-2 bg-[#E8EFE9] rounded-full overflow-hidden">
                                                    <div class="h-full bg-[#004D3A] rounded-full"
                                                         style="width: {{ $progressoModulo }}%;">
                                                    </div>
                                                </div>

                                            </div>

                                        </button>

                                        <!-- AULAS -->
                                        <div id="modulo-{{ $modulo->id }}" class="hidden border-t border-[#E3EBE4] p-4 space-y-3">

                                            @if(isset($modulo->aulas))
                                                @forelse($modulo->aulas as $aula)

                                                    @php
                                                        $avaliacaoId = DB::table('avaliacoes')
                                                            ->where('aula_id', $aula->id)
                                                            ->value('id');

                                                        $aulaAssistida = DB::table('aulas_assistidas')
                                                            ->where('aluno_id', auth()->id())
                                                            ->where('aula_id', $aula->id)
                                                            ->where('assistido', true)
                                                            ->exists();

                                                        $posTesteConcluido = false;
                                                        $notaPosTeste = null;

                                                        if ($avaliacaoId) {
                                                            $notaRegistro = DB::table('notas')
                                                                ->where('aluno_id', auth()->id())
                                                                ->where('avaliacao_id', $avaliacaoId)
                                                                ->orderBy('id', 'desc')
                                                                ->first();

                                                            $posTesteConcluido = (bool) $notaRegistro;

                                                            if ($notaRegistro && isset($notaRegistro->nota)) {
                                                                $notaPosTeste = (float) $notaRegistro->nota;
                                                            }
                                                        }

                                                        $atividadeConcluida = $aulaAssistida && (!$avaliacaoId || $posTesteConcluido);
                                                    @endphp

                                                    <div class="bg-white border rounded-3xl p-4 transition
                                                        {{ $atividadeConcluida ? 'border-green-200 opacity-75' : 'border-[#E3EBE4] hover:shadow-md' }}">

                                                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

                                                            <div class="min-w-0">

                                                                <div class="flex flex-wrap items-center gap-2">

                                                                    @if($atividadeConcluida)
                                                                        <span class="w-7 h-7 rounded-full bg-green-100 text-green-700 flex items-center justify-center text-sm font-extrabold">
                                                                            ✓
                                                                        </span>
                                                                    @else
                                                                        <span class="w-7 h-7 rounded-full bg-[#EAF5EF] text-[#004D3A] flex items-center justify-center">
                                                                            ▶
                                                                        </span>
                                                                    @endif

                                                                    <p class="font-extrabold break-words
                                                                        {{ $atividadeConcluida ? 'text-[#8A9B92]' : 'text-[#003C2F]' }}">
                                                                        {{ $aula->titulo }}
                                                                    </p>

                                                                    @if($atividadeConcluida)
                                                                        <span class="text-[11px] bg-green-100 text-green-700 px-2 py-1 rounded-full font-extrabold">
                                                                            Concluído
                                                                        </span>
                                                                    @endif

                                                                </div>

                                                                <div class="mt-3 flex flex-wrap gap-2">

                                                                    @if($atividadeConcluida)
                                                                        <span class="inline-flex text-xs bg-[#F1F6F2] text-[#60756B] border border-[#E3EBE4] px-3 py-1 rounded-full font-bold">
                                                                            Aula e atividade finalizadas
                                                                        </span>
                                                                    @elseif($aulaAssistida && $avaliacaoId && !$posTesteConcluido)
                                                                        <span class="inline-flex text-xs bg-blue-50 text-blue-700 border border-blue-100 px-3 py-1 rounded-full font-bold">
                                                                            Aula assistida — pós-teste liberado
                                                                        </span>
                                                                    @elseif($aulaAssistida)
                                                                        <span class="inline-flex text-xs bg-green-50 text-green-700 border border-green-100 px-3 py-1 rounded-full font-bold">
                                                                            Aula concluída
                                                                        </span>
                                                                    @else
                                                                        <span class="inline-flex text-xs bg-yellow-50 text-yellow-700 border border-yellow-100 px-3 py-1 rounded-full font-bold">
                                                                            Assista para liberar o pós-teste
                                                                        </span>
                                                                    @endif

                                                                @if($notaPosTeste !== null)
                                                                    <div class="mt-3">
                                                                        <span class="inline-flex text-xs bg-blue-50 text-blue-700 border border-blue-100 px-3 py-1 rounded-full font-extrabold">
                                                                            Melhor nota do pós-teste: {{ number_format($notaPosTeste, 1) }}
                                                                        </span>
                                                                    </div>
                                                                @endif

                                                                @if(($aula->tempo_minimo_video ?? 0) > 0)
                                                                    <p class="text-[11px] text-[#60756B] mt-2 font-bold">
                                                                        Tempo mínimo da videoaula: {{ $aula->tempo_minimo_video }} minuto(s)
                                                                    </p>
                                                                @endif
                                                            </div>

                                                            <div class="flex flex-wrap gap-2 shrink-0">

                                                                @if(!$aulaAssistida)
                                                                    <button
                                                                        type="button"
                                                                        data-video="{{ $aula->video_url }}"
                                                                        data-aula="{{ $aula->id }}"
                                                                        data-avaliacao="{{ $avaliacaoId }}"
                                                                        data-tempo-minimo="{{ $aula->tempo_minimo_video ?? 0 }}"
                                                                        data-tempo-maximo="{{ $aula->tempo_maximo_video ?? 0 }}"
                                                                        onclick="abrirModal(this.dataset.video, this.dataset.aula, this.dataset.avaliacao, this.dataset.tempoMinimo, this.dataset.tempoMaximo)"
                                                                        class="bg-[#004D3A] hover:bg-[#003C2F] text-white px-4 py-2.5 rounded-2xl text-sm font-extrabold transition"
                                                                    >
                                                                        Assistir
                                                                    </button>
                                                                @else
                                                                    <button
                                                                        type="button"
                                                                        data-video="{{ $aula->video_url }}"
                                                                        data-aula="{{ $aula->id }}"
                                                                        data-avaliacao="{{ $avaliacaoId }}"
                                                                        data-tempo-minimo="{{ $aula->tempo_minimo_video ?? 0 }}"
                                                                        data-tempo-maximo="{{ $aula->tempo_maximo_video ?? 0 }}"
                                                                        onclick="abrirModal(this.dataset.video, this.dataset.aula, this.dataset.avaliacao, this.dataset.tempoMinimo, this.dataset.tempoMaximo)"
                                                                        class="bg-[#F1F6F2] hover:bg-[#E6EFE8] text-[#004D3A] border border-[#DCE7DE] px-4 py-2.5 rounded-2xl text-sm font-extrabold transition"
                                                                    >
                                                                        Rever aula
                                                                    </button>

                                                                    @if($avaliacaoId)
                                                                        <button
                                                                            type="button"
                                                                            onclick="fazerPosTeste('{{ $avaliacaoId }}')"
                                                                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-2xl text-sm font-extrabold transition"
                                                                        >
                                                                            {{ $posTesteConcluido ? 'Refazer pós-teste' : 'Fazer pós-teste' }}
                                                                        </button>

                                                                        @if($posTesteConcluido)
                                                                            <button
                                                                                type="button"
                                                                                onclick="verResultadoPosTeste('{{ $avaliacaoId }}')"
                                                                                class="bg-green-100 hover:bg-green-200 text-green-700 border border-green-200 px-4 py-2.5 rounded-2xl text-sm font-extrabold transition">
                                                                                Ver resultado
                                                                            </button>
                                                                        @endif
                                                                    @else
                                                                        <span class="text-xs text-[#60756B] self-center font-bold">
                                                                            Sem pós-teste
                                                                        </span>
                                                                    @endif
                                                                @endif

                                                            </div>

                                                        </div>

                                                    </div>

                                                @empty
                                                    <div class="bg-white border border-[#E3EBE4] rounded-3xl p-5 text-center text-[#60756B]">
                                                        Nenhuma aula neste módulo.
                                                    </div>
                                                @endforelse
                                            @endif

                                        </div>

                                    </div>

                                @empty

                                    <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-3xl p-8 text-center text-[#60756B]">
                                        Nenhum módulo encontrado.
                                    </div>

                                @endforelse

                            </div>

                        </div>
                    @endif

                </div>

                <!-- COLUNA LATERAL -->
                <div class="xl:col-span-4 space-y-7">

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