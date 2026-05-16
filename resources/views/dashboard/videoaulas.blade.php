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

    /*
    |--------------------------------------------------------------------------
    | MODAIS RESPONSIVOS
    |--------------------------------------------------------------------------
    | A sidebar/topbar usam z-index alto. Por isso os modais precisam ficar
    | acima deles para não aparecerem atrás do menu.
    */
    .modal-videoaulas-overlay {
        z-index: 10050 !important;
    }

    .modal-videoaulas-card {
        width: min(100%, 980px);
        max-height: calc(100vh - 56px);
        overflow-y: auto;
        overscroll-behavior: contain;
    }

    body.modal-aberto {
        overflow: hidden;
    }

    @media (max-width: 768px) {
        .modal-scroll-mobile,
        .modal-videoaulas-card {
            width: calc(100vw - 24px) !important;
            max-height: calc(100vh - 32px) !important;
            overflow-y: auto !important;
            border-radius: 1.5rem !important;
        }

        .area-professor-videoaulas {
            padding-top: 5rem !important;
        }
    }
</style>

<div class="flex min-h-screen w-full bg-[#F3F7F3] text-[#003C2F] overflow-x-hidden">

    @include('partials.sidebar-professor')

    <main class="flex-1 min-w-0 w-full bg-[#F3F7F3] overflow-x-hidden">

        @include('partials.navbar')

        <section class="area-professor-videoaulas p-4 sm:p-6 lg:p-8">

            <!-- CABEÇALHO -->
            <div class="mb-7 flex flex-col xl:flex-row xl:items-end xl:justify-between gap-5">

                <div class="min-w-0">
                    <div class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest text-[#00A63E] mb-2">
                        <span class="w-2 h-2 rounded-full bg-[#00A63E]"></span>
                        Administração de conteúdo
                    </div>

                    <h1 class="text-2xl sm:text-4xl font-extrabold text-[#003C2F] tracking-tight break-words">
                        Gerenciamento de Videoaulas
                    </h1>

                    <p class="text-sm text-[#60756B] mt-2 max-w-3xl">
                        Organize o curso do período com módulos, aulas e pós-testes. O aluno verá apenas o curso publicado/atual no ambiente dele.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 w-full xl:w-auto">

                    <a href="{{ route('biblioteca.cursos') }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-white text-[#004D3A] border border-[#DCE7DE] px-5 py-3 rounded-2xl shadow-sm hover:bg-[#F8FBF8] transition text-sm font-bold">
                        Biblioteca de Cursos
                    </a>

                    <button type="button"
                            onclick="abrirModalAula()"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-[#004D3A] text-white px-5 py-3 rounded-2xl shadow-sm hover:bg-[#003C2F] transition text-sm font-bold">
                        Nova Aula
                    </button>

                </div>

            </div>

            <!-- ALERTAS -->
            @if(session('success'))
                <div class="mb-5 bg-green-100 text-green-700 px-4 py-3 rounded-2xl border border-green-200 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-5 bg-red-100 text-red-700 px-4 py-3 rounded-2xl border border-red-200 shadow-sm break-words">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 bg-red-100 text-red-700 px-4 py-3 rounded-2xl border border-red-200 shadow-sm">
                    <p class="font-extrabold mb-2">Corrija os campos abaixo:</p>

                    <ul class="list-disc pl-5 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- PESQUISA AO VIVO -->
            <div class="bg-white border border-[#E3EBE4] rounded-3xl shadow-sm p-4 sm:p-5 mb-7">
                <div class="flex flex-col lg:flex-row lg:items-center gap-4">

                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-5 flex items-center text-[#8A9B92]">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-6 h-6"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0z"/>
                            </svg>
                        </span>

                        <input type="text"
                               id="pesquisaVideoaulas"
                               oninput="pesquisarVideoaulasAoVivo()"
                               placeholder="Pesquisar como no WhatsApp: curso, módulo, aula, teste..."
                               autocomplete="off"
                               class="w-full h-14 pl-14 pr-12 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-bold placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition">

                        <button type="button"
                                onclick="limparPesquisaVideoaulas()"
                                class="absolute inset-y-0 right-4 hidden items-center text-[#8A9B92] hover:text-[#003C2F]"
                                id="btnLimparPesquisaVideoaulas">
                            ✕
                        </button>
                    </div>

                    <div class="bg-[#EAF5EF] text-[#004D3A] px-4 py-3 rounded-2xl text-sm font-extrabold whitespace-nowrap text-center">
                        <span id="contadorPesquisaVideoaulas">{{ $totalAulas }}</span> resultado(s)
                    </div>

                </div>
            </div>

            <!-- CURSO SELECIONADO -->
            <div class="bg-white border border-[#E3EBE4] rounded-3xl shadow-sm p-5 sm:p-6 mb-7">

                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">

                    <div class="min-w-0">
                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                            Curso em edição
                        </p>

                        <h2 class="text-xl sm:text-2xl font-extrabold text-[#003C2F] mt-1 break-words">
                            {{ $cursoAtual->nome ?? 'Nenhum curso selecionado' }}
                        </h2>

                        <p class="text-sm text-[#60756B] mt-2 max-w-3xl break-words">
                            {{ $cursoAtual->descricao ?? 'Este é o curso que você está editando. No ambiente do aluno, ele verá somente o curso definido/publicado para o período.' }}
                        </p>
                    </div>

                    <form method="GET" action="{{ route('videoaulas') }}" class="w-full lg:w-[380px]">
                        <label class="block text-xs font-bold text-[#60756B] uppercase tracking-wider mb-1.5">
                            Curso para gerenciar
                        </label>

                        <select name="curso_id"
                                onchange="this.form.submit()"
                                class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-[#F8FBF8] text-[#003C2F] text-sm focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition cursor-pointer">
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

            <!-- REGRA DO CURSO DO PERÍODO -->
            <div class="bg-[#EAF5EF] border border-[#DCE7DE] rounded-3xl shadow-sm p-5 sm:p-6 mb-7">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-[11px] uppercase tracking-widest text-[#004D3A] font-extrabold">
                            Regra do sistema
                        </p>

                        <h3 class="text-lg sm:text-xl font-extrabold text-[#003C2F] mt-1 break-words">
                            1 curso por período • módulos dentro do curso • aulas dentro dos módulos
                        </h3>

                        <p class="text-sm text-[#60756B] mt-2 leading-relaxed">
                            O aluno não escolhe curso no ambiente dele. Ele verá automaticamente o curso publicado/atual do período.
                            A prova final deve ser liberada somente quando o aluno concluir 70% do curso completo, e o certificado somente com 70% ou mais na prova final.
                        </p>
                    </div>

                    <div class="bg-white border border-[#DCE7DE] rounded-2xl px-4 py-3 text-sm font-extrabold text-[#004D3A] shrink-0 text-center">
                        Curso atual:
                        <br>
                        <span class="text-[#003C2F]">{{ $cursoAtual->nome ?? 'Não definido' }}</span>
                    </div>
                </div>
            </div>

            <!-- RESUMO RESPONSIVO -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-7">

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
                    <p class="text-xs text-[#60756B] font-semibold">Pós-testes</p>
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

                        <div class="modulo-pesquisa bg-white border border-[#E3EBE4] rounded-3xl shadow-sm overflow-hidden"
                             data-search="{{ strtolower(($modulo->nome ?? '') . ' ' . ($cursoAtual->nome ?? '')) }}">

                            <!-- CABEÇALHO DO MÓDULO -->
                            <div onclick="toggleModulo({{ $modulo->id }})"
                                 class="cursor-pointer p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 hover:bg-[#F8FBF8] transition">

                                <div class="flex items-start gap-4">

                                    <div class="w-12 h-12 rounded-2xl bg-[#004D3A] text-white flex items-center justify-center font-extrabold shrink-0">
                                        {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                    </div>

                                    <div class="min-w-0">
                                        <h2 class="text-lg sm:text-xl font-extrabold text-[#003C2F] break-words">
                                            {{ $modulo->nome }}
                                        </h2>

                                        <p class="text-xs sm:text-sm text-[#60756B] mt-1">
                                            {{ $totalAulasModulo }} aula(s) • {{ $totalTestesModulo }} pós-teste(s)
                                        </p>
                                    </div>

                                </div>

                                <div class="flex items-center gap-3">

                                    <span class="text-[11px] bg-green-100 text-green-700 px-3 py-1 rounded-full font-bold">
                                        {{ $totalAulasModulo > 0 ? 'PUBLICADO' : 'VAZIO' }}
                                    </span>

                                    <span id="icon-{{ $modulo->id }}" class="text-[#60756B] transition-transform duration-300">
                                        ▼
                                    </span>

                                </div>

                            </div>

                            <!-- AULAS DO MÓDULO -->
                            <div id="modulo-{{ $modulo->id }}" class="{{ $loop->first ? '' : 'hidden' }} px-4 sm:px-6 pb-6 space-y-4">

                                @forelse ($aulasDoModulo as $aulaIndex => $aula)

                                    @php
                                        $avaliacaoMiniTeste = DB::table('avaliacoes')
                                            ->where('aula_id', $aula->id)
                                            ->where(function ($query) {
                                                $query->where('tipo', 'normal')
                                                      ->orWhereNull('tipo');
                                            })
                                            ->first();

                                        $temMiniTeste = $avaliacaoMiniTeste ? true : false;

                                        $perguntasMiniTeste = collect();

                                        if ($avaliacaoMiniTeste) {
                                            $perguntasMiniTeste = DB::table('perguntas')
                                                ->where('avaliacao_id', $avaliacaoMiniTeste->id)
                                                ->orderBy('id')
                                                ->get();

                                            foreach ($perguntasMiniTeste as $perguntaMini) {
                                                $perguntaMini->respostas = DB::table('respostas')
                                                    ->where('pergunta_id', $perguntaMini->id)
                                                    ->orderBy('id')
                                                    ->get();
                                            }
                                        }
                                    @endphp

                                    <div class="aula-pesquisa bg-[#F8FBF8] border border-[#E3EBE4] rounded-3xl p-4 transition hover:shadow-md"
                                         data-search="{{ strtolower(($aula->titulo ?? '') . ' ' . ($aula->descricao ?? '') . ' ' . ($aula->video_url ?? '') . ' ' . ($modulo->nome ?? '') . ' ' . ($cursoAtual->nome ?? '') . ' ' . ($temMiniTeste ? 'pós-teste pos teste pós teste posteste avaliação avaliacao' : 'sem teste')) }}">

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
                                                            Pós-teste cadastrado
                                                        </span>
                                                    @else
                                                        <span class="text-[11px] font-bold bg-gray-100 text-gray-600 px-2.5 py-1 rounded-lg">
                                                            Sem pós-teste
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

                                                <button type="button"
                                                        data-id="{{ $aula->id }}"
                                                        data-titulo='@json($aula->titulo)'
                                                        data-descricao='@json($aula->descricao)'
                                                        data-video='@json($aula->video_url)'
                                                        data-modulo="{{ $aula->modulo_id }}"
                                                        onclick="abrirModalEditarAulaPeloBotao(this)"
                                                        class="inline-flex items-center justify-center gap-2 bg-white text-[#004D3A] border border-[#DCE7DE] px-4 py-3 rounded-2xl text-sm font-bold hover:bg-[#EAF5EF] hover:border-[#00A63E]/40 transition">
                                                    Editar Conteúdo
                                                </button>

                                                <button type="button"
                                                        onclick='abrirModalMiniTeste(
                                                            @json($aula->id),
                                                            @json($cursoAtual->id ?? null),
                                                            @json($aula->titulo),
                                                            @json($avaliacaoMiniTeste),
                                                            @json($perguntasMiniTeste)
                                                        )'
                                                        class="inline-flex items-center justify-center gap-2
                                                            {{ $temMiniTeste
                                                                ? 'bg-blue-50 text-blue-700 border border-blue-100 hover:bg-blue-100'
                                                                : 'bg-[#EAF5EF] text-[#004D3A] border border-[#DCE7DE] hover:bg-[#DCE7DE]'
                                                            }}
                                                            px-4 py-3 rounded-2xl text-sm font-bold transition">
                                                    {{ $temMiniTeste ? 'Editar Pós-teste' : 'Criar Pós-teste' }}
                                                </button>

                                                <form action="{{ route('aulas.destroy', $aula->id) }}"
                                                      method="POST"
                                                      class="w-full form-excluir-aula">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="button"
                                                            onclick='confirmarExclusaoAula(this, @json($aula->titulo))'
                                                            class="w-full inline-flex items-center justify-center gap-2 bg-red-50 text-red-600 border border-red-100 px-4 py-3 rounded-2xl text-sm font-bold hover:bg-red-100 transition">
                                                        Excluir
                                                    </button>
                                                </form>

                                            </div>

                                        </div>

                                    </div>

                                @empty

                                    <div class="bg-[#F8FBF8] border border-dashed border-[#AFC5B5] rounded-3xl p-8 text-center">
                                        <div class="w-14 h-14 rounded-full bg-[#EAF5EF] mx-auto mb-4 flex items-center justify-center text-[#004D3A]">
                                            +
                                        </div>

                                        <h3 class="font-extrabold text-[#003C2F]">
                                            Nenhuma aula neste módulo
                                        </h3>

                                        <p class="text-sm text-[#60756B] mt-1">
                                            Adicione uma aula para começar a organizar este módulo.
                                        </p>
                                    </div>

                                @endforelse

                                <button type="button"
                                        onclick="abrirModalAula()"
                                        class="w-full border border-dashed border-[#AFC5B5] text-[#004D3A] rounded-3xl py-4 text-sm font-extrabold hover:bg-[#F8FBF8] transition flex items-center justify-center gap-2">
                                    Adicionar Nova Aula ao Módulo
                                </button>

                            </div>

                        </div>

                    @empty

                        <div class="bg-white rounded-3xl border border-[#E3EBE4] shadow-sm p-8 text-center">
                            <div class="w-16 h-16 rounded-full bg-[#EAF5EF] mx-auto mb-4 flex items-center justify-center text-[#004D3A]">
                                +
                            </div>

                            <h3 class="font-extrabold text-xl text-[#003C2F] mb-2">
                                Nenhum módulo encontrado
                            </h3>

                            <p class="text-[#60756B] text-sm mb-5">
                                Clique em <strong>Nova Aula</strong> e preencha o campo “Novo módulo”.
                            </p>

                            <button type="button"
                                    onclick="abrirModalAula()"
                                    class="bg-[#004D3A] text-white px-5 py-3 rounded-2xl hover:bg-[#003C2F] transition text-sm font-bold">
                                Criar primeira aula
                            </button>
                        </div>

                    @endforelse

                </div>

                <!-- PAINEL DIREITO -->
                <aside class="xl:col-span-4 space-y-5">

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
                                Pós-testes
                            </p>

                            <h3 class="text-3xl font-extrabold mt-2 text-[#003C2F]">
                                {{ $totalMiniTestes }}
                            </h3>

                            <p class="text-xs text-[#60756B] mt-2">
                                {{ $totalAulasComTeste }} aula(s) com teste.
                            </p>
                        </div>
                    </div>

                    <div class="bg-white border border-[#E3EBE4] rounded-3xl p-5 shadow-sm">
                        <div class="flex items-start gap-3 mb-4">
                            <div class="w-11 h-11 rounded-2xl bg-[#EAF5EF] text-[#004D3A] flex items-center justify-center shrink-0">
                                📚
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

                    <div class="bg-white border border-[#E3EBE4] rounded-3xl p-5 shadow-sm">
                        <div class="flex items-start gap-3 mb-4">
                            <div class="w-11 h-11 rounded-2xl bg-[#EAF5EF] text-[#004D3A] flex items-center justify-center shrink-0">
                                🎥
                            </div>

                            <div>
                                <h2 class="font-extrabold text-lg text-[#003C2F]">
                                    Conteúdos
                                </h2>

                                <p class="text-xs text-[#60756B] mt-1">
                                    Gerencie aulas, módulos e pós-testes deste curso.
                                </p>
                            </div>
                        </div>

                        <button type="button"
                                onclick="abrirModalAula()"
                                class="w-full inline-flex items-center justify-center bg-[#004D3A] text-white rounded-2xl px-4 py-3 text-sm font-extrabold hover:bg-[#003C2F] transition">
                            Criar nova aula
                        </button>
                    </div>

                </aside>

            </div>

        </section>

    </main>

</div>


<!-- MODAL CRIAR AULA -->
<div id="modalAula" class="modal-videoaulas-overlay fixed inset-0 hidden items-center justify-center px-3 sm:px-4 py-4"
     style="background: rgba(0,0,0,0.55); backdrop-filter: blur(4px);">

    <div class="modal-videoaulas-card bg-white rounded-3xl shadow-2xl mx-auto overflow-hidden modal-scroll-mobile">

        <div class="flex items-start justify-between px-5 sm:px-8 pt-8 pb-4">
            <div>
                <h2 class="text-2xl font-extrabold text-[#003C2F]">
                    Criar Aula Completa
                </h2>

                <p class="text-sm text-[#60756B] mt-1">
                    Cadastre ou atualize o curso do período, seus módulos, aulas e o pós-teste de cada aula.
                </p>
            </div>

            <button type="button"
                    onclick="fecharModalAula()"
                    class="w-10 h-10 rounded-xl bg-[#F1F6F2] text-[#003C2F] flex items-center justify-center hover:bg-[#E6EFE8] transition shrink-0">
                ✕
            </button>
        </div>

        <div class="px-5 sm:px-8 pb-8">

            <form action="{{ route('aulas.store') }}" method="POST" id="formAula">
                @csrf

                <div class="mb-5 border border-[#E3EBE4] rounded-3xl p-4 bg-[#F8FBF8]">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#60756B] uppercase tracking-wider mb-1.5">
                                Curso existente
                            </label>

                            <select name="curso_id"
                                    class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-white text-[#003C2F] text-sm focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition cursor-pointer">
                                <option value="">Selecionar curso</option>

                                @foreach ($cursos as $curso)
                                    <option value="{{ $curso->id }}" {{ old('curso_id', $cursoAtualId) == $curso->id ? 'selected' : '' }}>
                                        {{ $curso->nome }}
                                    </option>
                                @endforeach
                            </select>

                            <p class="text-xs text-[#8A9B92] mt-1">
                                Use para adicionar a aula ao curso do período já cadastrado.
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#60756B] uppercase tracking-wider mb-1.5">
                                Novo curso
                            </label>

                            <input type="text"
                                   name="novo_curso"
                                   value="{{ old('novo_curso') }}"
                                   placeholder="Ou criar novo curso"
                                   class="w-full px-4 py-3 rounded-2xl border border-dashed border-[#00A63E] bg-[#EAF5EF] text-[#003C2F] text-sm placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition">

                            <p class="text-xs text-[#8A9B92] mt-1">
                                Use somente quando for iniciar um novo curso/período.
                            </p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-xs font-bold text-[#60756B] uppercase tracking-wider mb-1.5">
                            Descrição do novo curso
                        </label>

                        <textarea name="descricao_curso"
                                  placeholder="Opcional: descreva o objetivo do novo curso..."
                                  rows="2"
                                  class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-white text-[#003C2F] text-sm placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition resize-none">{{ old('descricao_curso') }}</textarea>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-[#60756B] uppercase tracking-wider mb-1.5">
                            Módulo existente
                        </label>

                        <select name="modulo_id"
                                class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-[#F8FBF8] text-[#003C2F] text-sm focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition cursor-pointer">
                            <option value="">Selecionar módulo</option>

                            @foreach ($modulos as $modulo)
                                <option value="{{ $modulo->id }}" {{ old('modulo_id') == $modulo->id ? 'selected' : '' }}>
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

                        <input type="text"
                               name="novo_modulo"
                               value="{{ old('novo_modulo') }}"
                               placeholder="Ou criar novo módulo"
                               class="w-full px-4 py-3 rounded-2xl border border-dashed border-[#00A63E] bg-[#EAF5EF] text-[#003C2F] text-sm placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition">

                        <p class="text-xs text-[#8A9B92] mt-1">
                            Use se ainda não existir módulo.
                        </p>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-[#60756B] uppercase tracking-wider mb-1.5">
                        Título da aula
                    </label>

                    <input type="text"
                           name="titulo"
                           value="{{ old('titulo') }}"
                           placeholder="Ex: Aula 01: Introdução aos Sistemas de Saúde"
                           required
                           class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-[#F8FBF8] text-[#003C2F] text-sm placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition">
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-[#60756B] uppercase tracking-wider mb-1.5">
                        Descrição
                    </label>

                    <textarea name="descricao"
                              placeholder="Descreva brevemente o conteúdo da aula..."
                              rows="3"
                              class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-[#F8FBF8] text-[#003C2F] text-sm placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition resize-none">{{ old('descricao') }}</textarea>
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-bold text-[#60756B] uppercase tracking-wider mb-1.5">
                        Link do vídeo
                    </label>

                    <input type="text"
                           name="video_url"
                           value="{{ old('video_url') }}"
                           placeholder="Cole aqui o link do YouTube ou embed"
                           required
                           class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-[#F8FBF8] text-[#003C2F] text-sm placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition">
                </div>

                <div class="mb-4 border-t border-[#DCE7DE] pt-5">
                    <h3 class="font-extrabold text-[#003C2F]">
                        Pós-teste
                    </h3>

                    <p class="text-xs text-[#60756B] mt-1">
                        Opcional. Você pode criar perguntas novas ou importar perguntas antigas.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                    <div>
                        <label class="block text-xs font-bold text-[#60756B] uppercase tracking-wider mb-1.5">
                            Título do pós-teste
                        </label>

                        <input type="text"
                               name="avaliacao[titulo]"
                               value="{{ old('avaliacao.titulo') }}"
                               placeholder="Ex: Pós-teste da Aula 01"
                               class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-[#F8FBF8] text-[#003C2F] text-sm placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#60756B] uppercase tracking-wider mb-1.5">
                            Tempo limite
                        </label>

                        <input type="number"
                               name="avaliacao[tempo_limite]"
                               value="{{ old('avaliacao.tempo_limite') }}"
                               placeholder="Tempo em minutos"
                               min="1"
                               class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-[#F8FBF8] text-[#003C2F] text-sm placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition">
                    </div>
                </div>

                <div class="mb-5">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                        <div>
                            <h4 class="font-extrabold text-[#003C2F]">
                                Perguntas importadas
                            </h4>

                            <p class="text-xs text-[#60756B]">
                                Escolha perguntas já utilizadas anteriormente.
                            </p>
                        </div>

                        <button type="button"
                                onclick="abrirBancoPerguntas()"
                                class="bg-[#EAF5EF] text-[#004D3A] px-4 py-3 rounded-2xl font-extrabold hover:bg-[#DCE7DE] transition text-sm">
                            Buscar perguntas antigas
                        </button>
                    </div>

                    <div id="perguntasImportadasContainer" class="space-y-2"></div>
                </div>

                <div id="perguntas-container" class="space-y-4 mb-5"></div>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-between sm:items-center pt-2">

                    <button type="button"
                            onclick="addPergunta()"
                            class="flex items-center justify-center gap-2 px-4 py-3 rounded-2xl border border-[#00A63E] text-[#004D3A] text-sm font-extrabold hover:bg-[#EAF5EF] transition">
                        Pergunta nova
                    </button>

                    <div class="flex flex-col sm:flex-row gap-3 justify-end">

                        <button type="button"
                                onclick="fecharModalAula()"
                                class="px-6 py-3 rounded-2xl border border-[#DCE7DE] text-[#60756B] text-sm font-bold hover:bg-[#F8FBF8] transition">
                            Cancelar
                        </button>

                        <button type="submit"
                                id="btnSalvarAula"
                                class="px-6 py-3 rounded-2xl bg-[#004D3A] hover:bg-[#003C2F] text-white text-sm font-extrabold transition shadow-sm disabled:opacity-60 disabled:cursor-not-allowed">
                            Salvar Aula
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL BANCO DE PERGUNTAS -->
<div id="modalBancoPerguntas"
     class="modal-videoaulas-overlay fixed inset-0 hidden items-center justify-center px-3 sm:px-4 py-4"
     style="background: rgba(0,0,0,0.58); backdrop-filter: blur(4px);">

    <div class="modal-videoaulas-card bg-white rounded-3xl shadow-2xl overflow-hidden modal-scroll-mobile" style="width: min(100%, 1100px);">

        <div class="flex items-start justify-between px-5 sm:px-8 pt-8 pb-4 border-b border-[#E3EBE4]">
            <div>
                <h2 class="text-2xl font-extrabold text-[#003C2F]">
                    Banco de Perguntas
                </h2>

                <p class="text-sm text-[#60756B] mt-1">
                    Pesquise perguntas já utilizadas e importe para o novo pós-teste.
                </p>
            </div>

            <button type="button"
                    onclick="fecharBancoPerguntas()"
                    class="w-10 h-10 rounded-xl bg-[#F1F6F2] text-[#003C2F] flex items-center justify-center hover:bg-[#E6EFE8] transition shrink-0">
                ✕
            </button>
        </div>

        <div class="p-5 sm:p-8">

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 mb-5">
                <div class="lg:col-span-6">
                    <label class="block text-xs font-bold text-[#60756B] uppercase tracking-wider mb-1.5">
                        Pesquisar pergunta
                    </label>

                    <input type="text"
                           id="pesquisaBancoPerguntas"
                           placeholder="Pesquisar por pergunta, curso, módulo ou aula..."
                           class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-[#F8FBF8] text-[#003C2F] text-sm placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition">
                </div>

                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold text-[#60756B] uppercase tracking-wider mb-1.5">
                        Data inicial
                    </label>

                    <input type="date"
                           id="dataInicioBancoPerguntas"
                           class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-[#F8FBF8] text-[#003C2F] text-sm focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition">
                </div>

                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold text-[#60756B] uppercase tracking-wider mb-1.5">
                        Data final
                    </label>

                    <input type="date"
                           id="dataFimBancoPerguntas"
                           class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-[#F8FBF8] text-[#003C2F] text-sm focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition">
                </div>

                <div class="lg:col-span-2 flex items-end">
                    <button type="button"
                            onclick="buscarPerguntasAntigas()"
                            class="w-full bg-[#004D3A] text-white px-4 py-3 rounded-2xl font-extrabold hover:bg-[#003C2F] transition">
                        Buscar
                    </button>
                </div>
            </div>

            <div id="listaBancoPerguntas" class="space-y-3">
                <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-3xl p-8 text-center text-[#60756B]">
                    Clique em buscar para carregar perguntas antigas.
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button type="button"
                        onclick="fecharBancoPerguntas()"
                        class="bg-[#004D3A] text-white px-6 py-3 rounded-2xl font-extrabold hover:bg-[#003C2F] transition">
                    Concluir seleção
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EDITAR AULA -->
<div id="modalEditarAula" class="modal-videoaulas-overlay fixed inset-0 hidden items-center justify-center px-3 sm:px-4 py-4"
     style="background: rgba(0,0,0,0.55); backdrop-filter: blur(4px);">

    <div class="modal-videoaulas-card bg-white rounded-3xl shadow-2xl mx-auto overflow-hidden modal-scroll-mobile" style="width: min(100%, 760px);">

        <div class="flex items-start justify-between px-5 sm:px-8 pt-8 pb-4">
            <div>
                <h2 class="text-2xl font-extrabold text-[#003C2F]">
                    Editar Conteúdo da Aula
                </h2>

                <p class="text-sm text-[#60756B] mt-1">
                    Atualize título, descrição, vídeo e módulo da aula.
                </p>
            </div>

            <button type="button"
                    onclick="fecharModalEditarAula()"
                    class="w-10 h-10 rounded-xl bg-[#F1F6F2] text-[#003C2F] flex items-center justify-center hover:bg-[#E6EFE8] transition shrink-0">
                ✕
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

                    <select id="edit_modulo_id"
                            name="modulo_id"
                            class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-[#F8FBF8] text-[#003C2F] text-sm focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition cursor-pointer">
                        @foreach ($modulos as $modulo)
                            <option value="{{ $modulo->id }}">{{ $modulo->nome }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-[#60756B] uppercase tracking-wider mb-1.5">
                        Título da aula
                    </label>

                    <input type="text"
                           id="edit_titulo"
                           name="titulo"
                           required
                           class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-[#F8FBF8] text-[#003C2F] text-sm placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition">
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-[#60756B] uppercase tracking-wider mb-1.5">
                        Descrição
                    </label>

                    <textarea id="edit_descricao"
                              name="descricao"
                              rows="4"
                              class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-[#F8FBF8] text-[#003C2F] text-sm placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition resize-none"></textarea>
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-bold text-[#60756B] uppercase tracking-wider mb-1.5">
                        Link do vídeo
                    </label>

                    <input type="text"
                           id="edit_video_url"
                           name="video_url"
                           required
                           class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-[#F8FBF8] text-[#003C2F] text-sm placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition">
                </div>

                <div class="flex flex-col sm:flex-row justify-end gap-3">
                    <button type="button"
                            onclick="fecharModalEditarAula()"
                            class="px-6 py-3 rounded-2xl border border-[#DCE7DE] text-[#60756B] text-sm font-bold hover:bg-[#F8FBF8] transition">
                        Cancelar
                    </button>

                    <button type="submit"
                            id="btnAtualizarAula"
                            class="px-6 py-3 rounded-2xl bg-[#004D3A] hover:bg-[#003C2F] text-white text-sm font-extrabold transition shadow-sm disabled:opacity-60 disabled:cursor-not-allowed">
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL CRIAR / EDITAR MINI TESTE -->
<div id="modalMiniTeste"
     class="modal-videoaulas-overlay fixed inset-0 hidden items-center justify-center bg-black/55 backdrop-blur-sm px-3 sm:px-4 py-4">

    <div class="modal-videoaulas-card bg-white rounded-3xl border border-[#E3EBE4] shadow-2xl overflow-hidden modal-scroll-mobile" style="width: min(100%, 1100px);">

        <div class="p-5 sm:p-7 border-b border-[#E3EBE4] flex items-start justify-between gap-4">

            <div class="min-w-0">
                <div class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest text-[#00A63E] mb-2">
                    <span class="w-2 h-2 rounded-full bg-[#00A63E]"></span>
                    Pós-teste da aula
                </div>

                <h2 class="text-2xl sm:text-3xl font-extrabold text-[#003C2F] break-words">
                    Criar / Editar Pós-teste
                </h2>

                <p class="text-sm text-[#60756B] mt-2 break-words">
                    Aula: <strong id="miniTesteNomeAula">Aula</strong>
                </p>
            </div>

            <button type="button"
                    onclick="fecharModalMiniTeste()"
                    class="w-10 h-10 rounded-xl bg-[#F1F6F2] text-[#003C2F] flex items-center justify-center hover:bg-[#E6EFE8] transition shrink-0">
                ✕
            </button>

        </div>

        <form method="POST" action="{{ route('avaliacoes.store') }}" id="formMiniTeste">
            @csrf

            <input type="hidden" name="aula_id" id="miniTesteAulaId">
            <input type="hidden" name="curso_id" id="miniTesteCursoId">

            <div class="p-5 sm:p-7">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

                    <div class="md:col-span-2">
                        <label class="block text-[11px] uppercase tracking-widest font-extrabold text-[#60756B] mb-2">
                            Título do pós-teste
                        </label>

                        <input type="text"
                               name="avaliacao[titulo]"
                               id="miniTesteTitulo"
                               required
                               placeholder="Ex: Pós-teste da aula"
                               class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-bold placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition">
                    </div>

                    <div>
                        <label class="block text-[11px] uppercase tracking-widest font-extrabold text-[#60756B] mb-2">
                            Tempo limite
                        </label>

                        <input type="number"
                               name="avaliacao[tempo_limite]"
                               id="miniTesteTempo"
                               min="1"
                               placeholder="Minutos"
                               class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-bold placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition">
                    </div>

                </div>

                <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-3xl overflow-hidden">

                    <div class="p-5 border-b border-[#E3EBE4] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                        <div>
                            <h3 class="text-xl font-extrabold text-[#003C2F]">
                                Perguntas do pós-teste
                            </h3>

                            <p class="text-xs text-[#60756B] mt-1">
                                Adicione perguntas e marque uma alternativa correta.
                            </p>
                        </div>

                        <button type="button"
                                onclick="adicionarPerguntaMiniTeste()"
                                class="bg-[#004D3A] hover:bg-[#003C2F] text-white px-5 py-3 rounded-2xl font-extrabold transition shadow-sm text-sm">
                            Adicionar pergunta
                        </button>

                    </div>

                    <div class="p-5">
                        <div id="miniTestePerguntasContainer" class="space-y-5"></div>
                    </div>

                </div>

                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 mt-7">

                    <button type="button"
                            onclick="fecharModalMiniTeste()"
                            class="px-6 py-3 rounded-2xl border border-[#DCE7DE] text-[#60756B] text-sm font-bold hover:bg-[#F8FBF8] transition">
                        Cancelar
                    </button>

                    <button type="submit"
                            id="btnSalvarMiniTeste"
                            class="px-7 py-3 rounded-2xl bg-[#004D3A] hover:bg-[#003C2F] text-white text-sm font-extrabold transition shadow-sm disabled:opacity-60 disabled:cursor-not-allowed">
                        Salvar pós-teste
                    </button>

                </div>

            </div>

        </form>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let perguntaIndex = 0;
    let perguntasImportadas = new Set();
    let miniTestePerguntaIndex = 0;

    function abrirModalAula() {
        document.body.classList.add('modal-aberto');

        const modal = document.getElementById('modalAula');

        if (!modal) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function fecharModalAula() {
        document.body.classList.remove('modal-aberto');

        const modal = document.getElementById('modalAula');

        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        const form = document.getElementById('formAula');
        const perguntasContainer = document.getElementById('perguntas-container');
        const importadasContainer = document.getElementById('perguntasImportadasContainer');
        const btnSalvar = document.getElementById('btnSalvarAula');

        if (form) form.reset();
        if (perguntasContainer) perguntasContainer.innerHTML = '';
        if (importadasContainer) importadasContainer.innerHTML = '';

        perguntasImportadas = new Set();

        if (btnSalvar) {
            btnSalvar.disabled = false;
            btnSalvar.innerText = 'Salvar Aula';
        }

        perguntaIndex = 0;
    }

    function abrirModalEditarAulaPeloBotao(botao) {
        if (!botao) return;

        const id = botao.dataset.id;
        const moduloId = botao.dataset.modulo;

        let titulo = '';
        let descricao = '';
        let videoUrl = '';

        try {
            titulo = JSON.parse(botao.dataset.titulo || '""');
            descricao = JSON.parse(botao.dataset.descricao || '""');
            videoUrl = JSON.parse(botao.dataset.video || '""');
        } catch (e) {
            titulo = botao.dataset.titulo || '';
            descricao = botao.dataset.descricao || '';
            videoUrl = botao.dataset.video || '';
        }

        abrirModalEditarAula(id, titulo, descricao, videoUrl, moduloId);
    }

    function abrirModalEditarAula(id, titulo, descricao, videoUrl, moduloId) {
        document.body.classList.add('modal-aberto');

        const modal = document.getElementById('modalEditarAula');
        const form = document.getElementById('formEditarAula');

        if (!modal || !form) {
            alert('Modal de edição não encontrado na página.');
            return;
        }

        const inputTitulo = document.getElementById('edit_titulo');
        const inputDescricao = document.getElementById('edit_descricao');
        const inputVideo = document.getElementById('edit_video_url');
        const inputModulo = document.getElementById('edit_modulo_id');

        if (inputTitulo) inputTitulo.value = titulo ?? '';
        if (inputDescricao) inputDescricao.value = descricao ?? '';
        if (inputVideo) inputVideo.value = videoUrl ?? '';
        if (inputModulo) inputModulo.value = moduloId ?? '';

        form.action = '/aulas/' + id;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function fecharModalEditarAula() {
        document.body.classList.remove('modal-aberto');

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

    function abrirModalMiniTeste(aulaId, cursoId, nomeAula, avaliacao, perguntas) {
        document.body.classList.add('modal-aberto');

        const modal = document.getElementById('modalMiniTeste');
        const aulaIdInput = document.getElementById('miniTesteAulaId');
        const cursoIdInput = document.getElementById('miniTesteCursoId');
        const nomeAulaTexto = document.getElementById('miniTesteNomeAula');
        const tituloInput = document.getElementById('miniTesteTitulo');
        const tempoInput = document.getElementById('miniTesteTempo');
        const container = document.getElementById('miniTestePerguntasContainer');
        const btnSalvar = document.getElementById('btnSalvarMiniTeste');

        if (!modal || !aulaIdInput || !cursoIdInput || !nomeAulaTexto || !tituloInput || !tempoInput || !container) {
            alert('Modal de pós-teste não encontrado na página.');
            return;
        }

        miniTestePerguntaIndex = 0;

        aulaIdInput.value = aulaId || '';
        cursoIdInput.value = cursoId || '';
        nomeAulaTexto.innerText = nomeAula || 'Aula';

        tituloInput.value = avaliacao && avaliacao.titulo
            ? avaliacao.titulo
            : 'Pós-teste - ' + (nomeAula || 'Aula');

        tempoInput.value = avaliacao && avaliacao.tempo_limite
            ? avaliacao.tempo_limite
            : '';

        container.innerHTML = '';

        if (btnSalvar) {
            btnSalvar.disabled = false;
            btnSalvar.innerText = 'Salvar pós-teste';
        }

        if (perguntas && perguntas.length > 0) {
            perguntas.forEach((pergunta) => {
                adicionarPerguntaMiniTeste(pergunta);
            });
        } else {
            adicionarPerguntaMiniTeste();
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function fecharModalMiniTeste() {
        document.body.classList.remove('modal-aberto');

        const modal = document.getElementById('modalMiniTeste');
        const form = document.getElementById('formMiniTeste');
        const container = document.getElementById('miniTestePerguntasContainer');
        const btnSalvar = document.getElementById('btnSalvarMiniTeste');

        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        if (form) form.reset();
        if (container) container.innerHTML = '';

        if (btnSalvar) {
            btnSalvar.disabled = false;
            btnSalvar.innerText = 'Salvar pós-teste';
        }

        miniTestePerguntaIndex = 0;
    }

    function adicionarPerguntaMiniTeste(dados = null) {
        const container = document.getElementById('miniTestePerguntasContainer');

        if (!container) return;

        const index = miniTestePerguntaIndex;
        const perguntaTexto = dados && dados.pergunta ? dados.pergunta : '';

        let respostas = dados && dados.respostas && dados.respostas.length > 0
            ? dados.respostas
            : [
                { resposta: '', correta: true },
                { resposta: '', correta: false },
                { resposta: '', correta: false },
                { resposta: '', correta: false }
            ];

        const card = document.createElement('div');
        card.className = 'mini-teste-card bg-white border border-[#DCE7DE] rounded-3xl p-5 shadow-sm';
        card.id = 'mini-teste-pergunta-' + index;

        card.innerHTML = `
            <div class="flex items-start justify-between gap-4 mb-4">
                <div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-[#EAF5EF] text-[#004D3A] text-[11px] font-extrabold uppercase tracking-widest">
                        Pergunta ${index + 1}
                    </span>
                </div>

                <button type="button"
                        onclick="removerPerguntaMiniTeste(${index})"
                        class="text-red-600 font-bold text-sm hover:text-red-700">
                    Remover
                </button>
            </div>

            <label class="block text-[11px] uppercase tracking-widest font-extrabold text-[#60756B] mb-2">
                Enunciado
            </label>

            <input type="text"
                   name="perguntas[${index}][pergunta]"
                   value="${escapeHtmlMiniTeste(perguntaTexto)}"
                   placeholder="Digite a pergunta..."
                   required
                   class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-bold placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition mb-4">

            <div class="space-y-3" id="mini-teste-respostas-${index}"></div>
        `;

        container.appendChild(card);

        respostas.forEach((resposta, respostaIndex) => {
            adicionarRespostaMiniTeste(index, respostaIndex, resposta);
        });

        miniTestePerguntaIndex++;
    }

    function adicionarRespostaMiniTeste(perguntaIndex, respostaIndex, respostaDados = null) {
        const container = document.getElementById('mini-teste-respostas-' + perguntaIndex);

        if (!container) return;

        const letras = ['A', 'B', 'C', 'D', 'E'];
        const texto = respostaDados && respostaDados.resposta ? respostaDados.resposta : '';
        const correta = respostaDados && (respostaDados.correta == 1 || respostaDados.correta === true);

        const div = document.createElement('div');
        div.className = 'bg-[#F8FBF8] border border-[#DCE7DE] rounded-2xl px-4 py-3 flex items-center gap-3';

        div.innerHTML = `
            <input type="radio"
                   name="perguntas[${perguntaIndex}][correta]"
                   value="${respostaIndex}"
                   ${correta ? 'checked' : ''}
                   class="w-4 h-4 accent-[#004D3A] cursor-pointer">

            <span class="text-xs font-extrabold text-[#60756B] w-5">
                ${letras[respostaIndex] ?? respostaIndex + 1}
            </span>

            <input type="text"
                   name="perguntas[${perguntaIndex}][respostas][]"
                   value="${escapeHtmlMiniTeste(texto)}"
                   placeholder="Digite a alternativa..."
                   required
                   class="flex-1 bg-transparent text-sm text-[#003C2F] placeholder-[#8A9B92] focus:outline-none">
        `;

        container.appendChild(div);
    }

    function removerPerguntaMiniTeste(index) {
        const card = document.getElementById('mini-teste-pergunta-' + index);

        if (card) {
            card.remove();
        }
    }

    function escapeHtmlMiniTeste(texto) {
        return String(texto ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;');
    }

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

    function normalizarPesquisa(texto) {
        return (texto || '')
            .toString()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    }

    function pesquisarVideoaulasAoVivo() {
        const input = document.getElementById('pesquisaVideoaulas');
        const btnLimpar = document.getElementById('btnLimparPesquisaVideoaulas');
        const contador = document.getElementById('contadorPesquisaVideoaulas');

        const termo = normalizarPesquisa(input ? input.value : '');

        const modulos = document.querySelectorAll('.modulo-pesquisa');
        let resultados = 0;

        if (btnLimpar) {
            btnLimpar.classList.toggle('hidden', termo.length === 0);
            btnLimpar.classList.toggle('flex', termo.length > 0);
        }

        modulos.forEach((modulo) => {
            const textoModulo = normalizarPesquisa(modulo.dataset.search || '');
            const aulas = modulo.querySelectorAll('.aula-pesquisa');

            let moduloTemResultado = textoModulo.includes(termo);
            let aulasVisiveis = 0;

            aulas.forEach((aula) => {
                const textoAula = normalizarPesquisa(aula.dataset.search || '');
                const aparece = termo === '' || textoAula.includes(termo) || textoModulo.includes(termo);

                aula.classList.toggle('hidden', !aparece);

                if (aparece) {
                    aulasVisiveis++;
                    resultados++;
                }
            });

            if (aulas.length === 0 && moduloTemResultado) {
                resultados++;
            }

            modulo.classList.toggle('hidden', !(termo === '' || moduloTemResultado || aulasVisiveis > 0));

            const corpoModulo = modulo.querySelector('[id^="modulo-"]');

            if (corpoModulo && termo !== '' && (moduloTemResultado || aulasVisiveis > 0)) {
                corpoModulo.classList.remove('hidden');
            }
        });

        if (contador) {
            contador.innerText = resultados;
        }
    }

    function limparPesquisaVideoaulas() {
        const input = document.getElementById('pesquisaVideoaulas');

        if (input) {
            input.value = '';
            input.focus();
        }

        pesquisarVideoaulasAoVivo();
    }

    /*
    |--------------------------------------------------------------------------
    | PERGUNTAS DO PÓS-TESTE NA CRIAÇÃO DA AULA
    |--------------------------------------------------------------------------
    | Corrigido:
    | - Ao remover pergunta, a numeração é reorganizada.
    | - Ao criar nova pergunta depois de remover, continua a sequência correta.
    | - Alternativas continuam em ordem alfabética: A, B, C, D, E, F, G...
    */

    function letraAlternativa(indice) {
        let numero = indice + 1;
        let letra = '';

        while (numero > 0) {
            const resto = (numero - 1) % 26;
            letra = String.fromCharCode(65 + resto) + letra;
            numero = Math.floor((numero - 1) / 26);
        }

        return letra;
    }

    function reindexarPerguntas() {
        const container = document.getElementById('perguntas-container');

        if (!container) return;

        const cards = Array.from(container.querySelectorAll('.pergunta-card'));

        cards.forEach((card, novoIndex) => {
            card.id = `pergunta-${novoIndex}`;
            card.dataset.index = novoIndex;

            const titulo = card.querySelector('.titulo-pergunta-card');
            if (titulo) {
                titulo.innerText = `Pergunta ${novoIndex + 1}`;
            }

            const remover = card.querySelector('.btn-remover-pergunta');
            if (remover) {
                remover.setAttribute('onclick', `removerPergunta(${novoIndex})`);
            }

            const inputPergunta = card.querySelector('.input-pergunta');
            if (inputPergunta) {
                inputPergunta.name = `perguntas[${novoIndex}][pergunta]`;
            }

            const respostasContainer = card.querySelector('.respostas-container');
            if (respostasContainer) {
                respostasContainer.id = `respostas-${novoIndex}`;
            }

            const addRespostaBotao = card.querySelector('.btn-add-resposta');
            if (addRespostaBotao) {
                addRespostaBotao.setAttribute('onclick', `addResposta(${novoIndex})`);
            }

            reindexarRespostas(novoIndex);
        });

        perguntaIndex = cards.length;
    }

    function reindexarRespostas(perguntaIndexAtual) {
        const container = document.getElementById(`respostas-${perguntaIndexAtual}`);

        if (!container) return;

        const respostas = Array.from(container.querySelectorAll('.resposta-card'));

        respostas.forEach((resposta, novoIndex) => {
            resposta.id = `resposta-${perguntaIndexAtual}-${novoIndex}`;
            resposta.dataset.index = novoIndex;

            const radio = resposta.querySelector('input[type="radio"]');
            if (radio) {
                radio.name = `perguntas[${perguntaIndexAtual}][correta]`;
                radio.value = novoIndex;
            }

            const letra = resposta.querySelector('.letra-resposta');
            if (letra) {
                letra.innerText = letraAlternativa(novoIndex);
            }

            const input = resposta.querySelector('.input-resposta');
            if (input) {
                input.name = `perguntas[${perguntaIndexAtual}][respostas][]`;
                input.placeholder = `Texto da alternativa ${letraAlternativa(novoIndex)}...`;
            }

            const remover = resposta.querySelector('.btn-remover-resposta');
            if (remover) {
                remover.setAttribute('onclick', `removerResposta(${perguntaIndexAtual}, ${novoIndex})`);
            }
        });
    }

    function addPergunta() {
        const container = document.getElementById('perguntas-container');

        if (!container) return;

        perguntaIndex = container.querySelectorAll('.pergunta-card').length;

        const div = document.createElement('div');

        div.className = 'pergunta-card border border-[#DCE7DE] rounded-3xl p-4 bg-[#F8FBF8]';
        div.id = `pergunta-${perguntaIndex}`;
        div.dataset.index = perguntaIndex;

        div.innerHTML = `
            <div class="flex items-center justify-between mb-3">
                <span class="titulo-pergunta-card text-xs font-bold text-[#60756B] uppercase tracking-widest bg-white border border-[#DCE7DE] px-3 py-1 rounded-xl">
                    Pergunta ${perguntaIndex + 1}
                </span>

                <button type="button" onclick="removerPergunta(${perguntaIndex})"
                    class="btn-remover-pergunta text-red-400 hover:text-red-600 transition" title="Remover">
                    Remover
                </button>
            </div>

            <input
                type="text"
                name="perguntas[${perguntaIndex}][pergunta]"
                placeholder="Digite o enunciado da questão aqui..."
                class="input-pergunta w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-white text-[#003C2F] text-sm placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition mb-3"
            >

            <p class="text-xs font-bold text-[#60756B] uppercase tracking-wider mb-2">
                Alternativas — marque a correta
            </p>

            <div id="respostas-${perguntaIndex}" class="respostas-container space-y-2 mb-3"></div>

            <button type="button" onclick="addResposta(${perguntaIndex})"
                class="btn-add-resposta flex items-center gap-1 text-xs font-bold text-[#004D3A] hover:text-[#003C2F] transition">
                Adicionar alternativa
            </button>
        `;

        container.appendChild(div);

        addResposta(perguntaIndex);
        addResposta(perguntaIndex);
        addResposta(perguntaIndex);
        addResposta(perguntaIndex);

        reindexarPerguntas();
    }

    function removerPergunta(index) {
        const pergunta = document.getElementById(`pergunta-${index}`);

        if (pergunta) {
            pergunta.remove();
        }

        reindexarPerguntas();
    }

    function addResposta(index) {
        const container = document.getElementById(`respostas-${index}`);

        if (!container) return;

        const total = container.querySelectorAll('.resposta-card').length;
        const letra = letraAlternativa(total);

        const div = document.createElement('div');

        div.id = `resposta-${index}-${total}`;
        div.dataset.index = total;
        div.className = 'resposta-card flex items-center gap-3 bg-white border border-[#DCE7DE] rounded-2xl px-4 py-3';

        div.innerHTML = `
            <input type="radio" name="perguntas[${index}][correta]" value="${total}"
                class="w-4 h-4 accent-[#004D3A] cursor-pointer">

            <span class="letra-resposta text-xs font-bold text-[#60756B] w-6">${letra}</span>

            <input
                type="text"
                name="perguntas[${index}][respostas][]"
                placeholder="Texto da alternativa ${letra}..."
                class="input-resposta flex-1 text-sm text-[#003C2F] bg-transparent placeholder-[#8A9B92] focus:outline-none"
            >

            <button type="button" onclick="removerResposta(${index}, ${total})"
                class="btn-remover-resposta text-gray-300 hover:text-red-500 transition">
                ✕
            </button>
        `;

        container.appendChild(div);

        reindexarRespostas(index);
    }

    function removerResposta(perguntaIndexAtual, respostaIndex) {
        const resposta = document.getElementById(`resposta-${perguntaIndexAtual}-${respostaIndex}`);

        if (resposta) {
            resposta.remove();
        }

        reindexarRespostas(perguntaIndexAtual);
    }

    function abrirBancoPerguntas() {
        document.body.classList.add('modal-aberto');

        const modal = document.getElementById('modalBancoPerguntas');

        if (!modal) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        buscarPerguntasAntigas();
    }

    function fecharBancoPerguntas() {
        const modal = document.getElementById('modalBancoPerguntas');

        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    async function buscarPerguntasAntigas() {
        const lista = document.getElementById('listaBancoPerguntas');
        const pesquisa = document.getElementById('pesquisaBancoPerguntas')?.value || '';
        const dataInicio = document.getElementById('dataInicioBancoPerguntas')?.value || '';
        const dataFim = document.getElementById('dataFimBancoPerguntas')?.value || '';

        if (!lista) return;

        lista.innerHTML = `
            <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-3xl p-8 text-center text-[#60756B]">
                Carregando perguntas...
            </div>
        `;

        const params = new URLSearchParams();

        if (pesquisa) params.append('pesquisa', pesquisa);
        if (dataInicio) params.append('data_inicio', dataInicio);
        if (dataFim) params.append('data_fim', dataFim);

        try {
            const resposta = await fetch('/banco-perguntas?' + params.toString());
            const dados = await resposta.json();

            if (!dados.success || !dados.perguntas || dados.perguntas.length === 0) {
                lista.innerHTML = `
                    <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-3xl p-8 text-center text-[#60756B]">
                        Nenhuma pergunta encontrada.
                    </div>
                `;
                return;
            }

            lista.innerHTML = '';

            dados.perguntas.forEach((pergunta) => {
                const respostas = pergunta.respostas || [];

                const respostasHtml = respostas.map((resposta) => `
                    <li class="${resposta.correta ? 'text-green-700 font-bold' : 'text-[#60756B]'}">
                        ${resposta.resposta} ${resposta.correta ? '(correta)' : ''}
                    </li>
                `).join('');

                const selecionada = perguntasImportadas.has(String(pergunta.id));

                const item = document.createElement('div');
                item.className = 'bg-[#F8FBF8] border border-[#E3EBE4] rounded-3xl p-5';

                item.innerHTML = `
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold mb-2">
                                ${pergunta.curso_nome || 'Sem curso'} • ${pergunta.modulo_nome || 'Sem módulo'} • ${pergunta.aula_titulo || 'Sem aula'}
                            </p>

                            <h3 class="text-base font-extrabold text-[#003C2F] break-words">
                                ${pergunta.pergunta}
                            </h3>

                            <ul class="list-disc pl-5 text-sm mt-3 space-y-1">
                                ${respostasHtml}
                            </ul>
                        </div>

                        <button type="button"
                                onclick='selecionarPerguntaImportada(${JSON.stringify(pergunta.id)}, ${JSON.stringify(pergunta.pergunta)})'
                                class="${selecionada ? 'bg-green-600 text-white' : 'bg-[#004D3A] text-white'} px-4 py-3 rounded-2xl font-extrabold hover:bg-[#003C2F] transition shrink-0">
                            ${selecionada ? 'Selecionada' : 'Importar'}
                        </button>
                    </div>
                `;

                lista.appendChild(item);
            });

        } catch (e) {
            lista.innerHTML = `
                <div class="bg-red-50 border border-red-100 rounded-3xl p-8 text-center text-red-600">
                    Erro ao buscar perguntas antigas.
                </div>
            `;
        }
    }

    function selecionarPerguntaImportada(id, texto) {
        const container = document.getElementById('perguntasImportadasContainer');

        if (!container) return;

        const idString = String(id);

        if (perguntasImportadas.has(idString)) {
            return;
        }

        perguntasImportadas.add(idString);

        const item = document.createElement('div');
        item.id = 'pergunta-importada-' + idString;
        item.className = 'bg-[#F8FBF8] border border-[#DCE7DE] rounded-2xl p-4 flex items-start justify-between gap-3';

        item.innerHTML = `
            <div>
                <input type="hidden" name="perguntas_importadas[]" value="${idString}">
                <p class="text-sm font-extrabold text-[#003C2F]">${texto}</p>
                <p class="text-xs text-[#60756B] mt-1">Essa pergunta será copiada para o novo pós-teste.</p>
            </div>

            <button type="button"
                    onclick="removerPerguntaImportada('${idString}')"
                    class="text-red-600 font-bold">
                Remover
            </button>
        `;

        container.appendChild(item);

        buscarPerguntasAntigas();
    }

    function removerPerguntaImportada(id) {
        perguntasImportadas.delete(String(id));

        const item = document.getElementById('pergunta-importada-' + id);

        if (item) {
            item.remove();
        }
    }

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
            color: '#003C2F'
        }).then((result) => {
            if (result.isConfirmed) {
                btn.closest('form').submit();
            }
        });
    }

    const modalAula = document.getElementById('modalAula');
    const modalEditarAula = document.getElementById('modalEditarAula');
    const modalBancoPerguntas = document.getElementById('modalBancoPerguntas');
    const modalMiniTeste = document.getElementById('modalMiniTeste');

    if (modalAula) {
        modalAula.addEventListener('click', function (e) {
            if (e.target === this) fecharModalAula();
        });
    }

    if (modalEditarAula) {
        modalEditarAula.addEventListener('click', function(e) {
            if (e.target === this) fecharModalEditarAula();
        });
    }

    if (modalBancoPerguntas) {
        modalBancoPerguntas.addEventListener('click', function(e) {
            if (e.target === this) fecharBancoPerguntas();
        });
    }

    if (modalMiniTeste) {
        modalMiniTeste.addEventListener('click', function(e) {
            if (e.target === this) fecharModalMiniTeste();
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

    const formMiniTeste = document.getElementById('formMiniTeste');

    if (formMiniTeste) {
        formMiniTeste.addEventListener('submit', function () {
            const btn = document.getElementById('btnSalvarMiniTeste');

            if (btn) {
                btn.disabled = true;
                btn.innerText = 'Salvando...';
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            fecharModalAula();
            fecharModalEditarAula();
            fecharBancoPerguntas();
            fecharModalMiniTeste();
        }
    });

    @if ($errors->any() || session('error'))
        abrirModalAula();
    @endif

    /*
    |--------------------------------------------------------------------------
    | FECHAR MODAIS AO CLICAR FORA
    |--------------------------------------------------------------------------
    */
    ['modalAula', 'modalEditarAula', 'modalMiniTeste', 'modalBancoPerguntas'].forEach(function(idModal) {
        const modal = document.getElementById(idModal);

        if (!modal) return;

        modal.addEventListener('click', function(e) {
            if (e.target !== modal) return;

            if (idModal === 'modalAula') fecharModalAula();
            if (idModal === 'modalEditarAula') fecharModalEditarAula();
            if (idModal === 'modalMiniTeste') fecharModalMiniTeste();
            if (idModal === 'modalBancoPerguntas') fecharBancoPerguntas();
        });
    });

</script>

@endsection