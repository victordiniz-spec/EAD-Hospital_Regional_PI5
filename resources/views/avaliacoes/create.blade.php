@extends('layout.app')

@section('title', isset($avaliacao) && $avaliacao ? 'Editar Pós-teste' : 'Criar Pós-teste')

@section('content')

@php
    $tituloAvaliacao = old('avaliacao.titulo', $avaliacao->titulo ?? 'Pós-teste - ' . ($aulaDados->titulo ?? 'Aula'));
    $tempoLimite = old('avaliacao.tempo_limite', $avaliacao->tempo_limite ?? '');
    $cursoId = $aulaDados->curso_id ?? null;
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
            <div class="mb-7 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-5">

                <div>
                    <div class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest text-[#00A63E] mb-2">
                        <span class="w-2 h-2 rounded-full bg-[#00A63E]"></span>
                        Avaliação da aula
                    </div>

                    <h1 class="text-3xl sm:text-4xl font-extrabold text-[#003C2F] tracking-tight">
                        {{ isset($avaliacao) && $avaliacao ? 'Editar Pós-teste' : 'Criar Pós-teste' }}
                    </h1>

                    <p class="text-sm text-[#60756B] mt-2 max-w-3xl">
                        Aula: <strong>{{ $aulaDados->titulo ?? 'Aula não encontrada' }}</strong>
                    </p>
                </div>

                <a href="{{ route('videoaulas', ['curso_id' => $cursoId]) }}"
                   class="bg-white border border-[#DCE7DE] text-[#004D3A] px-5 py-3 rounded-2xl hover:bg-[#F8FBF8] transition flex items-center justify-center gap-2 text-sm font-extrabold shadow-sm">
                    Voltar para videoaulas
                </a>

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

            <form method="POST" action="{{ route('avaliacoes.store') }}" id="formAvaliacao">
                @csrf

                <input type="hidden" name="aula_id" value="{{ $aula }}">
                <input type="hidden" name="curso_id" value="{{ $cursoId }}">

                <div class="grid grid-cols-1 xl:grid-cols-12 gap-7">

                    <!-- FORMULÁRIO PRINCIPAL -->
                    <div class="xl:col-span-8 space-y-5">

                        <!-- DADOS DO TESTE -->
                        <div class="bg-white border border-[#E3EBE4] rounded-3xl shadow-sm p-5 sm:p-6">

                            <div class="flex items-start gap-3 mb-6">
                                <div class="w-12 h-12 rounded-2xl bg-[#EAF5EF] text-[#004D3A] flex items-center justify-center text-2xl shrink-0">
                                    📝
                                </div>

                                <div>
                                    <h2 class="text-xl font-extrabold text-[#003C2F]">
                                        Informações do pós-teste
                                    </h2>

                                    <p class="text-xs text-[#60756B] mt-1">
                                        Defina o título e o tempo limite para o aluno responder.
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                                <div class="md:col-span-2">
                                    <label class="block text-[11px] uppercase tracking-widest font-extrabold text-[#60756B] mb-2">
                                        Título
                                    </label>

                                    <input type="text"
                                           name="avaliacao[titulo]"
                                           value="{{ $tituloAvaliacao }}"
                                           required
                                           class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-bold placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition">
                                </div>

                                <div>
                                    <label class="block text-[11px] uppercase tracking-widest font-extrabold text-[#60756B] mb-2">
                                        Tempo limite
                                    </label>

                                    <input type="number"
                                           name="avaliacao[tempo_limite]"
                                           value="{{ $tempoLimite }}"
                                           min="1"
                                           placeholder="Minutos"
                                           class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-bold placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition">
                                </div>

                            </div>

                        </div>

                        <!-- PERGUNTAS -->
                        <div class="bg-white border border-[#E3EBE4] rounded-3xl shadow-sm overflow-hidden">

                            <div class="p-5 sm:p-6 border-b border-[#E3EBE4] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                                <div>
                                    <h2 class="text-xl font-extrabold text-[#003C2F]">
                                        Perguntas
                                    </h2>

                                    <p class="text-xs text-[#60756B] mt-1">
                                        Edite as perguntas e marque a alternativa correta.
                                    </p>
                                </div>

                                <button type="button"
                                        onclick="adicionarPergunta()"
                                        class="bg-[#004D3A] hover:bg-[#003C2F] text-white px-5 py-3 rounded-2xl font-extrabold transition shadow-sm text-sm">
                                    Adicionar pergunta
                                </button>

                            </div>

                            <div class="p-5 sm:p-6">
                                <div id="perguntasContainer" class="space-y-5"></div>
                            </div>

                        </div>

                    </div>

                    <!-- PAINEL DIREITO -->
                    <aside class="xl:col-span-4 space-y-5">

                        <div class="bg-white border border-[#E3EBE4] rounded-3xl shadow-sm p-5">

                            <div class="flex items-start gap-3 mb-5">
                                <div class="w-11 h-11 rounded-2xl bg-[#EAF5EF] text-[#004D3A] flex items-center justify-center text-xl shrink-0">
                                    ✅
                                </div>

                                <div>
                                    <h2 class="font-extrabold text-lg text-[#003C2F]">
                                        Resumo
                                    </h2>

                                    <p class="text-xs text-[#60756B] mt-1">
                                        Confira antes de salvar.
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-4">
                                    <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                        Aula
                                    </p>

                                    <p class="text-sm font-extrabold text-[#003C2F] mt-1">
                                        {{ $aulaDados->titulo ?? 'Aula' }}
                                    </p>
                                </div>

                                <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-4">
                                    <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                        Status
                                    </p>

                                    <p class="text-sm font-extrabold text-[#004D3A] mt-1">
                                        {{ isset($avaliacao) && $avaliacao ? 'Editando pós-teste existente' : 'Novo pós-teste' }}
                                    </p>
                                </div>

                                <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-4">
                                    <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                        Perguntas
                                    </p>

                                    <p class="text-2xl font-extrabold text-[#004D3A] mt-1" id="contadorPerguntas">
                                        0
                                    </p>
                                </div>
                            </div>

                            <button type="submit"
                                    id="btnSalvarAvaliacao"
                                    class="w-full bg-[#004D3A] hover:bg-[#003C2F] text-white px-5 py-4 rounded-2xl font-extrabold transition shadow-sm mt-5">
                                Salvar pós-teste
                            </button>

                        </div>

                    </aside>

                </div>

            </form>

        </section>

    </main>

</div>

<script>
    let perguntaIndex = 0;

    const perguntasExistentes = @json($perguntas ?? []);

    function atualizarContadorPerguntas() {
        const total = document.querySelectorAll('.card-pergunta').length;
        const contador = document.getElementById('contadorPerguntas');

        if (contador) {
            contador.innerText = total;
        }
    }

    function adicionarPergunta(dados = null) {
        const container = document.getElementById('perguntasContainer');

        if (!container) return;

        const index = perguntaIndex;
        const perguntaTexto = dados?.pergunta ?? '';

        const respostas = dados?.respostas ?? [
            { resposta: '', correta: true },
            { resposta: '', correta: false },
            { resposta: '', correta: false },
            { resposta: '', correta: false }
        ];

        const card = document.createElement('div');
        card.className = 'card-pergunta bg-[#F8FBF8] border border-[#E3EBE4] rounded-3xl p-5';
        card.id = `pergunta-card-${index}`;

        card.innerHTML = `
            <div class="flex items-start justify-between gap-4 mb-4">
                <div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-white border border-[#DCE7DE] text-[11px] font-extrabold text-[#60756B] uppercase tracking-widest">
                        Pergunta ${index + 1}
                    </span>
                </div>

                <button type="button"
                        onclick="removerPergunta(${index})"
                        class="text-red-600 font-bold text-sm hover:text-red-700">
                    Remover
                </button>
            </div>

            <label class="block text-[11px] uppercase tracking-widest font-extrabold text-[#60756B] mb-2">
                Enunciado
            </label>

            <input type="text"
                   name="perguntas[${index}][pergunta]"
                   value="${escapeHtml(perguntaTexto)}"
                   placeholder="Digite a pergunta..."
                   required
                   class="w-full px-4 py-3 rounded-2xl bg-white border border-[#DCE7DE] text-[#003C2F] text-sm font-bold placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition mb-4">

            <div class="space-y-3" id="respostas-${index}"></div>
        `;

        container.appendChild(card);

        const respostasContainer = document.getElementById(`respostas-${index}`);

        respostas.forEach((resposta, respostaIndex) => {
            adicionarResposta(index, respostaIndex, resposta);
        });

        perguntaIndex++;
        atualizarContadorPerguntas();
    }

    function adicionarResposta(perguntaIdx, respostaIdx, respostaDados = null) {
        const respostasContainer = document.getElementById(`respostas-${perguntaIdx}`);

        if (!respostasContainer) return;

        const letras = ['A', 'B', 'C', 'D', 'E'];
        const texto = respostaDados?.resposta ?? '';
        const correta = respostaDados?.correta == 1 || respostaDados?.correta === true;

        const div = document.createElement('div');
        div.className = 'bg-white border border-[#DCE7DE] rounded-2xl px-4 py-3 flex items-center gap-3';

        div.innerHTML = `
            <input type="radio"
                   name="perguntas[${perguntaIdx}][correta]"
                   value="${respostaIdx}"
                   ${correta ? 'checked' : ''}
                   class="w-4 h-4 accent-[#004D3A] cursor-pointer">

            <span class="text-xs font-extrabold text-[#60756B] w-5">
                ${letras[respostaIdx] ?? respostaIdx + 1}
            </span>

            <input type="text"
                   name="perguntas[${perguntaIdx}][respostas][]"
                   value="${escapeHtml(texto)}"
                   placeholder="Digite a alternativa..."
                   required
                   class="flex-1 bg-transparent text-sm text-[#003C2F] placeholder-[#8A9B92] focus:outline-none">
        `;

        respostasContainer.appendChild(div);
    }

    function removerPergunta(index) {
        const card = document.getElementById(`pergunta-card-${index}`);

        if (card) {
            card.remove();
        }

        atualizarContadorPerguntas();
    }

    function escapeHtml(texto) {
        return String(texto ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;');
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (perguntasExistentes && perguntasExistentes.length > 0) {
            perguntasExistentes.forEach((pergunta) => {
                adicionarPergunta(pergunta);
            });
        } else {
            adicionarPergunta();
        }
    });

    const form = document.getElementById('formAvaliacao');

    if (form) {
        form.addEventListener('submit', function () {
            const btn = document.getElementById('btnSalvarAvaliacao');

            if (btn) {
                btn.disabled = true;
                btn.innerText = 'Salvando...';
            }
        });
    }
</script>

@endsection