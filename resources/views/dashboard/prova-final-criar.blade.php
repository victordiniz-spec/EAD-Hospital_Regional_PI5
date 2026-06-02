@extends('layout.app')

@section('title', 'Criar Prova Final')

@section('content')

@php
    use Illuminate\Support\Facades\DB;

    /*
    |--------------------------------------------------------------------------
    | PROVA FINAL EXISTENTE
    |--------------------------------------------------------------------------
    | Esta tela agora carrega a prova final já cadastrada para edição.
    | Se não houver prova final, abre o formulário em branco.
    */

    $provaFinalExistente = DB::table('avaliacoes')
        ->where('tipo', 'final')
        ->orderBy('id', 'desc')
        ->first();

    $perguntasExistentes = collect();

    if ($provaFinalExistente) {
        $perguntasExistentes = DB::table('perguntas')
            ->where('avaliacao_id', $provaFinalExistente->id)
            ->orderBy('id')
            ->get();

        foreach ($perguntasExistentes as $perguntaExistente) {
            $perguntaExistente->respostas = DB::table('respostas')
                ->where('pergunta_id', $perguntaExistente->id)
                ->orderBy('id')
                ->get();
        }
    }

    $totalPerguntasRender = $perguntasExistentes->count() > 0
        ? $perguntasExistentes->count()
        : 1;

    $tituloProva = old('titulo', data_get($provaFinalExistente, 'titulo', 'Prova Final'));
    $notaMinima = old('nota_minima', data_get($provaFinalExistente, 'nota_minima', 70));
    $tempoLimite = old('tempo_limite', data_get($provaFinalExistente, 'tempo_limite', 60));
    $tentativas = old('tentativas', data_get($provaFinalExistente, 'tentativas', 2));
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

    @media (max-width: 768px) {
        .modal-scroll-mobile {
            max-height: 88vh !important;
            overflow-y: auto !important;
        }
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

                    <h1 class="text-3xl sm:text-4xl font-extrabold text-[#003C2F] tracking-tight">
                        Gerenciar Prova Final
                    </h1>

                    <p class="text-sm text-[#60756B] mt-2 max-w-2xl">
                        Configure ou edite a avaliação final. A prova será liberada ao aluno quando ele concluir pelo menos 70% do curso atual.
                    </p>
                </div>

                <button
                    type="button"
                    onclick="addPergunta()"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-[#EAF5EF] text-[#004D3A] border border-[#BFD8C5] px-5 py-3 rounded-2xl shadow-sm hover:bg-[#DFF1E5] transition text-sm font-extrabold"
                >
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-5 h-5"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>

                    Adicionar Nova Questão
                </button>

            </div>

            <!-- PROVA FINAL EXISTENTE -->
            @if($provaFinalExistente)
                <div class="mb-6 bg-white border border-[#BFD8C5] rounded-3xl shadow-sm p-5 sm:p-6">

                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">

                        <div class="flex items-start gap-4">

                            <div class="w-14 h-14 rounded-2xl bg-[#EAF5EF] text-[#004D3A] flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-7 h-7"
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
                                <h2 class="text-lg sm:text-xl font-extrabold text-[#003C2F]">
                                    Prova final já cadastrada
                                </h2>

                                <p class="text-sm text-[#60756B] mt-1">
                                    Os dados abaixo foram carregados da prova existente. Edite o que desejar e clique em <strong>Atualizar Prova</strong>.
                                </p>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span class="inline-flex items-center bg-[#F8FBF8] border border-[#E3EBE4] text-[#004D3A] px-3 py-1 rounded-full text-xs font-bold">
                                        {{ $provaFinalExistente->titulo ?? 'Prova Final' }}
                                    </span>

                                    <span class="inline-flex items-center bg-[#F8FBF8] border border-[#E3EBE4] text-[#60756B] px-3 py-1 rounded-full text-xs font-bold">
                                        Mínimo: {{ $tempoMinimo }} minutos
                                    </span>

                                    <span class="inline-flex items-center bg-[#F8FBF8] border border-[#E3EBE4] text-[#60756B] px-3 py-1 rounded-full text-xs font-bold">
                                        Máximo: {{ $tempoLimite }} minutos
                                    </span>

                                    <span class="inline-flex items-center bg-[#F8FBF8] border border-[#E3EBE4] text-[#60756B] px-3 py-1 rounded-full text-xs font-bold">
                                        {{ $perguntasExistentes->count() }} questão(ões)
                                    </span>
                                </div>
                            </div>

                        </div>

                        <button
                            type="button"
                            onclick="scrollParaFormularioProva()"
                            class="w-full lg:w-auto bg-[#004D3A] hover:bg-[#003C2F] text-white px-5 py-3 rounded-2xl font-extrabold text-sm transition shadow-sm"
                        >
                            Editar prova existente
                        </button>

                    </div>

                </div>
            @else
                <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-3xl shadow-sm p-5 sm:p-6">
                    <h2 class="text-lg sm:text-xl font-extrabold text-yellow-800">
                        Nenhuma prova final cadastrada
                    </h2>

                    <p class="text-sm text-yellow-700 mt-1">
                        Preencha os dados abaixo para publicar a primeira prova final.
                    </p>
                </div>
            @endif

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

            @if($errors->any())
                <div class="mb-5 bg-red-100 text-red-700 px-4 py-3 rounded-2xl border border-red-200 shadow-sm">
                    <p class="font-bold mb-2">Corrija os campos abaixo:</p>

                    <ul class="list-disc pl-5 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('prova.final.store') }}" method="POST" id="formProvaFinal">
                @csrf

                @if($provaFinalExistente)
                    <input type="hidden" name="avaliacao_id" value="{{ $provaFinalExistente->id }}">
                    <input type="hidden" name="modo" value="editar">
                @else
                    <input type="hidden" name="modo" value="criar">
                @endif

                <div class="grid grid-cols-1 xl:grid-cols-12 gap-7">

                    <!-- CONFIGURAÇÕES -->
                    <aside class="xl:col-span-4 2xl:col-span-3">

                        <div id="configuracoesProvaFinal" class="bg-white rounded-3xl border border-[#E3EBE4] shadow-sm p-5 sm:p-6 sticky top-6">

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
                                              d="M10.5 6h9.75M10.5 12h9.75M10.5 18h9.75M3.75 6h.008v.008H3.75V6zm0 6h.008v.008H3.75V12zm0 6h.008v.008H3.75V18z"/>
                                    </svg>
                                </div>

                                <div>
                                    <h2 class="text-xl font-extrabold text-[#003C2F] leading-tight">
                                        Configurações Globais
                                    </h2>

                                    <p class="text-xs text-[#60756B] mt-1">
                                        Defina os dados principais da prova.
                                    </p>
                                </div>

                            </div>

                            <!-- TÍTULO -->
                            <div class="mb-5">
                                <label class="block text-[11px] font-extrabold text-[#60756B] uppercase tracking-widest mb-2">
                                    Título da prova
                                </label>

                                <input
                                    type="text"
                                    name="titulo"
                                    value="{{ $tituloProva }}"
                                    placeholder="Ex: Prova Final"
                                    required
                                    class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-[#F8FBF8] text-[#003C2F] text-sm font-bold placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition"
                                >
                            </div>

                            <!-- NOTA MÍNIMA -->
                            <div class="mb-5">
                                <label class="block text-[11px] font-extrabold text-[#60756B] uppercase tracking-widest mb-2">
                                    Nota mínima para aprovação
                                </label>

                                <div class="flex items-center gap-2">
                                    <input
                                        type="number"
                                        name="nota_minima"
                                        id="nota_minima"
                                        value="{{ $notaMinima }}"
                                        min="0"
                                        max="100"
                                        class="w-full px-4 py-4 rounded-2xl border border-[#DCE7DE] bg-[#F1F6F2] text-[#003C2F] text-xl font-extrabold focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition"
                                    >

                                    <span class="text-xl font-extrabold text-[#A5B7AB]">%</span>
                                </div>

                                <p class="text-[11px] text-[#60756B] mt-2">
                                    Percentual de acertos necessário para emissão do certificado.
                                </p>
                            </div>

                            <!-- TEMPO MÍNIMO -->
                            <div class="mb-5">
                                <label class="block text-[11px] font-extrabold text-[#60756B] uppercase tracking-widest mb-2">
                                    Tempo mínimo para finalizar
                                </label>

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
                                                      d="M12 6v6l4 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                                            </svg>
                                        </div>

                                        <span class="text-sm font-bold text-[#60756B]">
                                            Minutos
                                        </span>
                                    </div>

                                    <input
                                        type="number"
                                        name="tempo_minimo"
                                        id="tempo_minimo"
                                        value="{{ $tempoMinimo }}"
                                        min="0"
                                        class="w-24 px-3 py-2 rounded-xl bg-white border border-[#DCE7DE] text-[#004D3A] text-center font-extrabold focus:outline-none focus:ring-2 focus:ring-[#00A63E]"
                                    >
                                </div>

                                <p class="text-[11px] text-[#60756B] mt-2">
                                    O aluno só poderá enviar a prova final depois de atingir esse tempo. Use 0 para não exigir tempo mínimo.
                                </p>
                            </div>

                            <!-- TEMPO MÁXIMO -->
                            <div class="mb-5">
                                <label class="block text-[11px] font-extrabold text-[#60756B] uppercase tracking-widest mb-2">
                                    Tempo máximo para responder
                                </label>

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

                                        <span class="text-sm font-bold text-[#60756B]">
                                            Minutos
                                        </span>
                                    </div>

                                    <input
                                        type="number"
                                        name="tempo_limite"
                                        value="{{ $tempoLimite }}"
                                        min="1"
                                        class="w-24 px-3 py-2 rounded-xl bg-white border border-[#DCE7DE] text-[#004D3A] text-center font-extrabold focus:outline-none focus:ring-2 focus:ring-[#00A63E]"
                                    >
                                </div>
                            </div>

                            <!-- TENTATIVAS -->
                            <div class="mb-5">
                                <label class="block text-[11px] font-extrabold text-[#60756B] uppercase tracking-widest mb-3">
                                    Número máximo de tentativas
                                </label>

                                <div class="flex items-center justify-between bg-[#F1F6F2] rounded-full p-2">
                                    <button
                                        type="button"
                                        onclick="alterarTentativas(-1)"
                                        class="w-10 h-10 rounded-full bg-white text-[#004D3A] font-extrabold hover:bg-[#EAF5EF] transition"
                                    >
                                        −
                                    </button>

                                    <input
                                        type="number"
                                        name="tentativas"
                                        id="tentativas"
                                        value="{{ $tentativas }}"
                                        min="1"
                                        class="w-16 bg-transparent text-center text-lg font-extrabold text-[#004D3A] focus:outline-none"
                                    >

                                    <button
                                        type="button"
                                        onclick="alterarTentativas(1)"
                                        class="w-10 h-10 rounded-full bg-white text-[#004D3A] font-extrabold hover:bg-[#EAF5EF] transition"
                                    >
                                        +
                                    </button>
                                </div>
                            </div>

                            <!-- AVISO -->
                            <div class="bg-green-50 border border-green-100 rounded-2xl p-4 text-green-800 text-xs leading-relaxed">
                                <div class="flex items-start gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="w-5 h-5 shrink-0"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="1.8"
                                              d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                                    </svg>

                                    <span>
                                        A prova final deve conter perguntas objetivas com apenas uma alternativa correta.
                                    </span>
                                </div>
                            </div>

                        </div>

                    </aside>

                    <!-- QUESTÕES -->
                    <div class="xl:col-span-8 2xl:col-span-9">

                        <div id="perguntas-container" class="space-y-5">

                            @if($perguntasExistentes->count() > 0)

                                @foreach($perguntasExistentes as $indexPergunta => $perguntaExistente)

                                    @php
                                        $respostasDaPergunta = collect($perguntaExistente->respostas ?? []);

                                        $respostasParaRenderizar = collect();

                                        for ($i = 0; $i < 4; $i++) {
                                            $respostasParaRenderizar->push($respostasDaPergunta->get($i));
                                        }

                                        $corretaIndex = 1;

                                        foreach ($respostasParaRenderizar as $idxResposta => $respostaExistente) {
                                            if ($respostaExistente && (int) data_get($respostaExistente, 'correta', 0) === 1) {
                                                $corretaIndex = $idxResposta + 1;
                                            }
                                        }
                                    @endphp

                                    <div class="pergunta-bloco bg-white border border-[#E3EBE4] rounded-3xl shadow-sm overflow-hidden" data-index="{{ $indexPergunta }}">

                                        <input type="hidden" name="perguntas[{{ $indexPergunta }}][id]" value="{{ $perguntaExistente->id }}">

                                        <div class="bg-[#F8FBF8] border-b border-[#E3EBE4] px-5 sm:px-6 py-4 flex items-center justify-between gap-4">

                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-[#004D3A] text-white flex items-center justify-center text-sm font-extrabold numero-questao">
                                                    {{ str_pad($indexPergunta + 1, 2, '0', STR_PAD_LEFT) }}
                                                </div>

                                                <div>
                                                    <p class="text-[11px] uppercase tracking-widest font-extrabold text-[#60756B]">
                                                        Questão de múltipla escolha
                                                    </p>

                                                    <p class="text-xs text-[#8A9B92] mt-1">
                                                        Configure pergunta, peso e alternativas.
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <button
                                                    type="button"
                                                    onclick="duplicarPergunta(this)"
                                                    class="w-9 h-9 rounded-xl hover:bg-white text-[#60756B] hover:text-[#004D3A] transition flex items-center justify-center"
                                                    title="Duplicar questão"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                         class="w-5 h-5"
                                                         fill="none"
                                                         viewBox="0 0 24 24"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round"
                                                              stroke-linejoin="round"
                                                              stroke-width="1.8"
                                                              d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75A1.125 1.125 0 0 1 3.75 20.625v-9.75c0-.621.504-1.125 1.125-1.125H8.25m3-6h8.25c.621 0 1.125.504 1.125 1.125v8.25c0 .621-.504 1.125-1.125 1.125h-8.25A1.125 1.125 0 0 1 10.125 13.125v-8.25c0-.621.504-1.125 1.125-1.125z"/>
                                                    </svg>
                                                </button>

                                                <button
                                                    type="button"
                                                    onclick="removerPergunta(this)"
                                                    class="w-9 h-9 rounded-xl hover:bg-red-50 text-[#60756B] hover:text-red-600 transition flex items-center justify-center"
                                                    title="Excluir questão"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                         class="w-5 h-5"
                                                         fill="none"
                                                         viewBox="0 0 24 24"
                                                         stroke="currentColor">
                                                        <path stroke-linecap="round"
                                                              stroke-linejoin="round"
                                                              stroke-width="1.8"
                                                              d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </div>

                                        </div>

                                        <div class="p-5 sm:p-6">

                                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-6">

                                                <div class="lg:col-span-9">
                                                    <label class="block text-[11px] font-extrabold text-[#60756B] uppercase tracking-widest mb-2">
                                                        Texto da pergunta
                                                    </label>

                                                    <textarea
                                                        name="perguntas[{{ $indexPergunta }}][pergunta]"
                                                        rows="4"
                                                        placeholder="Digite o texto da pergunta..."
                                                        required
                                                        class="w-full px-4 py-4 rounded-2xl border border-[#DCE7DE] bg-[#F1F6F2] text-[#003C2F] text-sm placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition resize-none"
                                                    >{{ old("perguntas.$indexPergunta.pergunta", $perguntaExistente->pergunta ?? '') }}</textarea>
                                                </div>

                                                <div class="lg:col-span-3">
                                                    <label class="block text-[11px] font-extrabold text-[#60756B] uppercase tracking-widest mb-2">
                                                        Peso / pontos
                                                    </label>

                                                    <input
                                                        type="number"
                                                        name="perguntas[{{ $indexPergunta }}][peso]"
                                                        value="{{ old("perguntas.$indexPergunta.peso", data_get($perguntaExistente, 'peso', 10)) }}"
                                                        min="1"
                                                        class="w-full px-4 py-4 rounded-2xl border border-[#DCE7DE] bg-[#F1F6F2] text-[#004D3A] text-xl text-center font-extrabold focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition"
                                                    >
                                                </div>

                                            </div>

                                            <div>
                                                <label class="block text-[11px] font-extrabold text-[#60756B] uppercase tracking-widest mb-3">
                                                    Alternativas e resposta correta
                                                </label>

                                                <div class="space-y-3 respostas-container">

                                                    @foreach(['A', 'B', 'C', 'D'] as $i => $letra)
                                                        @php
                                                            $valorResposta = $i + 1;
                                                            $respostaExistente = $respostasParaRenderizar->get($i);
                                                            $respostaTexto = $respostaExistente->resposta ?? '';
                                                            $respostaId = $respostaExistente->id ?? null;

                                                            $respostaOld = old("perguntas.$indexPergunta.respostas.$valorResposta", $respostaTexto);
                                                            $corretaOld = old("perguntas.$indexPergunta.correta", $corretaIndex);
                                                        @endphp

                                                        <div class="flex items-center gap-3 alternativa-item">

                                                            @if($respostaId)
                                                                <input type="hidden"
                                                                       name="perguntas[{{ $indexPergunta }}][respostas_ids][{{ $valorResposta }}]"
                                                                       value="{{ $respostaId }}">
                                                            @endif

                                                            <div class="w-10 h-10 rounded-full bg-[#E8EFE9] text-[#004D3A] flex items-center justify-center font-extrabold shrink-0">
                                                                {{ $letra }}
                                                            </div>

                                                            <input
                                                                type="text"
                                                                name="perguntas[{{ $indexPergunta }}][respostas][{{ $valorResposta }}]"
                                                                value="{{ $respostaOld }}"
                                                                placeholder="Alternativa {{ $letra }}"
                                                                required
                                                                class="flex-1 px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-[#F1F6F2] text-[#003C2F] text-sm placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition"
                                                            >

                                                            <label class="w-10 h-10 rounded-full border border-[#DCE7DE] bg-[#F8FBF8] flex items-center justify-center cursor-pointer hover:bg-green-50 transition shrink-0">
                                                                <input
                                                                    type="radio"
                                                                    name="perguntas[{{ $indexPergunta }}][correta]"
                                                                    value="{{ $valorResposta }}"
                                                                    class="hidden peer"
                                                                    {{ (int) $corretaOld === (int) $valorResposta ? 'checked' : '' }}
                                                                >

                                                                <span class="w-6 h-6 rounded-full border border-[#AFC5B5] flex items-center justify-center peer-checked:bg-[#00A63E] peer-checked:border-[#00A63E]">
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

                                                        </div>
                                                    @endforeach

                                                </div>
                                            </div>

                                        </div>

                                    </div>

                                @endforeach

                            @else

                                <!-- PRIMEIRA QUESTÃO EM BRANCO -->
                                <div class="pergunta-bloco bg-white border border-[#E3EBE4] rounded-3xl shadow-sm overflow-hidden" data-index="0">

                                    <div class="bg-[#F8FBF8] border-b border-[#E3EBE4] px-5 sm:px-6 py-4 flex items-center justify-between gap-4">

                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-[#004D3A] text-white flex items-center justify-center text-sm font-extrabold numero-questao">
                                                01
                                            </div>

                                            <div>
                                                <p class="text-[11px] uppercase tracking-widest font-extrabold text-[#60756B]">
                                                    Questão de múltipla escolha
                                                </p>

                                                <p class="text-xs text-[#8A9B92] mt-1">
                                                    Configure pergunta, peso e alternativas.
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <button
                                                type="button"
                                                onclick="duplicarPergunta(this)"
                                                class="w-9 h-9 rounded-xl hover:bg-white text-[#60756B] hover:text-[#004D3A] transition flex items-center justify-center"
                                                title="Duplicar questão"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                     class="w-5 h-5"
                                                     fill="none"
                                                     viewBox="0 0 24 24"
                                                     stroke="currentColor">
                                                    <path stroke-linecap="round"
                                                          stroke-linejoin="round"
                                                          stroke-width="1.8"
                                                          d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75A1.125 1.125 0 0 1 3.75 20.625v-9.75c0-.621.504-1.125 1.125-1.125H8.25m3-6h8.25c.621 0 1.125.504 1.125 1.125v8.25c0 .621-.504 1.125-1.125 1.125h-8.25A1.125 1.125 0 0 1 10.125 13.125v-8.25c0-.621.504-1.125 1.125-1.125z"/>
                                                </svg>
                                            </button>

                                            <button
                                                type="button"
                                                onclick="removerPergunta(this)"
                                                class="w-9 h-9 rounded-xl hover:bg-red-50 text-[#60756B] hover:text-red-600 transition flex items-center justify-center"
                                                title="Excluir questão"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                     class="w-5 h-5"
                                                     fill="none"
                                                     viewBox="0 0 24 24"
                                                     stroke="currentColor">
                                                    <path stroke-linecap="round"
                                                          stroke-linejoin="round"
                                                          stroke-width="1.8"
                                                          d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>

                                    </div>

                                    <div class="p-5 sm:p-6">

                                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-6">

                                            <div class="lg:col-span-9">
                                                <label class="block text-[11px] font-extrabold text-[#60756B] uppercase tracking-widest mb-2">
                                                    Texto da pergunta
                                                </label>

                                                <textarea
                                                    name="perguntas[0][pergunta]"
                                                    rows="4"
                                                    placeholder="Digite o texto da pergunta..."
                                                    required
                                                    class="w-full px-4 py-4 rounded-2xl border border-[#DCE7DE] bg-[#F1F6F2] text-[#003C2F] text-sm placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition resize-none"
                                                >{{ old('perguntas.0.pergunta') }}</textarea>
                                            </div>

                                            <div class="lg:col-span-3">
                                                <label class="block text-[11px] font-extrabold text-[#60756B] uppercase tracking-widest mb-2">
                                                    Peso / pontos
                                                </label>

                                                <input
                                                    type="number"
                                                    name="perguntas[0][peso]"
                                                    value="{{ old('perguntas.0.peso', 10) }}"
                                                    min="1"
                                                    class="w-full px-4 py-4 rounded-2xl border border-[#DCE7DE] bg-[#F1F6F2] text-[#004D3A] text-xl text-center font-extrabold focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition"
                                                >
                                            </div>

                                        </div>

                                        <div>
                                            <label class="block text-[11px] font-extrabold text-[#60756B] uppercase tracking-widest mb-3">
                                                Alternativas e resposta correta
                                            </label>

                                            <div class="space-y-3 respostas-container">

                                                @foreach(['A', 'B', 'C', 'D'] as $i => $letra)
                                                    @php
                                                        $valorResposta = $i + 1;
                                                    @endphp

                                                    <div class="flex items-center gap-3 alternativa-item">

                                                        <div class="w-10 h-10 rounded-full bg-[#E8EFE9] text-[#004D3A] flex items-center justify-center font-extrabold shrink-0">
                                                            {{ $letra }}
                                                        </div>

                                                        <input
                                                            type="text"
                                                            name="perguntas[0][respostas][{{ $valorResposta }}]"
                                                            value="{{ old("perguntas.0.respostas.$valorResposta") }}"
                                                            placeholder="Alternativa {{ $letra }}"
                                                            required
                                                            class="flex-1 px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-[#F1F6F2] text-[#003C2F] text-sm placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition"
                                                        >

                                                        <label class="w-10 h-10 rounded-full border border-[#DCE7DE] bg-[#F8FBF8] flex items-center justify-center cursor-pointer hover:bg-green-50 transition shrink-0">
                                                            <input
                                                                type="radio"
                                                                name="perguntas[0][correta]"
                                                                value="{{ $valorResposta }}"
                                                                class="hidden peer"
                                                                {{ old('perguntas.0.correta', 1) == $valorResposta ? 'checked' : '' }}
                                                            >

                                                            <span class="w-6 h-6 rounded-full border border-[#AFC5B5] flex items-center justify-center peer-checked:bg-[#00A63E] peer-checked:border-[#00A63E]">
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

                                                    </div>
                                                @endforeach

                                            </div>
                                        </div>

                                    </div>

                                </div>

                            @endif

                        </div>

                        <!-- ADICIONAR QUESTÃO -->
                        <button
                            type="button"
                            onclick="addPergunta()"
                            class="mt-8 w-full border border-dashed border-[#AFC5B5] text-[#004D3A] rounded-3xl py-5 text-sm font-extrabold hover:bg-[#F8FBF8] transition flex items-center justify-center gap-3"
                        >
                            <span class="w-10 h-10 rounded-full border border-dashed border-[#004D3A] flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-5 h-5"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M12 4.5v15m7.5-7.5h-15"/>
                                </svg>
                            </span>

                            Clique para adicionar mais questões
                        </button>

                    </div>

                </div>

                <!-- BARRA DE SALVAR -->
                <div class="mt-8 bg-white border border-[#E3EBE4] rounded-3xl shadow-sm p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                    <div>
                        <p class="font-extrabold text-[#003C2F]">
                            {{ $provaFinalExistente ? 'Pronto para atualizar?' : 'Pronto para publicar?' }}
                        </p>

                        <p class="text-sm text-[#60756B]">
                            Confira as questões antes de salvar a prova final.
                        </p>
                    </div>

                    <button
                        type="submit"
                        id="btnPublicar"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-[#004D3A] text-white px-7 py-3 rounded-2xl shadow-lg hover:bg-[#003C2F] transition text-sm font-extrabold disabled:opacity-60 disabled:cursor-not-allowed"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="w-5 h-5"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="1.8"
                                  d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L6 12Zm0 0h7.5"/>
                        </svg>

                        {{ $provaFinalExistente ? 'Atualizar Prova' : 'Publicar Prova' }}
                    </button>

                </div>

            </form>

        </section>

    </main>

</div>

<!-- MODAL PROVA FINAL EXISTENTE -->
@if($provaFinalExistente)
    <div id="modalProvaFinalExistente"
         class="fixed inset-0 z-[80] hidden items-center justify-center bg-black/50 backdrop-blur-sm px-4">

        <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl border border-[#E3EBE4] overflow-hidden">

            <div class="p-6 text-center">

                <div class="w-20 h-20 mx-auto rounded-full bg-[#EAF5EF] flex items-center justify-center mb-5">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-10 h-10 text-[#004D3A]"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="1.8"
                              d="M9 12h6m-6 4h6M9 8h6M5 4h14v16H5z"/>
                    </svg>
                </div>

                <h2 class="text-2xl font-extrabold text-[#003C2F] mb-2">
                    Prova final carregada
                </h2>

                <p class="text-sm text-[#60756B] leading-relaxed mb-5">
                    A prova final existente foi carregada no formulário. Edite as informações e salve novamente.
                </p>

                <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-4 text-left mb-6">

                    <p class="text-xs font-extrabold text-[#60756B] uppercase tracking-widest mb-1">
                        Prova encontrada
                    </p>

                    <p class="font-extrabold text-[#003C2F]">
                        {{ $provaFinalExistente->titulo ?? 'Prova Final' }}
                    </p>

                    <p class="text-xs text-[#60756B] mt-1">
                        Questões:
                        <strong>{{ $perguntasExistentes->count() }}</strong>
                    </p>

                    <p class="text-xs text-[#60756B] mt-1">
                        Tempo mínimo:
                        <strong>{{ $tempoMinimo }} minutos</strong>
                    </p>

                    <p class="text-xs text-[#60756B] mt-1">
                        Tempo máximo:
                        <strong>{{ $tempoLimite }} minutos</strong>
                    </p>

                </div>

                <button type="button"
                        onclick="fecharAvisoProvaFinal()"
                        class="w-full px-5 py-3 rounded-2xl bg-[#004D3A] text-white font-bold hover:bg-[#003C2F] transition shadow">
                    Continuar editando
                </button>

            </div>

        </div>

    </div>
@endif

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let contador = {{ $totalPerguntasRender }};

    const letras = ['A', 'B', 'C', 'D'];

    function escapeHtmlProvaFinal(texto) {
        return String(texto ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;');
    }

    function fecharAvisoProvaFinal() {
        const modal = document.getElementById('modalProvaFinalExistente');

        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function abrirAvisoProvaFinalExistente() {
        const modal = document.getElementById('modalProvaFinalExistente');

        if (!modal) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function scrollParaFormularioProva() {
        const alvo = document.getElementById('configuracoesProvaFinal');

        if (alvo) {
            alvo.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }

        abrirAvisoProvaFinalExistente();
    }

    function templatePergunta(index) {
        const numero = String(index + 1).padStart(2, '0');

        let alternativas = '';

        letras.forEach((letra, i) => {
            const valor = i + 1;

            alternativas += `
                <div class="flex items-center gap-3 alternativa-item">

                    <div class="w-10 h-10 rounded-full bg-[#E8EFE9] text-[#004D3A] flex items-center justify-center font-extrabold shrink-0">
                        ${letra}
                    </div>

                    <input
                        type="text"
                        name="perguntas[${index}][respostas][${valor}]"
                        placeholder="Alternativa ${letra}"
                        required
                        class="flex-1 px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-[#F1F6F2] text-[#003C2F] text-sm placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition"
                    >

                    <label class="w-10 h-10 rounded-full border border-[#DCE7DE] bg-[#F8FBF8] flex items-center justify-center cursor-pointer hover:bg-green-50 transition shrink-0">
                        <input
                            type="radio"
                            name="perguntas[${index}][correta]"
                            value="${valor}"
                            class="hidden peer"
                            ${valor === 1 ? 'checked' : ''}
                        >

                        <span class="w-6 h-6 rounded-full border border-[#AFC5B5] flex items-center justify-center peer-checked:bg-[#00A63E] peer-checked:border-[#00A63E]">
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

                </div>
            `;
        });

        return `
            <div class="pergunta-bloco bg-white border border-[#E3EBE4] rounded-3xl shadow-sm overflow-hidden" data-index="${index}">

                <div class="bg-[#F8FBF8] border-b border-[#E3EBE4] px-5 sm:px-6 py-4 flex items-center justify-between gap-4">

                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-[#004D3A] text-white flex items-center justify-center text-sm font-extrabold numero-questao">
                            ${numero}
                        </div>

                        <div>
                            <p class="text-[11px] uppercase tracking-widest font-extrabold text-[#60756B]">
                                Questão de múltipla escolha
                            </p>

                            <p class="text-xs text-[#8A9B92] mt-1">
                                Configure pergunta, peso e alternativas.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            onclick="duplicarPergunta(this)"
                            class="w-9 h-9 rounded-xl hover:bg-white text-[#60756B] hover:text-[#004D3A] transition flex items-center justify-center"
                            title="Duplicar questão"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-5 h-5"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75A1.125 1.125 0 0 1 3.75 20.625v-9.75c0-.621.504-1.125 1.125-1.125H8.25m3-6h8.25c.621 0 1.125.504 1.125 1.125v8.25c0 .621-.504 1.125-1.125 1.125h-8.25A1.125 1.125 0 0 1 10.125 13.125v-8.25c0-.621.504-1.125 1.125-1.125z"/>
                            </svg>
                        </button>

                        <button
                            type="button"
                            onclick="removerPergunta(this)"
                            class="w-9 h-9 rounded-xl hover:bg-red-50 text-[#60756B] hover:text-red-600 transition flex items-center justify-center"
                            title="Excluir questão"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-5 h-5"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>

                </div>

                <div class="p-5 sm:p-6">

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-6">

                        <div class="lg:col-span-9">
                            <label class="block text-[11px] font-extrabold text-[#60756B] uppercase tracking-widest mb-2">
                                Texto da pergunta
                            </label>

                            <textarea
                                name="perguntas[${index}][pergunta]"
                                rows="4"
                                placeholder="Digite o texto da pergunta..."
                                required
                                class="w-full px-4 py-4 rounded-2xl border border-[#DCE7DE] bg-[#F1F6F2] text-[#003C2F] text-sm placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition resize-none"
                            ></textarea>
                        </div>

                        <div class="lg:col-span-3">
                            <label class="block text-[11px] font-extrabold text-[#60756B] uppercase tracking-widest mb-2">
                                Peso / pontos
                            </label>

                            <input
                                type="number"
                                name="perguntas[${index}][peso]"
                                value="10"
                                min="1"
                                class="w-full px-4 py-4 rounded-2xl border border-[#DCE7DE] bg-[#F1F6F2] text-[#004D3A] text-xl text-center font-extrabold focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition"
                            >
                        </div>

                    </div>

                    <div>
                        <label class="block text-[11px] font-extrabold text-[#60756B] uppercase tracking-widest mb-3">
                            Alternativas e resposta correta
                        </label>

                        <div class="space-y-3 respostas-container">
                            ${alternativas}
                        </div>
                    </div>

                </div>

            </div>
        `;
    }

    function addPergunta() {
        const container = document.getElementById('perguntas-container');

        if (!container) return;

        container.insertAdjacentHTML('beforeend', templatePergunta(contador));

        contador++;
        renumerarPerguntas();
    }

    function removerPergunta(btn) {
        const perguntas = document.querySelectorAll('.pergunta-bloco');

        if (perguntas.length <= 1) {
            Swal.fire({
                icon: 'info',
                title: 'Atenção',
                text: 'A prova precisa ter pelo menos uma questão.',
                confirmButtonColor: '#004D3A'
            });

            return;
        }

        Swal.fire({
            title: 'Excluir questão?',
            text: 'Essa questão será removida da prova.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b'
        }).then((result) => {
            if (result.isConfirmed) {
                btn.closest('.pergunta-bloco').remove();
                renumerarPerguntas();
                reindexarPerguntas();
            }
        });
    }

    function duplicarPergunta(btn) {
        const blocoOriginal = btn.closest('.pergunta-bloco');
        const novoIndex = contador;

        const perguntaTexto = blocoOriginal.querySelector('textarea').value;
        const peso = blocoOriginal.querySelector('input[name*="[peso]"]').value || 10;
        const respostas = blocoOriginal.querySelectorAll('.alternativa-item input[type="text"]');
        const correta = blocoOriginal.querySelector('input[type="radio"]:checked')?.value || 1;

        const container = document.getElementById('perguntas-container');

        container.insertAdjacentHTML('beforeend', templatePergunta(novoIndex));

        const novoBloco = container.lastElementChild;

        novoBloco.querySelector('textarea').value = perguntaTexto;
        novoBloco.querySelector('input[name*="[peso]"]').value = peso;

        const novasRespostas = novoBloco.querySelectorAll('.alternativa-item input[type="text"]');

        respostas.forEach((resposta, i) => {
            if (novasRespostas[i]) {
                novasRespostas[i].value = resposta.value;
            }
        });

        const radioCorreta = novoBloco.querySelector(`input[type="radio"][value="${correta}"]`);

        if (radioCorreta) {
            radioCorreta.checked = true;
        }

        contador++;
        renumerarPerguntas();
    }

    function renumerarPerguntas() {
        const perguntas = document.querySelectorAll('.pergunta-bloco');

        perguntas.forEach((bloco, index) => {
            const numero = String(index + 1).padStart(2, '0');
            const numeroEl = bloco.querySelector('.numero-questao');

            if (numeroEl) {
                numeroEl.textContent = numero;
            }
        });
    }

    function reindexarPerguntas() {
        const perguntas = document.querySelectorAll('.pergunta-bloco');

        perguntas.forEach((bloco, index) => {
            bloco.dataset.index = index;

            const perguntaId = bloco.querySelector('input[name*="[id]"]');
            const textarea = bloco.querySelector('textarea[name*="[pergunta]"]');
            const peso = bloco.querySelector('input[name*="[peso]"]');
            const respostas = bloco.querySelectorAll('.alternativa-item input[type="text"]');
            const radios = bloco.querySelectorAll('.alternativa-item input[type="radio"]');
            const idsRespostas = bloco.querySelectorAll('input[name*="[respostas_ids]"]');

            if (perguntaId) {
                perguntaId.name = `perguntas[${index}][id]`;
            }

            if (textarea) {
                textarea.name = `perguntas[${index}][pergunta]`;
            }

            if (peso) {
                peso.name = `perguntas[${index}][peso]`;
            }

            respostas.forEach((input, respostaIndex) => {
                input.name = `perguntas[${index}][respostas][${respostaIndex + 1}]`;
            });

            radios.forEach((radio) => {
                radio.name = `perguntas[${index}][correta]`;
            });

            idsRespostas.forEach((input, respostaIndex) => {
                input.name = `perguntas[${index}][respostas_ids][${respostaIndex + 1}]`;
            });
        });
    }

    function alterarTentativas(valor) {
        const input = document.getElementById('tentativas');

        if (!input) return;

        const atual = parseInt(input.value || 1);
        const novo = Math.max(1, atual + valor);

        input.value = novo;
    }

    const form = document.getElementById('formProvaFinal');

    if (form) {
        form.addEventListener('submit', function(e) {
            reindexarPerguntas();

            const perguntas = document.querySelectorAll('.pergunta-bloco');
            const btn = document.getElementById('btnPublicar');

            const tempoMinimoInput = document.getElementById('tempo_minimo');
            const tempoLimiteInput = form.querySelector('input[name="tempo_limite"]');

            const tempoMinimo = tempoMinimoInput ? parseInt(tempoMinimoInput.value || 0) : 0;
            const tempoLimite = tempoLimiteInput ? parseInt(tempoLimiteInput.value || 0) : 0;

            if (tempoMinimo < 0) {
                e.preventDefault();

                Swal.fire({
                    icon: 'warning',
                    title: 'Tempo mínimo inválido',
                    text: 'O tempo mínimo da prova final não pode ser negativo.',
                    confirmButtonColor: '#004D3A'
                });

                return;
            }

            if (tempoLimite <= 0) {
                e.preventDefault();

                Swal.fire({
                    icon: 'warning',
                    title: 'Tempo máximo obrigatório',
                    text: 'Informe um tempo máximo maior que zero para a prova final.',
                    confirmButtonColor: '#004D3A'
                });

                return;
            }

            if (tempoMinimo > 0 && tempoMinimo > tempoLimite) {
                e.preventDefault();

                Swal.fire({
                    icon: 'warning',
                    title: 'Tempo inválido',
                    text: 'O tempo mínimo não pode ser maior que o tempo máximo da prova final.',
                    confirmButtonColor: '#004D3A'
                });

                return;
            }

            if (perguntas.length === 0) {
                e.preventDefault();

                Swal.fire({
                    icon: 'error',
                    title: 'Nenhuma questão cadastrada',
                    text: 'Adicione pelo menos uma questão para publicar a prova.',
                    confirmButtonColor: '#004D3A'
                });

                return;
            }

            let erro = false;

            perguntas.forEach((bloco) => {
                const pergunta = bloco.querySelector('textarea')?.value.trim();
                const respostas = bloco.querySelectorAll('.alternativa-item input[type="text"]');
                const correta = bloco.querySelector('input[type="radio"]:checked');

                if (!pergunta || !correta) {
                    erro = true;
                }

                respostas.forEach((input) => {
                    if (!input.value.trim()) {
                        erro = true;
                    }
                });
            });

            if (erro) {
                e.preventDefault();

                Swal.fire({
                    icon: 'warning',
                    title: 'Campos incompletos',
                    text: 'Preencha todas as perguntas, alternativas e marque a resposta correta.',
                    confirmButtonColor: '#004D3A'
                });

                return;
            }

            if (btn) {
                btn.disabled = true;
                btn.innerHTML = 'Salvando...';
            }
        });
    }
</script>

@endsection
