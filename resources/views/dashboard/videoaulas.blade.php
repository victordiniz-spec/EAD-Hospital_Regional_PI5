@extends('layout.app')

@section('title', 'Videoaulas')

@section('content')

@php
    use Illuminate\Support\Facades\DB;

    $cursoAtualId = $cursoAtual->id ?? null;

    $totalCursos = isset($cursos) ? $cursos->count() : 0;
    $totalModulos = isset($modulos) ? $modulos->count() : 0;
    $totalAulas = isset($aulas) ? $aulas->count() : 0;

    $idsAulas = isset($aulas) ? $aulas->pluck('id')->toArray() : [];

    $totalMiniTestes = count($idsAulas) > 0
        ? DB::table('avaliacoes')->whereIn('aula_id', $idsAulas)->count()
        : 0;

    $provaFinal = DB::table('avaliacoes')
        ->where('tipo', 'final')
        ->first();

    $tempoProvaFinal = $provaFinal->tempo_limite ?? 60;
    $notaMinima = 70;

    $totalAulasComTeste = count($idsAulas) > 0
        ? DB::table('avaliacoes')->whereIn('aula_id', $idsAulas)->distinct('aula_id')->count('aula_id')
        : 0;
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

    @include('partials.sidebar-professor')

    <main class="flex-1 min-w-0 w-full bg-[#F3F7F3] overflow-x-hidden">

        @include('partials.navbar')

        <section class="p-4 sm:p-6 lg:p-8">

            <!-- CABEÇALHO -->
            <div class="mb-7 flex flex-col xl:flex-row xl:items-end xl:justify-between gap-5">

                <div>
                    <div class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest text-[#00A63E] mb-2">
                        <span class="w-2 h-2 rounded-full bg-[#00A63E]"></span>
                        Administração de conteúdo
                    </div>

                    <h1 class="text-3xl sm:text-4xl font-extrabold text-[#003C2F] tracking-tight">
                        Gerenciamento de Videoaulas
                    </h1>

                    <p class="text-sm text-[#60756B] mt-2 max-w-3xl">
                        Organize cursos, módulos, aulas, mini testes e reutilize conteúdos antigos pela biblioteca de cursos.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">

                    <a
                        href="{{ route('biblioteca.cursos') }}"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-white text-[#004D3A] border border-[#DCE7DE] px-5 py-3 rounded-2xl shadow-sm hover:bg-[#F8FBF8] transition text-sm font-bold"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="w-5 h-5"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="1.8"
                                  d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25A8.966 8.966 0 0 1 18 3.75c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.966 8.966 0 0 0-6 2.292m0-14.25v14.25"/>
                        </svg>

                        Biblioteca de Cursos
                    </a>

                    <button
                        type="button"
                        onclick="abrirModalAula()"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-[#004D3A] text-white px-5 py-3 rounded-2xl shadow-sm hover:bg-[#003C2F] transition text-sm font-bold"
                    >
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

                        Nova Aula
                    </button>

                </div>

            </div>

            <!-- CURSO SELECIONADO -->
            <div class="bg-white border border-[#E3EBE4] rounded-3xl shadow-sm p-5 sm:p-6 mb-7">

                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">

                    <div class="min-w-0">
                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                            Curso selecionado
                        </p>

                        <h2 class="text-2xl font-extrabold text-[#003C2F] mt-1 break-words">
                            {{ $cursoAtual->nome ?? 'Nenhum curso selecionado' }}
                        </h2>

                        <p class="text-sm text-[#60756B] mt-2 max-w-3xl break-words">
                            {{ $cursoAtual->descricao ?? 'Selecione um curso ou crie uma nova aula para iniciar a estrutura.' }}
                        </p>
                    </div>

                    <form method="GET" action="{{ route('videoaulas') }}" class="w-full lg:w-[380px]">
                        <label class="block text-xs font-bold text-[#60756B] uppercase tracking-wider mb-1.5">
                            Trocar curso
                        </label>

                        <select
                            name="curso_id"
                            onchange="this.form.submit()"
                            class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-[#F8FBF8] text-[#003C2F] text-sm focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition cursor-pointer"
                        >
                            @forelse($cursos as $curso)
                                <option value="{{ $curso->id }}" {{ $cursoAtualId == $curso->id ? 'selected' : '' }}>
                                    {{ $curso->nome }}
                                </option>
                            @empty
                                <option value="">Nenhum curso cadastrado</option>
                            @endforelse
                        </select>
                    </form>

                </div>

            </div>

            <!-- RESUMO MOBILE -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6 xl:hidden">

                <div class="bg-white border border-[#E3EBE4] rounded-3xl p-5 shadow-sm">
                    <p class="text-xs text-[#60756B] font-semibold">Cursos</p>
                    <h3 class="text-3xl font-extrabold mt-1">{{ $totalCursos }}</h3>
                </div>

                <div class="bg-white border border-[#E3EBE4] rounded-3xl p-5 shadow-sm">
                    <p class="text-xs text-[#60756B] font-semibold">Módulos</p>
                    <h3 class="text-3xl font-extrabold mt-1">{{ $totalModulos }}</h3>
                </div>

                <div class="bg-white border border-[#E3EBE4] rounded-3xl p-5 shadow-sm">
                    <p class="text-xs text-[#60756B] font-semibold">Aulas</p>
                    <h3 class="text-3xl font-extrabold mt-1">{{ $totalAulas }}</h3>
                </div>

                <div class="bg-white border border-[#E3EBE4] rounded-3xl p-5 shadow-sm">
                    <p class="text-xs text-[#60756B] font-semibold">Mini testes</p>
                    <h3 class="text-3xl font-extrabold mt-1">{{ $totalMiniTestes }}</h3>
                </div>

            </div>

            <!-- CONTEÚDO PRINCIPAL -->
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-7">

                <!-- LISTA DE MÓDULOS -->
                <div class="xl:col-span-8 space-y-5">

                    @forelse ($modulos as $index => $modulo)

                        @php
                            $aulasDoModulo = $aulas->where('modulo_id', $modulo->id);
                            $totalAulasModulo = $aulasDoModulo->count();

                            $idsAulasModulo = $aulasDoModulo->pluck('id')->toArray();

                            $totalTestesModulo = count($idsAulasModulo) > 0
                                ? DB::table('avaliacoes')->whereIn('aula_id', $idsAulasModulo)->count()
                                : 0;
                        @endphp

                        <div class="bg-white border border-[#E3EBE4] rounded-3xl shadow-sm overflow-hidden">

                            <!-- CABEÇALHO DO MÓDULO -->
                            <div
                                onclick="toggleModulo({{ $modulo->id }})"
                                class="cursor-pointer p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 hover:bg-[#F8FBF8] transition"
                            >

                                <div class="flex items-start gap-4">

                                    <div class="w-12 h-12 rounded-2xl bg-[#004D3A] text-white flex items-center justify-center font-extrabold shrink-0">
                                        {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                    </div>

                                    <div>
                                        <h2 class="text-lg sm:text-xl font-extrabold text-[#003C2F]">
                                            {{ $modulo->nome }}
                                        </h2>

                                        <p class="text-xs sm:text-sm text-[#60756B] mt-1">
                                            {{ $totalAulasModulo }} aula(s) • {{ $totalTestesModulo }} mini teste(s)
                                        </p>
                                    </div>

                                </div>

                                <div class="flex items-center gap-3">

                                    <span class="text-[11px] bg-green-100 text-green-700 px-3 py-1 rounded-full font-bold">
                                        {{ $totalAulasModulo > 0 ? 'PUBLICADO' : 'VAZIO' }}
                                    </span>

                                    <span
                                        id="icon-{{ $modulo->id }}"
                                        class="text-[#60756B] transition-transform duration-300"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="w-5 h-5"
                                             fill="none"
                                             viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </span>

                                </div>

                            </div>

                            <!-- AULAS DO MÓDULO -->
                            <div id="modulo-{{ $modulo->id }}" class="{{ $loop->first ? '' : 'hidden' }} px-4 sm:px-6 pb-6 space-y-4">

                                @forelse ($aulasDoModulo as $aulaIndex => $aula)

                                    @php
                                        $avaliacaoAula = DB::table('avaliacoes')
                                            ->where('aula_id', $aula->id)
                                            ->first();

                                        $temMiniTeste = $avaliacaoAula ? true : false;
                                    @endphp

                                    <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-3xl p-4 transition hover:shadow-md">

                                        <div class="flex flex-col lg:flex-row lg:items-center gap-4">

                                            <!-- THUMB -->
                                            <div class="w-full lg:w-40 h-28 rounded-2xl overflow-hidden bg-[#111827] shrink-0 relative flex items-center justify-center">

                                                <div class="absolute inset-0 bg-gradient-to-br from-black via-[#1F2937] to-black opacity-90"></div>

                                                <div class="relative w-12 h-12 rounded-full bg-white/10 border border-white/20 flex items-center justify-center backdrop-blur">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                         class="w-6 h-6 text-white"
                                                         fill="currentColor"
                                                         viewBox="0 0 24 24">
                                                        <path d="M8 5v14l11-7z"/>
                                                    </svg>
                                                </div>

                                            </div>

                                            <!-- INFO -->
                                            <div class="flex-1 min-w-0">

                                                <div class="flex flex-wrap items-center gap-2 mb-2">

                                                    <span class="text-[11px] font-extrabold text-[#004D3A] bg-[#EAF5EF] px-2.5 py-1 rounded-lg">
                                                        Aula {{ str_pad($aulaIndex + 1, 2, '0', STR_PAD_LEFT) }}
                                                    </span>

                                                    @if($temMiniTeste)
                                                        <span class="text-[11px] font-bold bg-blue-100 text-blue-700 px-2.5 py-1 rounded-lg">
                                                            Mini teste cadastrado
                                                        </span>
                                                    @else
                                                        <span class="text-[11px] font-bold bg-gray-100 text-gray-600 px-2.5 py-1 rounded-lg">
                                                            Sem mini teste
                                                        </span>
                                                    @endif

                                                    <span class="text-[11px] font-bold bg-green-100 text-green-700 px-2.5 py-1 rounded-lg">
                                                        Publicado
                                                    </span>

                                                </div>

                                                <h3 class="font-extrabold text-[#003C2F] text-base sm:text-lg break-words">
                                                    {{ $aula->titulo }}
                                                </h3>

                                                <p class="text-sm text-[#60756B] mt-1 break-words line-clamp-2">
                                                    {{ $aula->descricao ?: 'Sem descrição cadastrada.' }}
                                                </p>

                                                @if (!empty($aula->video_url))
                                                    <p class="text-xs text-[#8A9B92] mt-2 break-all">
                                                        {{ $aula->video_url }}
                                                    </p>
                                                @endif

                                            </div>

                                            <!-- AÇÕES -->
                                            <div class="w-full lg:w-auto flex flex-col sm:flex-row lg:flex-col gap-2 shrink-0">

                                                <!-- EDITAR CONTEÚDO -->
                                                <button
                                                    type="button"
                                                    onclick='abrirModalEditarAula(
                                                        @json($aula->id),
                                                        @json($aula->titulo),
                                                        @json($aula->descricao),
                                                        @json($aula->video_url),
                                                        @json($aula->modulo_id)
                                                    )'
                                                    class="inline-flex items-center justify-center gap-2 bg-white text-[#004D3A] border border-[#DCE7DE] px-4 py-3 rounded-2xl text-sm font-bold hover:bg-[#EAF5EF] hover:border-[#00A63E]/40 transition"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                         class="w-4 h-4"
                                                         fill="none"
                                                         viewBox="0 0 24 24"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round"
                                                              stroke-linejoin="round"
                                                              stroke-width="1.8"
                                                              d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931z"/>
                                                    </svg>

                                                    Editar Conteúdo
                                                </button>

                                                <!-- MINI TESTE -->
                                                <a
                                                    href="{{ route('avaliacoes.criar', $aula->id) }}"
                                                    class="inline-flex items-center justify-center gap-2
                                                        {{ $temMiniTeste
                                                            ? 'bg-blue-50 text-blue-700 border border-blue-100 hover:bg-blue-100'
                                                            : 'bg-[#EAF5EF] text-[#004D3A] border border-[#DCE7DE] hover:bg-[#DCE7DE]'
                                                        }}
                                                        px-4 py-3 rounded-2xl text-sm font-bold transition"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                         class="w-4 h-4"
                                                         fill="none"
                                                         viewBox="0 0 24 24"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round"
                                                              stroke-linejoin="round"
                                                              stroke-width="1.8"
                                                              d="M9 12h6m-6 4h6M9 8h6M5 4h14v16H5z"/>
                                                    </svg>

                                                    {{ $temMiniTeste ? 'Editar Mini Teste' : 'Criar Mini Teste' }}
                                                </a>

                                                <!-- EXCLUIR -->
                                                <form
                                                    action="{{ route('aulas.destroy', $aula->id) }}"
                                                    method="POST"
                                                    class="w-full form-excluir-aula"
                                                >
                                                    @csrf
                                                    @method('DELETE')

                                                    <button
                                                        type="button"
                                                        onclick='confirmarExclusaoAula(this, @json($aula->titulo))'
                                                        class="w-full inline-flex items-center justify-center gap-2 bg-red-50 text-red-600 border border-red-100 px-4 py-3 rounded-2xl text-sm font-bold hover:bg-red-100 transition"
                                                    >
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                             class="w-4 h-4"
                                                             fill="none"
                                                             viewBox="0 0 24 24"
                                                             stroke="currentColor">
                                                            <path stroke-linecap="round"
                                                                  stroke-linejoin="round"
                                                                  stroke-width="1.8"
                                                                  d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16"/>
                                                        </svg>

                                                        Excluir
                                                    </button>
                                                </form>

                                            </div>

                                        </div>

                                    </div>

                                @empty

                                    <div class="bg-[#F8FBF8] border border-dashed border-[#AFC5B5] rounded-3xl p-8 text-center">

                                        <div class="w-14 h-14 rounded-full bg-[#EAF5EF] mx-auto mb-4 flex items-center justify-center text-[#004D3A]">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                 class="w-7 h-7"
                                                 fill="none"
                                                 viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                      stroke-width="1.8"
                                                      d="M12 4.5v15m7.5-7.5h-15"/>
                                            </svg>
                                        </div>

                                        <h3 class="font-extrabold text-[#003C2F]">
                                            Nenhuma aula neste módulo
                                        </h3>

                                        <p class="text-sm text-[#60756B] mt-1">
                                            Adicione uma aula para começar a organizar este módulo.
                                        </p>

                                    </div>

                                @endforelse

                                <!-- ADICIONAR AULA -->
                                <button
                                    type="button"
                                    onclick="abrirModalAula()"
                                    class="w-full border border-dashed border-[#AFC5B5] text-[#004D3A] rounded-3xl py-4 text-sm font-extrabold hover:bg-[#F8FBF8] transition flex items-center justify-center gap-2"
                                >
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

                                    Adicionar Nova Aula ao Módulo
                                </button>

                            </div>

                        </div>

                    @empty

                        <div class="bg-white rounded-3xl border border-[#E3EBE4] shadow-sm p-8 text-center">

                            <div class="w-16 h-16 rounded-full bg-[#EAF5EF] mx-auto mb-4 flex items-center justify-center text-[#004D3A]">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-8 h-8"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="1.8"
                                          d="M12 4.5v15m7.5-7.5h-15"/>
                                </svg>
                            </div>

                            <h3 class="font-extrabold text-xl text-[#003C2F] mb-2">
                                Nenhum módulo encontrado
                            </h3>

                            <p class="text-[#60756B] text-sm mb-5">
                                Clique em <strong>Nova Aula</strong> e preencha o campo “Novo módulo”.
                            </p>

                            <button
                                type="button"
                                onclick="abrirModalAula()"
                                class="bg-[#004D3A] text-white px-5 py-3 rounded-2xl hover:bg-[#003C2F] transition text-sm font-bold"
                            >
                                Criar primeira aula
                            </button>

                        </div>

                    @endforelse

                </div>

                <!-- PAINEL DIREITO -->
                <aside class="xl:col-span-4 space-y-5">

                    <!-- CONFIGURAÇÃO PROVA FINAL -->
                    <div class="bg-white border border-[#E3EBE4] rounded-3xl shadow-sm overflow-hidden">

                        <div class="h-1.5 bg-[#004D3A]"></div>

                        <div class="p-5 sm:p-6">

                            <div class="flex items-start gap-3 mb-6">

                                <div class="w-11 h-11 rounded-2xl bg-[#EAF5EF] text-[#004D3A] flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="w-6 h-6"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="1.8"
                                              d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                                    </svg>
                                </div>

                                <div>
                                    <h2 class="font-extrabold text-xl text-[#003C2F]">
                                        Configuração: Prova Final
                                    </h2>

                                    <p class="text-xs text-[#60756B] mt-1">
                                        Parâmetros gerais da avaliação final.
                                    </p>
                                </div>

                            </div>

                            <div class="mb-6">
                                <div class="flex justify-between items-end mb-2">
                                    <p class="text-[11px] font-bold uppercase tracking-widest text-[#60756B]">
                                        Nota mínima para aprovação
                                    </p>

                                    <span class="text-2xl font-extrabold text-[#004D3A]">
                                        {{ $notaMinima }}%
                                    </span>
                                </div>

                                <div class="h-2 bg-[#E8EFE9] rounded-full overflow-hidden">
                                    <div class="h-full bg-[#004D3A] rounded-full" style="width: {{ $notaMinima }}%;"></div>
                                </div>
                            </div>

                            <div class="mb-6">
                                <p class="text-[11px] font-bold uppercase tracking-widest text-[#60756B] mb-3">
                                    Tentativas permitidas
                                </p>

                                <div class="grid grid-cols-4 gap-2">
                                    <span class="bg-[#F1F6F2] text-[#60756B] rounded-xl py-2 text-center text-sm font-extrabold">
                                        01
                                    </span>

                                    <span class="bg-[#F1F6F2] text-[#60756B] rounded-xl py-2 text-center text-sm font-extrabold">
                                        02
                                    </span>

                                    <span class="bg-[#004D3A] text-white rounded-xl py-2 text-center text-sm font-extrabold">
                                        03
                                    </span>

                                    <span class="bg-[#F1F6F2] text-[#60756B] rounded-xl py-2 text-center text-sm font-extrabold">
                                        ∞
                                    </span>
                                </div>
                            </div>

                            <div class="space-y-3 mb-6">

                                <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-4 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-white flex items-center justify-center text-[#004D3A]">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                 class="w-5 h-5"
                                                 fill="none"
                                                 viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                      stroke-width="1.8"
                                                      d="M12 6v6h4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                                            </svg>
                                        </div>

                                        <p class="text-sm font-bold text-[#60756B]">
                                            Tempo limite
                                        </p>
                                    </div>

                                    <p class="text-lg font-extrabold text-[#004D3A]">
                                        {{ $tempoProvaFinal }} min
                                    </p>
                                </div>

                                <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-4 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-white flex items-center justify-center text-[#004D3A]">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                 class="w-5 h-5"
                                                 fill="none"
                                                 viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                      stroke-width="1.8"
                                                      d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5M16.5 3 21 7.5m0 0L16.5 12M21 7.5H7.5"/>
                                            </svg>
                                        </div>

                                        <p class="text-sm font-bold text-[#60756B]">
                                            Questões aleatórias
                                        </p>
                                    </div>

                                    <div class="w-12 h-7 rounded-full bg-[#00A63E] p-1 flex justify-end">
                                        <span class="w-5 h-5 rounded-full bg-white shadow"></span>
                                    </div>
                                </div>

                            </div>

                            <a
                                href="{{ route('prova.final.criar') }}"
                                class="w-full inline-flex items-center justify-center gap-2 bg-[#004D3A] text-white rounded-2xl px-4 py-3 text-sm font-extrabold hover:bg-[#003C2F] transition"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-5 h-5"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="1.8"
                                          d="M9 12h6m-6 4h6M9 8h6M5 4h14v16H5z"/>
                                </svg>

                                Editar Banco de Questões
                            </a>

                        </div>

                    </div>

                    <!-- CARDS PEQUENOS -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-2 gap-4">

                        <div class="bg-[#004D3A] text-white rounded-3xl p-5 shadow-sm">
                            <p class="text-[11px] uppercase tracking-widest font-bold text-white/70">
                                Total de aulas
                            </p>

                            <h3 class="text-3xl font-extrabold mt-2">
                                {{ $totalAulas }}
                            </h3>

                            <div class="mt-4 h-1.5 bg-white/20 rounded-full overflow-hidden">
                                <div class="h-full bg-white rounded-full" style="width: {{ $totalAulas > 0 ? 80 : 0 }}%;"></div>
                            </div>
                        </div>

                        <div class="bg-white border border-[#E3EBE4] rounded-3xl p-5 shadow-sm">
                            <p class="text-[11px] uppercase tracking-widest font-bold text-[#60756B]">
                                Mini testes
                            </p>

                            <h3 class="text-3xl font-extrabold mt-2 text-[#003C2F]">
                                {{ $totalMiniTestes }}
                            </h3>

                            <p class="text-xs text-[#60756B] mt-2">
                                {{ $totalAulasComTeste }} aula(s) com teste.
                            </p>
                        </div>

                    </div>

                    <!-- CARD BIBLIOTECA -->
                    <div class="bg-white border border-[#E3EBE4] rounded-3xl p-5 shadow-sm">

                        <div class="flex items-start gap-3 mb-4">
                            <div class="w-11 h-11 rounded-2xl bg-[#EAF5EF] text-[#004D3A] flex items-center justify-center shrink-0">
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
                                <h2 class="font-extrabold text-lg text-[#003C2F]">
                                    Biblioteca
                                </h2>

                                <p class="text-xs text-[#60756B] mt-1">
                                    Reutilize cursos antigos com módulos, aulas e testes.
                                </p>
                            </div>
                        </div>

                        <a href="{{ route('biblioteca.cursos') }}"
                           class="w-full inline-flex items-center justify-center bg-[#EAF5EF] text-[#004D3A] rounded-2xl px-4 py-3 text-sm font-extrabold hover:bg-[#DCE7DE] transition">
                            Abrir biblioteca de cursos
                        </a>

                    </div>

                </aside>

            </div>

        </section>

    </main>

</div>

<!-- BOTÃO FLUTUANTE -->
<button
    type="button"
    onclick="abrirModalAula()"
    class="fixed right-5 bottom-5 w-14 h-14 rounded-full bg-[#004D3A] text-white shadow-2xl flex items-center justify-center hover:bg-[#003C2F] transition z-40"
>
    <svg xmlns="http://www.w3.org/2000/svg"
         class="w-7 h-7"
         fill="none"
         viewBox="0 0 24 24"
         stroke="currentColor">
        <path stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 4.5v15m7.5-7.5h-15"/>
    </svg>
</button>

<!-- MODAL CRIAR AULA -->
<div id="modalAula" class="fixed inset-0 hidden items-center justify-center z-50"
     style="background: rgba(0,0,0,0.45); backdrop-filter: blur(4px);">

    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-3xl mx-4 overflow-hidden"
         style="max-height: 90vh; overflow-y: auto;">

        <!-- HEADER MODAL -->
        <div class="flex items-start justify-between px-5 sm:px-8 pt-8 pb-4">

            <div>
                <h2 class="text-2xl font-extrabold text-[#003C2F]">
                    Criar Aula Completa
                </h2>

                <p class="text-sm text-[#60756B] mt-1">
                    Cadastre curso, módulo, aula e mini teste, se desejar.
                </p>
            </div>

            <button
                type="button"
                onclick="fecharModalAula()"
                class="w-10 h-10 rounded-xl bg-[#F1F6F2] text-[#003C2F] flex items-center justify-center hover:bg-[#E6EFE8] transition"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

        </div>

        <div class="px-5 sm:px-8 pb-8">

            <form action="{{ route('aulas.store') }}" method="POST" id="formAula">
                @csrf

                <!-- CURSO -->
                <div class="mb-5 border border-[#E3EBE4] rounded-3xl p-4 bg-[#F8FBF8]">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <div>
                            <label class="block text-xs font-bold text-[#60756B] uppercase tracking-wider mb-1.5">
                                Curso existente
                            </label>

                            <select
                                name="curso_id"
                                class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-white text-[#003C2F] text-sm focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition cursor-pointer"
                            >
                                <option value="">Selecionar curso</option>

                                @foreach ($cursos as $curso)
                                    <option
                                        value="{{ $curso->id }}"
                                        {{ old('curso_id', $cursoAtualId) == $curso->id ? 'selected' : '' }}
                                    >
                                        {{ $curso->nome }}
                                    </option>
                                @endforeach
                            </select>

                            <p class="text-xs text-[#8A9B92] mt-1">
                                Escolha um curso existente.
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#60756B] uppercase tracking-wider mb-1.5">
                                Novo curso
                            </label>

                            <input
                                type="text"
                                name="novo_curso"
                                value="{{ old('novo_curso') }}"
                                placeholder="Ou criar novo curso"
                                class="w-full px-4 py-3 rounded-2xl border border-dashed border-[#00A63E] bg-[#EAF5EF] text-[#003C2F] text-sm placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition"
                            >

                            <p class="text-xs text-[#8A9B92] mt-1">
                                Use se ainda não existir curso.
                            </p>
                        </div>

                    </div>

                    <div class="mt-4">
                        <label class="block text-xs font-bold text-[#60756B] uppercase tracking-wider mb-1.5">
                            Descrição do novo curso
                        </label>

                        <textarea
                            name="descricao_curso"
                            placeholder="Opcional: descreva o objetivo do novo curso..."
                            rows="2"
                            class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-white text-[#003C2F] text-sm placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition resize-none"
                        >{{ old('descricao_curso') }}</textarea>
                    </div>

                </div>

                <!-- MÓDULO -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">

                    <div>
                        <label class="block text-xs font-bold text-[#60756B] uppercase tracking-wider mb-1.5">
                            Módulo existente
                        </label>

                        <select
                            name="modulo_id"
                            class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-[#F8FBF8] text-[#003C2F] text-sm focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition cursor-pointer"
                        >
                            <option value="">Selecionar módulo</option>

                            @foreach ($modulos as $modulo)
                                <option
                                    value="{{ $modulo->id }}"
                                    {{ old('modulo_id') == $modulo->id ? 'selected' : '' }}
                                >
                                    {{ $modulo->nome }}
                                </option>
                            @endforeach
                        </select>

                        <p class="text-xs text-[#8A9B92] mt-1">
                            Escolha um módulo existente.
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#60756B] uppercase tracking-wider mb-1.5">
                            Novo módulo
                        </label>

                        <input
                            type="text"
                            name="novo_modulo"
                            value="{{ old('novo_modulo') }}"
                            placeholder="Ou criar novo módulo"
                            class="w-full px-4 py-3 rounded-2xl border border-dashed border-[#00A63E] bg-[#EAF5EF] text-[#003C2F] text-sm placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition"
                        >

                        <p class="text-xs text-[#8A9B92] mt-1">
                            Use se ainda não existir módulo.
                        </p>
                    </div>

                </div>

                <!-- TÍTULO -->
                <div class="mb-4">
                    <label class="block text-xs font-bold text-[#60756B] uppercase tracking-wider mb-1.5">
                        Título da aula
                    </label>

                    <input
                        type="text"
                        name="titulo"
                        value="{{ old('titulo') }}"
                        placeholder="Ex: Aula 01: Introdução aos Sistemas de Saúde"
                        required
                        class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-[#F8FBF8] text-[#003C2F] text-sm placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition"
                    >
                </div>

                <!-- DESCRIÇÃO -->
                <div class="mb-4">
                    <label class="block text-xs font-bold text-[#60756B] uppercase tracking-wider mb-1.5">
                        Descrição
                    </label>

                    <textarea
                        name="descricao"
                        placeholder="Descreva brevemente o conteúdo da aula..."
                        rows="3"
                        class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-[#F8FBF8] text-[#003C2F] text-sm placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition resize-none"
                    >{{ old('descricao') }}</textarea>
                </div>

                <!-- LINK -->
                <div class="mb-6">
                    <label class="block text-xs font-bold text-[#60756B] uppercase tracking-wider mb-1.5">
                        Link do vídeo
                    </label>

                    <input
                        type="text"
                        name="video_url"
                        value="{{ old('video_url') }}"
                        placeholder="Cole aqui o link do YouTube ou embed"
                        required
                        class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-[#F8FBF8] text-[#003C2F] text-sm placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition"
                    >
                </div>

                <!-- TESTE -->
                <div class="mb-4 border-t border-[#DCE7DE] pt-5">
                    <h3 class="font-extrabold text-[#003C2F]">
                        Mini Teste
                    </h3>

                    <p class="text-xs text-[#60756B] mt-1">
                        Opcional. Você pode criar a aula sem perguntas e adicionar o teste depois.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">

                    <div>
                        <label class="block text-xs font-bold text-[#60756B] uppercase tracking-wider mb-1.5">
                            Título do teste
                        </label>

                        <input
                            type="text"
                            name="avaliacao[titulo]"
                            value="{{ old('avaliacao.titulo') }}"
                            placeholder="Ex: Mini teste da Aula 01"
                            class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-[#F8FBF8] text-[#003C2F] text-sm placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#60756B] uppercase tracking-wider mb-1.5">
                            Tempo limite
                        </label>

                        <input
                            type="number"
                            name="avaliacao[tempo_limite]"
                            value="{{ old('avaliacao.tempo_limite') }}"
                            placeholder="Tempo em minutos"
                            min="1"
                            class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-[#F8FBF8] text-[#003C2F] text-sm placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition"
                        >
                    </div>

                </div>

                <!-- PERGUNTAS -->
                <div id="perguntas-container" class="space-y-4 mb-5"></div>

                <!-- BOTÕES -->
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-between sm:items-center pt-2">

                    <button
                        type="button"
                        onclick="addPergunta()"
                        class="flex items-center justify-center gap-2 px-4 py-3 rounded-2xl border border-[#00A63E] text-[#004D3A] text-sm font-extrabold hover:bg-[#EAF5EF] transition"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2.5"
                                  d="M12 4v16m8-8H4"/>
                        </svg>

                        Pergunta
                    </button>

                    <div class="flex flex-col sm:flex-row gap-3 justify-end">

                        <button
                            type="button"
                            onclick="fecharModalAula()"
                            class="px-6 py-3 rounded-2xl border border-[#DCE7DE] text-[#60756B] text-sm font-bold hover:bg-[#F8FBF8] transition"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            id="btnSalvarAula"
                            class="px-6 py-3 rounded-2xl bg-[#004D3A] hover:bg-[#003C2F] text-white text-sm font-extrabold transition shadow-sm disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            Salvar Aula
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

</div>

<!-- MODAL EDITAR AULA -->
<div id="modalEditarAula" class="fixed inset-0 hidden items-center justify-center z-50"
     style="background: rgba(0,0,0,0.45); backdrop-filter: blur(4px);">

    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden"
         style="max-height: 90vh; overflow-y: auto;">

        <div class="flex items-start justify-between px-5 sm:px-8 pt-8 pb-4">

            <div>
                <h2 class="text-2xl font-extrabold text-[#003C2F]">
                    Editar Conteúdo da Aula
                </h2>

                <p class="text-sm text-[#60756B] mt-1">
                    Atualize título, descrição, vídeo e módulo da aula.
                </p>
            </div>

            <button
                type="button"
                onclick="fecharModalEditarAula()"
                class="w-10 h-10 rounded-xl bg-[#F1F6F2] text-[#003C2F] flex items-center justify-center hover:bg-[#E6EFE8] transition"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

        </div>

        <div class="px-5 sm:px-8 pb-8">

            <form method="POST" id="formEditarAula">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block text-xs font-bold text-[#60756B] uppercase tracking-wider mb-1.5">
                        Módulo
                    </label>

                    <select
                        id="edit_modulo_id"
                        name="modulo_id"
                        class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-[#F8FBF8] text-[#003C2F] text-sm focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition cursor-pointer"
                    >
                        @foreach ($modulos as $modulo)
                            <option value="{{ $modulo->id }}">
                                {{ $modulo->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-[#60756B] uppercase tracking-wider mb-1.5">
                        Título da aula
                    </label>

                    <input
                        type="text"
                        id="edit_titulo"
                        name="titulo"
                        required
                        class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-[#F8FBF8] text-[#003C2F] text-sm placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition"
                    >
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-[#60756B] uppercase tracking-wider mb-1.5">
                        Descrição
                    </label>

                    <textarea
                        id="edit_descricao"
                        name="descricao"
                        rows="4"
                        class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-[#F8FBF8] text-[#003C2F] text-sm placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition resize-none"
                    ></textarea>
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-bold text-[#60756B] uppercase tracking-wider mb-1.5">
                        Link do vídeo
                    </label>

                    <input
                        type="text"
                        id="edit_video_url"
                        name="video_url"
                        required
                        class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-[#F8FBF8] text-[#003C2F] text-sm placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition"
                    >
                </div>

                <div class="flex flex-col sm:flex-row justify-end gap-3">

                    <button
                        type="button"
                        onclick="fecharModalEditarAula()"
                        class="px-6 py-3 rounded-2xl border border-[#DCE7DE] text-[#60756B] text-sm font-bold hover:bg-[#F8FBF8] transition"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        id="btnAtualizarAula"
                        class="px-6 py-3 rounded-2xl bg-[#004D3A] hover:bg-[#003C2F] text-white text-sm font-extrabold transition shadow-sm disabled:opacity-60 disabled:cursor-not-allowed"
                    >
                        Salvar Alterações
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let perguntaIndex = 0;

    // ─── Modal Criar Aula ─────────────────────────────────────────────────────

    function abrirModalAula() {
        const modal = document.getElementById('modalAula');

        if (!modal) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function fecharModalAula() {
        const modal = document.getElementById('modalAula');

        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        const form = document.getElementById('formAula');
        const perguntasContainer = document.getElementById('perguntas-container');
        const btnSalvar = document.getElementById('btnSalvarAula');

        if (form) form.reset();
        if (perguntasContainer) perguntasContainer.innerHTML = '';

        if (btnSalvar) {
            btnSalvar.disabled = false;
            btnSalvar.innerText = 'Salvar Aula';
        }

        perguntaIndex = 0;
    }

    const modalAula = document.getElementById('modalAula');

    if (modalAula) {
        modalAula.addEventListener('click', function (e) {
            if (e.target === this) fecharModalAula();
        });
    }

    const formAula = document.getElementById('formAula');

    if (formAula) {
        formAula.addEventListener('submit', function () {
            const btnSalvar = document.getElementById('btnSalvarAula');

            if (btnSalvar) {
                btnSalvar.disabled = true;
                btnSalvar.innerText = 'Salvando...';
            }
        });
    }

    // ─── Modal Editar Aula ────────────────────────────────────────────────────

    function abrirModalEditarAula(id, titulo, descricao, videoUrl, moduloId) {
        const modal = document.getElementById('modalEditarAula');
        const form = document.getElementById('formEditarAula');

        if (!modal || !form) return;

        document.getElementById('edit_titulo').value = titulo ?? '';
        document.getElementById('edit_descricao').value = descricao ?? '';
        document.getElementById('edit_video_url').value = videoUrl ?? '';
        document.getElementById('edit_modulo_id').value = moduloId ?? '';

        form.action = '/aulas/' + id;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function fecharModalEditarAula() {
        const modal = document.getElementById('modalEditarAula');
        const form = document.getElementById('formEditarAula');
        const btn = document.getElementById('btnAtualizarAula');

        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        if (form) form.reset();

        if (btn) {
            btn.disabled = false;
            btn.innerText = 'Salvar Alterações';
        }
    }

    const modalEditarAula = document.getElementById('modalEditarAula');

    if (modalEditarAula) {
        modalEditarAula.addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalEditarAula();
            }
        });
    }

    const formEditarAula = document.getElementById('formEditarAula');

    if (formEditarAula) {
        formEditarAula.addEventListener('submit', function () {
            const btn = document.getElementById('btnAtualizarAula');

            if (btn) {
                btn.disabled = true;
                btn.innerText = 'Salvando...';
            }
        });
    }

    // ─── Módulos ──────────────────────────────────────────────────────────────

    function toggleModulo(id) {
        const conteudo = document.getElementById(`modulo-${id}`);
        const icone = document.getElementById(`icon-${id}`);

        if (!conteudo) return;

        const aberto = !conteudo.classList.contains('hidden');

        conteudo.classList.toggle('hidden', aberto);

        if (icone) {
            icone.style.transform = aberto ? 'rotate(0deg)' : 'rotate(180deg)';
        }
    }

    // ─── Perguntas ────────────────────────────────────────────────────────────

    function addPergunta() {
        const container = document.getElementById('perguntas-container');

        if (!container) return;

        const div = document.createElement('div');

        div.className = 'border border-[#DCE7DE] rounded-3xl p-4 bg-[#F8FBF8]';
        div.id = `pergunta-${perguntaIndex}`;

        div.innerHTML = `
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-[#60756B] uppercase tracking-widest bg-white border border-[#DCE7DE] px-3 py-1 rounded-xl">
                    Pergunta ${perguntaIndex + 1}
                </span>

                <button type="button" onclick="removerPergunta(${perguntaIndex})"
                    class="text-red-400 hover:text-red-600 transition" title="Remover">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>

            <input
                type="text"
                name="perguntas[${perguntaIndex}][pergunta]"
                placeholder="Digite o enunciado da questão aqui..."
                class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-white text-[#003C2F] text-sm placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition mb-3"
            >

            <p class="text-xs font-bold text-[#60756B] uppercase tracking-wider mb-2">
                Alternativas — marque a correta
            </p>

            <div id="respostas-${perguntaIndex}" class="space-y-2 mb-3"></div>

            <button type="button" onclick="addResposta(${perguntaIndex})"
                class="flex items-center gap-1 text-xs font-bold text-[#004D3A] hover:text-[#003C2F] transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M12 4v16m8-8H4" />
                </svg>
                Adicionar alternativa
            </button>
        `;

        container.appendChild(div);

        addResposta(perguntaIndex);
        addResposta(perguntaIndex);
        addResposta(perguntaIndex);
        addResposta(perguntaIndex);

        perguntaIndex++;
    }

    function removerPergunta(index) {
        const pergunta = document.getElementById(`pergunta-${index}`);

        if (pergunta) pergunta.remove();
    }

    const letras = ['A', 'B', 'C', 'D', 'E'];

    function addResposta(index) {
        const container = document.getElementById(`respostas-${index}`);

        if (!container) return;

        const total = container.children.length;
        const letra = letras[total] ?? String(total + 1);

        const div = document.createElement('div');

        div.id = `resposta-${index}-${total}`;
        div.className = 'flex items-center gap-3 bg-white border border-[#DCE7DE] rounded-2xl px-4 py-3';

        div.innerHTML = `
            <input type="radio" name="perguntas[${index}][correta]" value="${total}"
                class="w-4 h-4 accent-[#004D3A] cursor-pointer">

            <span class="text-xs font-bold text-[#60756B] w-4">${letra}</span>

            <input
                type="text"
                name="perguntas[${index}][respostas][]"
                placeholder="Texto da alternativa ${letra}..."
                class="flex-1 text-sm text-[#003C2F] bg-transparent placeholder-[#8A9B92] focus:outline-none"
            >

            <button type="button" onclick="removerResposta(${index}, ${total})"
                class="text-gray-300 hover:text-red-500 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        `;

        container.appendChild(div);
    }

    function removerResposta(perguntaIndex, respostaIndex) {
        const resposta = document.getElementById(`resposta-${perguntaIndex}-${respostaIndex}`);

        if (resposta) resposta.remove();
    }

    // ─── Exclusão bonita com SweetAlert ───────────────────────────────────────

    function confirmarExclusaoAula(btn, titulo) {
        Swal.fire({
            title: 'Excluir aula?',
            html: `
                <div style="text-align:center;">
                    <p style="color:#475569; margin-bottom:10px;">
                        Você está prestes a excluir:
                    </p>
                    <strong style="color:#003C2F; font-size:16px;">
                        ${titulo ?? 'esta aula'}
                    </strong>
                    <p style="color:#ef4444; margin-top:14px; font-size:14px;">
                        Essa ação não poderá ser desfeita.
                    </p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            background: '#ffffff',
            color: '#003C2F',
            customClass: {
                popup: 'rounded-3xl',
                confirmButton: 'rounded-xl',
                cancelButton: 'rounded-xl'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                btn.closest('form').submit();
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            fecharModalAula();
            fecharModalEditarAula();
        }
    });

    @if ($errors->any() || session('error'))
        abrirModalAula();
    @endif
</script>

@endsection