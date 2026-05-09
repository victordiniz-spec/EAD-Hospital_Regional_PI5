@extends('layout.app')

@section('title', 'Avisos')

@section('content')

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
            <div class="mb-7 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-5">

                <div>
                    <div class="flex items-center gap-2 text-[11px] font-extrabold uppercase tracking-widest text-[#60756B] mb-2">
                        <span>Home</span>
                        <span>›</span>
                        <span class="text-[#004D3A]">Avisos</span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl font-extrabold text-[#003C2F] tracking-tight">
                        Avisos
                    </h1>

                    <p class="text-sm text-[#60756B] mt-2 max-w-2xl">
                        Crie avisos com tempo de exibição. Avisos urgentes aparecem em destaque para o aluno ao entrar na plataforma.
                    </p>
                </div>

                <div class="bg-white border border-[#E3EBE4] rounded-3xl px-5 py-4 shadow-sm">
                    <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                        Total de avisos
                    </p>

                    <p class="text-2xl font-extrabold text-[#004D3A] mt-1">
                        {{ $avisos->count() }}
                    </p>
                </div>

            </div>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-7">

                <!-- FORMULÁRIO -->
                <div class="xl:col-span-4">

                    <div class="bg-white border border-[#E3EBE4] rounded-3xl shadow-sm p-5 sm:p-6 sticky top-6">

                        <div class="flex items-center gap-3 mb-6">

                            <div class="w-12 h-12 rounded-2xl bg-[#EAF5EF] text-[#004D3A] flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-6 h-6"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="1.8"
                                          d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H5.25A2.25 2.25 0 0 1 3 13.5v-6A2.25 2.25 0 0 1 5.25 5.25h3c.704 0 1.402-.03 2.09-.09m0 10.68c1.08.094 2.102.31 3.04.63 1.38.47 2.62 1.17 3.67 2.03.517.424 1.28.067 1.28-.602V3.102c0-.669-.763-1.026-1.28-.602a14.8 14.8 0 0 1-3.67 2.03c-.938.32-1.96.536-3.04.63m0 10.68V5.16"/>
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-xl font-extrabold text-[#003C2F] leading-tight">
                                    Criar novo aviso
                                </h2>

                                <p class="text-xs text-[#60756B] mt-1">
                                    Defina categoria, mensagem e tempo de exibição.
                                </p>
                            </div>

                        </div>

                        <form method="POST" action="{{ route('avisos.store') }}">
                            @csrf

                            <div class="space-y-5">

                                <!-- TÍTULO -->
                                <div>
                                    <label class="block text-[11px] uppercase tracking-widest font-extrabold text-[#60756B] mb-2">
                                        Título
                                    </label>

                                    <input type="text"
                                           name="titulo"
                                           value="{{ old('titulo') }}"
                                           placeholder="Ex: Novo módulo disponível"
                                           class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-bold placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition"
                                           required>
                                </div>

                                <!-- CATEGORIA -->
                                <div>
                                    <label class="block text-[11px] uppercase tracking-widest font-extrabold text-[#60756B] mb-2">
                                        Categoria
                                    </label>

                                    <select name="categoria"
                                            class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-bold focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition"
                                            required>
                                        <option value="urgente" {{ old('categoria') === 'urgente' ? 'selected' : '' }}>
                                            Urgente
                                        </option>

                                        <option value="importante" {{ old('categoria', 'importante') === 'importante' ? 'selected' : '' }}>
                                            Importante
                                        </option>
                                    </select>

                                    <p class="text-xs text-[#60756B] mt-2">
                                        Aviso urgente aparece primeiro e abre em popup na dashboard do aluno.
                                    </p>
                                </div>

                                <!-- TEMPO DE EXIBIÇÃO -->
                                <div>
                                    <label class="block text-[11px] uppercase tracking-widest font-extrabold text-[#60756B] mb-2">
                                        Tempo visível na dashboard do aluno
                                    </label>

                                    <div class="grid grid-cols-2 gap-3">
                                        <input type="number"
                                               name="tempo_exibicao"
                                               min="1"
                                               value="{{ old('tempo_exibicao', 24) }}"
                                               class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-bold focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition"
                                               required>

                                        <select name="unidade_tempo"
                                                class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-bold focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition"
                                                required>
                                            <option value="minutos" {{ old('unidade_tempo') === 'minutos' ? 'selected' : '' }}>
                                                Minutos
                                            </option>

                                            <option value="horas" {{ old('unidade_tempo', 'horas') === 'horas' ? 'selected' : '' }}>
                                                Horas
                                            </option>

                                            <option value="dias" {{ old('unidade_tempo') === 'dias' ? 'selected' : '' }}>
                                                Dias
                                            </option>
                                        </select>
                                    </div>

                                    <p class="text-xs text-[#60756B] mt-2">
                                        Depois desse prazo, o aviso não aparecerá mais para o aluno.
                                    </p>
                                </div>

                                <!-- MENSAGEM -->
                                <div>
                                    <label class="block text-[11px] uppercase tracking-widest font-extrabold text-[#60756B] mb-2">
                                        Mensagem
                                    </label>

                                    <textarea name="mensagem"
                                              rows="5"
                                              placeholder="Digite a mensagem do aviso..."
                                              class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-medium placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition resize-none"
                                              required>{{ old('mensagem') }}</textarea>
                                </div>

                                <!-- PUBLICAR -->
                                <div class="flex items-center gap-3">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="publicar_agora" class="sr-only peer" checked>
                                        <div class="w-11 h-6 bg-gray-200 rounded-full peer
                                            peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#00A63E]
                                            peer-checked:bg-[#00A63E]
                                            after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                            after:bg-white after:border after:border-gray-300 after:rounded-full
                                            after:h-5 after:w-5 after:transition-all
                                            peer-checked:after:translate-x-full peer-checked:after:border-white">
                                        </div>
                                    </label>

                                    <span class="text-sm font-bold text-[#003C2F]">
                                        Publicar agora
                                    </span>
                                </div>

                                <!-- BOTÃO -->
                                <button type="submit"
                                        class="w-full bg-[#004D3A] hover:bg-[#003C2F] text-white px-6 py-4 rounded-2xl font-extrabold transition shadow-lg flex items-center justify-center gap-2">
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

                                    Salvar aviso
                                </button>

                            </div>

                        </form>

                    </div>

                </div>

                <!-- LISTA -->
                <div class="xl:col-span-8">

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
                                              d="M12 6v6h4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                                    </svg>
                                </div>

                                <div>
                                    <h2 class="text-xl font-extrabold text-[#003C2F]">
                                        Avisos recentes
                                    </h2>

                                    <p class="text-xs text-[#60756B] mt-1">
                                        Urgentes aparecem primeiro. Avisos expirados não aparecem para o aluno.
                                    </p>
                                </div>
                            </div>

                            <span class="inline-flex items-center justify-center bg-[#EAF5EF] text-[#004D3A] px-4 py-2 rounded-full text-xs font-extrabold">
                                {{ $avisos->count() }} cadastrados
                            </span>

                        </div>

                        <div class="p-4 sm:p-6">

                            <div class="space-y-4">

                                @forelse($avisos as $aviso)

                                    @php
                                        $categoria = strtolower($aviso->categoria ?? $aviso->tipo ?? 'importante');
                                        $urgente = $categoria === 'urgente';
                                        $expirado = isset($aviso->expires_at) && $aviso->expires_at && \Carbon\Carbon::parse($aviso->expires_at)->isPast();
                                        $mensagemAviso = $aviso->mensagem ?? $aviso->descricao ?? '';
                                    @endphp

                                    <div class="bg-[#F8FBF8] border rounded-3xl p-5 hover:shadow-md transition
                                        {{ $expirado ? 'border-gray-200 opacity-70' : 'border-[#E3EBE4]' }}">

                                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">

                                            <div class="min-w-0 flex-1">

                                                <div class="flex flex-wrap items-center gap-2 mb-3">

                                                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] font-extrabold
                                                        {{ $urgente ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">

                                                        <span class="w-2 h-2 rounded-full {{ $urgente ? 'bg-red-600' : 'bg-green-600' }}"></span>

                                                        {{ $urgente ? 'URGENTE' : 'IMPORTANTE' }}
                                                    </span>

                                                    @if($expirado)
                                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-extrabold bg-gray-200 text-gray-600">
                                                            EXPIRADO
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-extrabold bg-blue-50 text-blue-700">
                                                            ATIVO
                                                        </span>
                                                    @endif

                                                    <span class="text-xs text-[#8A9B92] font-semibold">
                                                        Criado em {{ \Carbon\Carbon::parse($aviso->created_at)->format('d/m/Y H:i') }}
                                                    </span>

                                                </div>

                                                <h3 class="text-lg font-extrabold text-[#003C2F] leading-tight break-words">
                                                    {{ $aviso->titulo }}
                                                </h3>

                                                <p class="text-sm text-[#60756B] mt-2 leading-relaxed break-words">
                                                    {{ $mensagemAviso }}
                                                </p>

                                                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                    <div class="bg-white border border-[#E3EBE4] rounded-2xl px-4 py-3">
                                                        <p class="text-[10px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                                            Expiração
                                                        </p>

                                                        <p class="text-xs text-[#003C2F] font-bold mt-1">
                                                            {{ $aviso->expires_at ? \Carbon\Carbon::parse($aviso->expires_at)->format('d/m/Y H:i') : 'Sem expiração' }}
                                                        </p>
                                                    </div>

                                                    <div class="bg-white border border-[#E3EBE4] rounded-2xl px-4 py-3">
                                                        <p class="text-[10px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                                            Tempo restante
                                                        </p>

                                                        <p class="text-xs font-bold mt-1 {{ $expirado ? 'text-gray-500' : 'text-[#004D3A]' }}">
                                                            @if($aviso->expires_at)
                                                                {{ $expirado ? 'Expirado' : \Carbon\Carbon::parse($aviso->expires_at)->diffForHumans() }}
                                                            @else
                                                                Indefinido
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="flex items-center gap-2 shrink-0">

                                                <!-- EDITAR -->
                                                <button type="button"
                                                        onclick='abrirModalEditarAviso(
                                                            @json($aviso->id),
                                                            @json($aviso->titulo),
                                                            @json($mensagemAviso),
                                                            @json($categoria)
                                                        )'
                                                        class="w-10 h-10 rounded-xl bg-white border border-[#DCE7DE] hover:bg-[#EAF5EF] text-[#004D3A] transition flex items-center justify-center"
                                                        title="Editar aviso">

                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                         class="w-5 h-5"
                                                         fill="none"
                                                         viewBox="0 0 24 24"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round"
                                                              stroke-linejoin="round"
                                                              stroke-width="1.8"
                                                              d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931z"/>
                                                    </svg>
                                                </button>

                                                <!-- EXCLUIR -->
                                                <button type="button"
                                                        onclick="abrirModalExcluirAviso({{ $aviso->id }})"
                                                        class="w-10 h-10 rounded-xl bg-white border border-red-100 hover:bg-red-50 text-red-600 transition flex items-center justify-center"
                                                        title="Excluir aviso">

                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                         class="w-5 h-5"
                                                         fill="none"
                                                         viewBox="0 0 24 24"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round"
                                                              stroke-linejoin="round"
                                                              stroke-width="1.8"
                                                              d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166M19.228 5.79 18.16 19.673A2.25 2.25 0 0 1 15.916 21.75H8.084A2.25 2.25 0 0 1 5.84 19.673L4.772 5.79M19.228 5.79a48.108 48.108 0 0 0-3.478-.397M4.772 5.79c1.148-.175 2.32-.302 3.478-.397m7.5 0V4.875C15.75 3.839 14.911 3 13.875 3h-3.75C9.089 3 8.25 3.839 8.25 4.875v.518m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                                    </svg>
                                                </button>

                                                <form id="formExcluirAviso{{ $aviso->id }}"
                                                      method="POST"
                                                      action="{{ route('avisos.destroy', $aviso->id) }}"
                                                      class="hidden">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>

                                            </div>

                                        </div>

                                    </div>

                                @empty

                                    <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-3xl p-10 text-center">

                                        <div class="w-20 h-20 mx-auto rounded-full bg-[#EAF5EF] text-[#004D3A] flex items-center justify-center mb-5">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                 class="w-10 h-10"
                                                 fill="none"
                                                 viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                      stroke-width="1.8"
                                                      d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022 23.848 23.848 0 0 0 5.455 1.31m5.714 0a3 3 0 1 1-5.714 0"/>
                                            </svg>
                                        </div>

                                        <h3 class="text-xl font-extrabold text-[#003C2F]">
                                            Nenhum aviso encontrado
                                        </h3>

                                        <p class="text-sm text-[#60756B] mt-2">
                                            Crie o primeiro aviso usando o formulário ao lado.
                                        </p>

                                    </div>

                                @endforelse

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </section>

    </main>

</div>

<!-- MODAL EDITAR AVISO -->
<div id="modalEditarAviso"
     class="fixed inset-0 hidden items-center justify-center bg-black/50 backdrop-blur-sm z-[80] px-4">

    <div class="bg-white w-full max-w-lg p-6 rounded-3xl border border-[#E3EBE4] shadow-2xl">

        <div class="flex items-start justify-between mb-6">

            <div>
                <h2 class="text-2xl font-extrabold text-[#003C2F]">
                    Editar aviso
                </h2>

                <p class="text-sm text-[#60756B] mt-1">
                    Atualize as informações e redefina o tempo de exibição.
                </p>
            </div>

            <button type="button"
                    onclick="fecharModalEditarAviso()"
                    class="bg-[#F1F6F2] hover:bg-[#E6EFE8] text-[#003C2F] p-2 rounded-xl transition">

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

        <form id="formEditarAviso" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-5">

                <div>
                    <label class="block text-[11px] uppercase tracking-widest font-extrabold text-[#60756B] mb-2">
                        Título
                    </label>

                    <input type="text"
                           name="titulo"
                           id="editarTituloAviso"
                           class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-bold focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition"
                           required>
                </div>

                <div>
                    <label class="block text-[11px] uppercase tracking-widest font-extrabold text-[#60756B] mb-2">
                        Categoria
                    </label>

                    <select name="categoria"
                            id="editarCategoriaAviso"
                            class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-bold focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition"
                            required>
                        <option value="urgente">Urgente</option>
                        <option value="importante">Importante</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] uppercase tracking-widest font-extrabold text-[#60756B] mb-2">
                        Novo tempo de exibição
                    </label>

                    <div class="grid grid-cols-2 gap-3">
                        <input type="number"
                               name="tempo_exibicao"
                               min="1"
                               value="24"
                               class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-bold focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition">

                        <select name="unidade_tempo"
                                class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-bold focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition">
                            <option value="minutos">Minutos</option>
                            <option value="horas" selected>Horas</option>
                            <option value="dias">Dias</option>
                        </select>
                    </div>

                    <p class="text-xs text-[#60756B] mt-2">
                        Ao salvar, o tempo será renovado a partir de agora.
                    </p>
                </div>

                <div>
                    <label class="block text-[11px] uppercase tracking-widest font-extrabold text-[#60756B] mb-2">
                        Mensagem
                    </label>

                    <textarea name="mensagem"
                              id="editarMensagemAviso"
                              rows="5"
                              class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition resize-none"
                              required></textarea>
                </div>

                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="publicar_agora" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer
                            peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#00A63E]
                            peer-checked:bg-[#00A63E]
                            after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                            after:bg-white after:border after:border-gray-300 after:rounded-full
                            after:h-5 after:w-5 after:transition-all
                            peer-checked:after:translate-x-full peer-checked:after:border-white">
                        </div>
                    </label>

                    <span class="text-sm font-bold text-[#003C2F]">
                        Publicar agora
                    </span>
                </div>

            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-3 mt-7">
                <button type="button"
                        onclick="fecharModalEditarAviso()"
                        class="px-5 py-3 rounded-2xl bg-[#F1F6F2] text-[#60756B] font-bold hover:bg-[#E6EFE8] transition">
                    Cancelar
                </button>

                <button type="submit"
                        class="px-5 py-3 rounded-2xl bg-[#004D3A] text-white font-bold hover:bg-[#003C2F] transition shadow-sm">
                    Salvar alterações
                </button>
            </div>

        </form>

    </div>

</div>

<!-- MODAL EXCLUIR AVISO -->
<div id="modalExcluirAviso"
     class="fixed inset-0 hidden items-center justify-center bg-black/50 backdrop-blur-sm z-[85] px-4">

    <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl p-6 text-center border border-[#E3EBE4]">

        <div class="w-16 h-16 mx-auto rounded-full bg-red-100 flex items-center justify-center mb-4">
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="w-8 h-8 text-red-600"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="1.8"
                      d="M12 9v3.75m0 3.75h.008v.008H12V16.5zm9-4.5a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
            </svg>
        </div>

        <h2 class="text-xl font-extrabold text-[#003C2F] mb-2">
            Excluir aviso?
        </h2>

        <p class="text-sm text-[#60756B] mb-6">
            Essa ação não poderá ser desfeita. O aviso será removido da plataforma.
        </p>

        <input type="hidden" id="idAvisoExcluir">

        <div class="flex gap-3">
            <button type="button"
                    onclick="fecharModalExcluirAviso()"
                    class="w-1/2 px-4 py-3 rounded-2xl bg-gray-100 text-gray-700 font-bold hover:bg-gray-200 transition">
                Cancelar
            </button>

            <button type="button"
                    onclick="confirmarExcluirAviso()"
                    class="w-1/2 px-4 py-3 rounded-2xl bg-red-600 text-white font-bold hover:bg-red-700 transition">
                Excluir
            </button>
        </div>

    </div>

</div>

<script>
    function abrirModalEditarAviso(id, titulo, mensagem, categoria) {
        const modal = document.getElementById('modalEditarAviso');
        const form = document.getElementById('formEditarAviso');

        if (!modal || !form) return;

        document.getElementById('editarTituloAviso').value = titulo ?? '';
        document.getElementById('editarMensagemAviso').value = mensagem ?? '';

        const categoriaFinal = categoria === 'informativo' ? 'importante' : (categoria ?? 'importante');
        document.getElementById('editarCategoriaAviso').value = categoriaFinal;

        form.action = "/avisos/" + id;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function fecharModalEditarAviso() {
        const modal = document.getElementById('modalEditarAviso');

        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function abrirModalExcluirAviso(id) {
        const modal = document.getElementById('modalExcluirAviso');
        const input = document.getElementById('idAvisoExcluir');

        if (!modal || !input) return;

        input.value = id;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function fecharModalExcluirAviso() {
        const modal = document.getElementById('modalExcluirAviso');

        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function confirmarExcluirAviso() {
        const id = document.getElementById('idAvisoExcluir')?.value;
        const form = document.getElementById('formExcluirAviso' + id);

        if (form) {
            form.submit();
        }
    }

    const modalEditarAviso = document.getElementById('modalEditarAviso');
    const modalExcluirAviso = document.getElementById('modalExcluirAviso');

    if (modalEditarAviso) {
        modalEditarAviso.addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalEditarAviso();
            }
        });
    }

    if (modalExcluirAviso) {
        modalExcluirAviso.addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalExcluirAviso();
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            fecharModalEditarAviso();
            fecharModalExcluirAviso();
        }
    });
</script>

@endsection