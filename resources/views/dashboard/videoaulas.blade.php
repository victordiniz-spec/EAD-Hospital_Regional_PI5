@extends('layout.app')

@section('title', 'Videoaulas')

@section('content')

<div class="flex min-h-screen">

    @include('partials.sidebar-professor')

    <main class="flex-1 p-4 sm:p-8 bg-[#0B1120] text-white">

        <!-- HEADER -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold">Módulos & Videoaulas</h2>
                <p class="text-sm text-gray-400 mt-1">
                    Gerencie módulos, aulas, avaliações, perguntas e respostas.
                </p>
            </div>

            <button
                type="button"
                onclick="abrirModalAula()"
                class="w-full sm:w-auto bg-green-600 px-5 py-2 rounded-lg hover:bg-green-700 transition text-sm font-semibold"
            >
                + Nova Aula
            </button>
        </div>

        <!-- ALERTAS -->
        @if (session('success'))
            <div class="mb-4 bg-green-600 text-white px-4 py-3 rounded-lg shadow">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 bg-red-600 text-white px-4 py-3 rounded-lg shadow">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 bg-red-600 text-white px-4 py-3 rounded-lg shadow">
                <p class="font-semibold mb-2">Corrija os campos abaixo:</p>
                <ul class="list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- MÓDULOS -->
        <div class="space-y-4">

            @forelse ($modulos as $modulo)

                <div class="bg-[#1E293B] rounded-xl shadow overflow-hidden">

                    <!-- Cabeçalho do Módulo -->
                    <div
                        onclick="toggleModulo({{ $modulo->id }})"
                        class="cursor-pointer p-4 sm:p-5 flex justify-between items-center hover:bg-[#0F172A] transition"
                    >
                        <div>
                            <h3 class="font-bold text-base sm:text-lg">{{ $modulo->nome }}</h3>
                            <p class="text-xs sm:text-sm text-gray-400">Clique para ver as aulas</p>
                        </div>

                        <span
                            id="icon-{{ $modulo->id }}"
                            class="text-gray-400 text-sm transition-transform duration-300"
                        >
                            ▼
                        </span>
                    </div>

                    <!-- Aulas do Módulo -->
                    <div id="modulo-{{ $modulo->id }}" class="hidden px-4 pb-4 pt-1 space-y-3">

                        @php
                            $aulasDoModulo = $aulas->where('modulo_id', $modulo->id);
                        @endphp

                        @forelse ($aulasDoModulo as $aula)

                            <div class="bg-[#0F172A] p-4 rounded-lg border border-slate-700">

                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                                    <div>
                                        <h4 class="font-semibold text-sm sm:text-base">
                                            {{ $aula->titulo }}
                                        </h4>

                                        <p class="text-gray-400 text-xs sm:text-sm mt-1">
                                            {{ $aula->descricao ?: 'Sem descrição.' }}
                                        </p>

                                        @if (!empty($aula->video_url))
                                            <p class="text-xs text-gray-500 mt-2 break-all">
                                                Vídeo: {{ $aula->video_url }}
                                            </p>
                                        @endif
                                    </div>

                                    <div class="flex flex-wrap gap-2">

                                        <a
                                            href="{{ route('aulas.assistir', $aula->id) }}"
                                            class="bg-blue-600 px-3 py-1.5 rounded text-xs sm:text-sm font-medium hover:bg-blue-700 transition"
                                        >
                                            Assistir
                                        </a>

                                        <form
                                            action="{{ route('aulas.destroy', $aula->id) }}"
                                            method="POST"
                                            class="inline"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="button"
                                                onclick="confirmarExclusao(this)"
                                                class="bg-red-600 px-3 py-1.5 rounded text-xs sm:text-sm font-medium hover:bg-red-700 transition"
                                            >
                                                Excluir
                                            </button>
                                        </form>

                                    </div>
                                </div>

                            </div>

                        @empty

                            <p class="text-gray-400 text-sm py-2">Nenhuma aula neste módulo.</p>

                        @endforelse

                    </div>

                </div>

            @empty

                <div class="bg-[#1E293B] rounded-xl shadow p-6 text-center">
                    <h3 class="font-bold text-lg mb-2">Nenhum módulo encontrado</h3>
                    <p class="text-gray-400 text-sm mb-4">
                        Clique em <strong>+ Nova Aula</strong> e preencha o campo “Ou criar novo módulo”.
                    </p>

                    <button
                        type="button"
                        onclick="abrirModalAula()"
                        class="bg-green-600 px-5 py-2 rounded-lg hover:bg-green-700 transition text-sm font-semibold"
                    >
                        Criar primeira aula
                    </button>
                </div>

            @endforelse

        </div>

    </main>

</div>

<!-- MODAL CRIAR AULA -->
<div id="modalAula" class="fixed inset-0 hidden items-center justify-center z-50"
    style="background: rgba(0,0,0,0.45); backdrop-filter: blur(4px);">

    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4"
        style="max-height: 90vh; overflow-y: auto;">

        <!-- Header -->
        <div class="flex items-center justify-between px-8 pt-8 pb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Criar Aula Completa</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Cadastre a aula, o módulo e o teste, se desejar.
                </p>
            </div>

            <button
                type="button"
                onclick="fecharModalAula()"
                class="text-gray-400 hover:text-gray-600 transition"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="px-8 pb-8">

            <form action="{{ route('aulas.store') }}" method="POST" id="formAula">
                @csrf

                <!-- Selecionar Módulo -->
                <div class="mb-3">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">
                        Módulo existente
                    </label>

                    <div class="relative">
                        <select
                            name="modulo_id"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-700 text-sm appearance-none focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition cursor-pointer"
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

                        <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    <p class="text-xs text-gray-400 mt-1">
                        Você pode escolher um módulo existente ou criar um novo abaixo.
                    </p>
                </div>

                <!-- Novo Módulo -->
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">
                        Novo módulo
                    </label>

                    <input
                        type="text"
                        name="novo_modulo"
                        value="{{ old('novo_modulo') }}"
                        placeholder="Ou criar novo módulo"
                        class="w-full px-4 py-2.5 rounded-lg border border-dashed border-teal-400 bg-teal-50 text-gray-700 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                    >
                </div>

                <!-- Título -->
                <div class="mb-3">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">
                        Título da aula
                    </label>

                    <input
                        type="text"
                        name="titulo"
                        value="{{ old('titulo') }}"
                        placeholder="Título"
                        required
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-700 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                    >
                </div>

                <!-- Descrição -->
                <div class="mb-3">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">
                        Descrição
                    </label>

                    <textarea
                        name="descricao"
                        placeholder="Descrição"
                        rows="3"
                        class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-50 text-gray-700 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition resize-none"
                    >{{ old('descricao') }}</textarea>
                </div>

                <!-- Link do Vídeo -->
                <div class="mb-6">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">
                        Link do vídeo
                    </label>

                    <input
                        type="text"
                        name="video_url"
                        value="{{ old('video_url') }}"
                        placeholder="Link do vídeo"
                        required
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-700 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                    >
                </div>

                <!-- Pós-Teste -->
                <div class="mb-4 border-t border-gray-200 pt-4">
                    <h3 class="font-semibold text-gray-800">Teste</h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Opcional. Você pode criar a aula sem perguntas e adicionar o teste depois.
                    </p>
                </div>

                <!-- Título do Teste -->
                <div class="mb-3">
                    <input
                        type="text"
                        name="avaliacao[titulo]"
                        value="{{ old('avaliacao.titulo') }}"
                        placeholder="Título do teste"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-700 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                    >
                </div>

                <!-- Tempo (min) -->
                <div class="mb-6">
                    <input
                        type="number"
                        name="avaliacao[tempo_limite]"
                        value="{{ old('avaliacao.tempo_limite') }}"
                        placeholder="Tempo (min)"
                        min="1"
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-700 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                    >
                </div>

                <!-- Container de Perguntas -->
                <div id="perguntas-container" class="space-y-4 mb-4"></div>

                <!-- Botões -->
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-between sm:items-center pt-2">

                    <button
                        type="button"
                        onclick="addPergunta()"
                        class="flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl border border-teal-500 text-teal-700 text-sm font-semibold hover:bg-teal-50 transition"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M12 4v16m8-8H4" />
                        </svg>
                        Pergunta
                    </button>

                    <div class="flex gap-3 justify-end">
                        <button
                            type="button"
                            onclick="fecharModalAula()"
                            class="px-6 py-2.5 rounded-xl border border-gray-200 text-gray-600 text-sm font-medium hover:bg-gray-50 transition"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            id="btnSalvarAula"
                            class="px-6 py-2.5 rounded-xl bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold transition shadow-sm disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            Salvar Aula
                        </button>
                    </div>

                </div>

            </form>

        </div>
    </div>
</div>

<script>
    let perguntaIndex = 0;

    // ─── Modal ────────────────────────────────────────────────────────────────

    function abrirModalAula() {
        const modal = document.getElementById('modalAula');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function fecharModalAula() {
        const modal = document.getElementById('modalAula');
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

    // Fechar ao clicar fora do modal
    const modalAula = document.getElementById('modalAula');

    if (modalAula) {
        modalAula.addEventListener('click', function (e) {
            if (e.target === this) fecharModalAula();
        });
    }

    // Evita duplo envio
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

    // ─── Módulos ──────────────────────────────────────────────────────────────

    function toggleModulo(id) {
        const conteudo = document.getElementById(`modulo-${id}`);
        const icone = document.getElementById(`icon-${id}`);

        if (!conteudo || !icone) return;

        const aberto = !conteudo.classList.contains('hidden');

        conteudo.classList.toggle('hidden', aberto);
        icone.style.transform = aberto ? 'rotate(0deg)' : 'rotate(180deg)';
    }

    // ─── Perguntas ────────────────────────────────────────────────────────────

    function addPergunta() {
        const container = document.getElementById('perguntas-container');

        if (!container) return;

        const div = document.createElement('div');
        div.className = 'border border-gray-200 rounded-xl p-4 bg-gray-50';
        div.id = `pergunta-${perguntaIndex}`;

        div.innerHTML = `
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-widest
                    bg-white border border-gray-200 px-2.5 py-1 rounded-lg">
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
                class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white text-gray-700 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition mb-3"
            >

            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                Alternativas (marque a correta)
            </p>

            <div id="respostas-${perguntaIndex}" class="space-y-2 mb-3"></div>

            <button type="button" onclick="addResposta(${perguntaIndex})"
                class="flex items-center gap-1 text-xs font-semibold text-teal-700 hover:text-teal-900 transition">
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

    // ─── Respostas ────────────────────────────────────────────────────────────

    const letras = ['A', 'B', 'C', 'D', 'E'];

    function addResposta(index) {
        const container = document.getElementById(`respostas-${index}`);

        if (!container) return;

        const total = container.children.length;
        const letra = letras[total] ?? String(total + 1);

        const div = document.createElement('div');
        div.id = `resposta-${index}-${total}`;
        div.className = 'flex items-center gap-3 bg-white border border-gray-200 rounded-lg px-4 py-2.5';

        div.innerHTML = `
            <input type="radio" name="perguntas[${index}][correta]" value="${total}"
                class="w-4 h-4 accent-teal-700 cursor-pointer">

            <span class="text-xs font-bold text-gray-500 w-4">${letra}</span>

            <input
                type="text"
                name="perguntas[${index}][respostas][]"
                placeholder="Texto da alternativa ${letra}..."
                class="flex-1 text-sm text-gray-700 bg-transparent placeholder-gray-400 focus:outline-none"
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

    // ─── Exclusão ─────────────────────────────────────────────────────────────

    function confirmarExclusao(btn) {
        if (confirm('Tem certeza que deseja excluir esta aula?')) {
            btn.closest('form').submit();
        }
    }

    // Reabre modal automaticamente se houver erro de validação ou erro de salvamento
    @if ($errors->any() || session('error'))
        abrirModalAula();
    @endif
</script>

@endsection