@extends('layout.app')

@section('title', 'Videoaulas')

@section('content')

<div class="flex min-h-screen">

    @include('partials.sidebar-professor')

    <main class="flex-1 p-8 bg-[#0B1120] text-white">

        <!-- HEADER -->
        <div class="flex justify-between mb-6">
            <h2 class="text-2xl font-bold">Módulos & Videoaulas</h2>

            <button onclick="abrirModalAula()" 
                class="bg-green-600 px-5 py-2 rounded hover:bg-green-700 transition">
                + Nova Aula
            </button>
        </div>

        <!-- 🔥 MÓDULOS -->
        <div class="space-y-4">

            @foreach($modulos as $modulo)

                <div class="bg-[#1E293B] rounded-xl shadow">

                    <div onclick="toggleModulo({{ $modulo->id }})"
                        class="cursor-pointer p-5 flex justify-between items-center hover:bg-[#0F172A] transition">

                        <div>
                            <h3 class="font-bold text-lg">📚 {{ $modulo->nome }}</h3>
                            <p class="text-sm text-gray-400">Clique para ver as aulas</p>
                        </div>

                        <span id="icon-{{ $modulo->id }}">▼</span>
                    </div>

                    <div id="modulo-{{ $modulo->id }}" class="hidden p-4 space-y-4">

                        @php
                            $aulasDoModulo = $aulas->where('modulo_id', $modulo->id);
                        @endphp

                        @forelse($aulasDoModulo as $aula)

                            <div class="bg-[#0F172A] p-4 rounded-lg">

                                <h4 class="font-semibold">{{ $aula->titulo }}</h4>

                                <p class="text-gray-400 text-sm">
                                    {{ $aula->descricao }}
                                </p>

                                <div class="flex gap-2 mt-4">

                                    <a href="{{ route('aulas.assistir', $aula->id) }}"
                                       class="bg-blue-600 px-3 py-1 rounded text-sm hover:bg-blue-700">
                                       Assistir
                                    </a>

                                    <form action="{{ route('aulas.destroy', $aula->id) }}" method="POST">
                                        @csrf 
                                        @method('DELETE')

                                        <button type="button" onclick="confirmarExclusao(this)"
                                            class="bg-red-600 px-3 py-1 rounded text-sm hover:bg-red-700">
                                            Excluir
                                        </button>
                                    </form>

                                </div>

                            </div>

                        @empty
                            <p class="text-gray-400">Nenhuma aula neste módulo.</p>
                        @endforelse

                    </div>

                </div>

            @endforeach

        </div>

    </main>

</div>

<!-- MODAL -->
<div id="modalAula" class="fixed inset-0 hidden bg-black/70 z-50">

    <div class="flex items-center justify-center min-h-screen">

        <div class="bg-[#0F172A] w-[900px] max-h-[90vh] overflow-auto p-6 rounded-2xl shadow-lg">

            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Criar Aula Completa</h2>
                <button onclick="fecharModalAula()" class="text-xl">✖</button>
            </div>

            <form action="{{ route('aulas.store') }}" method="POST">
                @csrf

                <select name="modulo_id"
                    class="w-full p-3 mb-3 bg-[#1E293B] text-white rounded">
                    <option value="">Selecionar módulo</option>
                    @foreach($modulos as $modulo)
                        <option value="{{ $modulo->id }}">{{ $modulo->nome }}</option>
                    @endforeach
                </select>

                <input type="text" name="novo_modulo" placeholder="Ou criar novo módulo"
                    class="w-full p-3 mb-4 bg-[#1E293B] text-white rounded border border-dashed border-green-500">

                <input type="text" name="titulo" placeholder="Título"
                    class="w-full p-3 mb-3 bg-[#1E293B] text-white rounded">

                <textarea name="descricao" placeholder="Descrição"
                    class="w-full p-3 mb-3 bg-[#1E293B] text-white rounded"></textarea>

                <input type="text" name="video_url" placeholder="Link do vídeo"
                    class="w-full p-3 mb-4 bg-[#1E293B] text-white rounded">

                <h3 class="mb-3 font-semibold">🧠 Pós-Teste</h3>

                <input type="text" name="avaliacao[titulo]" placeholder="Título do teste"
                    class="w-full p-3 mb-3 bg-[#1E293B] text-white rounded">

                <input type="number" name="avaliacao[tempo_limite]" placeholder="Tempo (min)"
                    class="w-full p-3 mb-4 bg-[#1E293B] text-white rounded">

                <div id="perguntas-container"></div>

                <div class="flex justify-between mt-4">

                    <button type="button" onclick="addPergunta()" 
                        class="bg-blue-600 px-4 py-2 rounded hover:bg-blue-700">
                        + Pergunta
                    </button>

                    <button class="bg-green-600 px-5 py-2 rounded hover:bg-green-700">
                        Salvar Aula
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>

<script>
let perguntaIndex = 0;

// MODAL
function abrirModalAula(){
    document.getElementById('modalAula').classList.remove('hidden')
}
function fecharModalAula(){
    document.getElementById('modalAula').classList.add('hidden')
}

// MÓDULO
function toggleModulo(id) {
    const modulo = document.getElementById('modulo-' + id);
    const icon = document.getElementById('icon-' + id);

    modulo.classList.toggle('hidden');
    icon.innerText = modulo.classList.contains('hidden') ? '▼' : '▲';
}

// =========================
// 🔥 PERGUNTAS
// =========================
function addPergunta() {

    const container = document.getElementById('perguntas-container');

    let html = `
        <div class="bg-[#1E293B] p-4 rounded-lg mb-4" id="pergunta-${perguntaIndex}">

            <div class="flex justify-between mb-2">
                <strong>Pergunta</strong>
                <button type="button" onclick="removerPergunta(${perguntaIndex})" class="text-red-400">🗑️</button>
            </div>

            <input type="text" name="perguntas[${perguntaIndex}][pergunta]" 
                placeholder="Digite a pergunta"
                class="w-full p-2 mb-3 rounded bg-[#0F172A] text-white">

            <div id="respostas-${perguntaIndex}" class="space-y-2"></div>

            <button type="button" onclick="addResposta(${perguntaIndex})"
                class="text-sm bg-blue-600 px-3 py-1 rounded">
                + Resposta
            </button>

        </div>
    `;

    container.insertAdjacentHTML('beforeend', html);

    addResposta(perguntaIndex);
    addResposta(perguntaIndex);

    perguntaIndex++;
}

// REMOVER PERGUNTA
function removerPergunta(index) {
    document.getElementById(`pergunta-${index}`).remove();
}

// =========================
// 🔥 RESPOSTAS
// =========================
function addResposta(index) {

    const container = document.getElementById(`respostas-${index}`);
    const total = container.children.length;

    let html = `
        <div class="flex items-center gap-2" id="resposta-${index}-${total}">

            <input type="radio" 
                name="perguntas[${index}][correta]" 
                value="${total}">

            <input type="text" 
                name="perguntas[${index}][respostas][]" 
                placeholder="Resposta ${total + 1}"
                class="w-full p-2 rounded bg-[#0F172A] text-white">

            <button type="button" onclick="removerResposta(${index}, ${total})" 
                class="text-red-400">❌</button>

        </div>
    `;

    container.insertAdjacentHTML('beforeend', html);
}

// REMOVER RESPOSTA
function removerResposta(perguntaIndex, respostaIndex) {
    document.getElementById(`resposta-${perguntaIndex}-${respostaIndex}`).remove();
}
</script>

@endsection