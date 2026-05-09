@extends('layout.app')

@section('title', 'Minhas Videoaulas')

@section('content')

@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;

    $usuarioLogado = auth()->user();
    $alunoId = auth()->id();

    $aulasConteudo = collect();
    $totalEtapasCurso = 0;
    $etapasConcluidasCurso = 0;

    $cursoAlunoId = null;
    $cursoAluno = null;

    if (isset($modulos) && $modulos->count() > 0) {
        $primeiroModulo = $modulos->first();
        $cursoAlunoId = $primeiroModulo->curso_id ?? null;

        if ($cursoAlunoId && Schema::hasTable('cursos')) {
            $cursoAluno = DB::table('cursos')
                ->where('id', $cursoAlunoId)
                ->first();
        }
    }

    if (!$cursoAluno && Schema::hasTable('matriculas') && Schema::hasTable('cursos')) {
        $cursoAlunoId = DB::table('matriculas')
            ->where('aluno_id', $alunoId)
            ->value('curso_id');

        if ($cursoAlunoId) {
            $cursoAluno = DB::table('cursos')
                ->where('id', $cursoAlunoId)
                ->first();
        }
    }

    $nomeCursoAluno = $cursoAluno->nome ?? 'Curso não identificado';
    $descricaoCursoAluno = $cursoAluno->descricao ?? 'Acompanhe suas aulas, módulos e atividades disponíveis.';

    if (isset($modulos)) {
        foreach ($modulos as $moduloIndex => $modulo) {
            $aulasModulo = collect($modulo->aulas ?? []);
            $totalEtapasModulo = 0;
            $etapasConcluidasModulo = 0;

            foreach ($aulasModulo as $aulaIndex => $aula) {
                $avaliacaoId = Schema::hasTable('avaliacoes')
                    ? DB::table('avaliacoes')->where('aula_id', $aula->id)->value('id')
                    : null;

                $aulaAssistida = Schema::hasTable('aulas_assistidas')
                    ? DB::table('aulas_assistidas')
                        ->where('aluno_id', $alunoId)
                        ->where('aula_id', $aula->id)
                        ->where('assistido', true)
                        ->exists()
                    : false;

                $posTesteConcluido = false;

                if ($avaliacaoId && Schema::hasTable('notas')) {
                    $posTesteConcluido = DB::table('notas')
                        ->where('aluno_id', $alunoId)
                        ->where('avaliacao_id', $avaliacaoId)
                        ->exists();
                }

                $atividadeConcluida = $aulaAssistida && (!$avaliacaoId || $posTesteConcluido);

                $totalEtapasModulo++;
                $totalEtapasCurso++;

                if ($aulaAssistida) {
                    $etapasConcluidasModulo++;
                    $etapasConcluidasCurso++;
                }

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
                    'titulo' => $aula->titulo,
                    'descricao' => $aula->descricao ?? null,
                    'video_url' => $aula->video_url ?? null,
                    'modulo_id' => $modulo->id,
                    'modulo_nome' => $modulo->nome,
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
    }

    $aulaAtual = $aulasConteudo->firstWhere('atividade_concluida', false) ?? $aulasConteudo->first();

    $moduloAtual = null;

    if ($aulaAtual && isset($modulos)) {
        $moduloAtual = $modulos->firstWhere('id', $aulaAtual->modulo_id);
    }

    $progressoCurso = $totalEtapasCurso > 0
        ? round(($etapasConcluidasCurso / $totalEtapasCurso) * 100)
        : 0;

    $totalAulas = $aulasConteudo->count();

    $totalModulos = isset($modulos) ? $modulos->count() : 0;

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
            font-size: 1.6rem !important;
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

            @if($aulaAtual)

                <!-- CABEÇALHO DO CURSO -->
                <div class="mb-7 grid grid-cols-1 xl:grid-cols-12 gap-5">

                    <div class="xl:col-span-8 bg-white border border-[#E3EBE4] rounded-3xl shadow-sm p-5 sm:p-6">

                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-5">

                            <div class="min-w-0">

                                <div class="inline-flex items-center gap-2 text-[11px] font-extrabold uppercase tracking-widest text-[#00A63E] mb-3">
                                    <span class="w-2 h-2 rounded-full bg-[#00A63E]"></span>
                                    Minha trilha de aprendizagem
                                </div>

                                <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                    Curso atual
                                </p>

                                <h1 class="text-2xl sm:text-4xl font-extrabold text-[#003C2F] tracking-tight mt-1 break-words">
                                    {{ $nomeCursoAluno }}
                                </h1>

                                <p class="text-sm text-[#60756B] mt-3 max-w-3xl leading-relaxed break-words">
                                    {{ $descricaoCursoAluno }}
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
                                Testes
                            </p>

                            <p class="text-3xl font-extrabold text-[#004D3A] mt-2">
                                {{ $totalTestesConcluidos }}
                            </p>
                        </div>

                    </div>

                </div>

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

                                @foreach($modulos as $moduloIndex => $modulo)

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

                                                <button type="button"
                                                        data-video="{{ $itemAula->video_url }}"
                                                        data-aula="{{ $itemAula->id }}"
                                                        data-avaliacao="{{ $itemAula->avaliacao_id }}"
                                                        onclick="abrirModal(this.dataset.video, this.dataset.aula, this.dataset.avaliacao)"
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

                                                </button>

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

                <div class="max-w-2xl mx-auto bg-white rounded-3xl border border-[#E3EBE4] shadow-sm p-8 text-center">

                    <div class="w-20 h-20 rounded-full bg-[#EAF5EF] text-[#004D3A] flex items-center justify-center mx-auto mb-5 text-3xl">
                        ▶
                    </div>

                    <h1 class="text-2xl font-extrabold text-[#004D3A]">
                        Nenhum módulo disponível ainda.
                    </h1>

                    <p class="mt-2 text-sm text-[#60756B]">
                        Assim que novas aulas forem cadastradas, elas aparecerão aqui.
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