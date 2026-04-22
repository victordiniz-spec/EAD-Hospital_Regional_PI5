@extends('layout.app')

@section('title', 'Videoaulas')

@section('content')

<div class="flex min-h-screen">

    <!-- SIDEBAR PADRÃO -->
    @include('partials.sidebar-professor')

    <!-- CONTEÚDO -->
    <main class="flex-1 p-8 bg-[#0B1120] text-white">

        <!-- HEADER -->
        <div class="flex justify-between mb-6">
            <h2 class="text-2xl font-bold">Videoaulas</h2>

            <button onclick="abrirModalAula()" 
                class="bg-green-600 px-5 py-2 rounded hover:bg-green-700 transition">
                + Nova Aula
            </button>
        </div>

        <!-- LISTA -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            @forelse($aulas as $aula)
                <div class="bg-[#1E293B] p-4 rounded-xl shadow">

                    <h3 class="font-bold">{{ $aula->titulo }}</h3>

                    <p class="text-gray-400 text-sm">
                        {{ $aula->descricao }}
                    </p>

                    <p class="text-xs text-green-400 mt-2">
                        📚 {{ $aula->modulo->nome ?? 'Sem módulo' }}
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
                <p class="text-gray-400">Nenhuma aula cadastrada.</p>
            @endforelse

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

                <!-- MODULO -->
                <select id="selectModulo" name="modulo_id"
                    class="w-full p-3 mb-3 bg-[#1E293B] text-white rounded">
                    <option value="">Selecionar módulo</option>
                    @foreach($modulos as $modulo)
                        <option value="{{ $modulo->id }}">{{ $modulo->nome }}</option>
                    @endforeach
                </select>

                <!-- NOVO MODULO -->
                <input type="text" id="novoModulo" name="novo_modulo" placeholder="Ou criar novo módulo"
                    class="w-full p-3 mb-4 bg-[#1E293B] text-white rounded border border-dashed border-green-500">

                <!-- DADOS -->
                <input type="text" name="titulo" placeholder="Título"
                    class="w-full p-3 mb-3 bg-[#1E293B] text-white rounded">

                <textarea name="descricao" placeholder="Descrição"
                    class="w-full p-3 mb-3 bg-[#1E293B] text-white rounded"></textarea>

                <input type="text" name="video_url" placeholder="Link do vídeo"
                    class="w-full p-3 mb-4 bg-[#1E293B] text-white rounded">

                <!-- TESTE -->
                <h3 class="mb-3 font-semibold">🧠 Pós-Teste</h3>

                <input type="text" name="avaliacao[titulo]" placeholder="Título do teste"
                    class="w-full p-3 mb-3 bg-[#1E293B] text-white rounded">

                <input type="number" name="avaliacao[tempo_limite]" placeholder="Tempo (min)"
                    class="w-full p-3 mb-4 bg-[#1E293B] text-white rounded">

                <!-- PERGUNTAS -->
                <div id="perguntas-container"></div>

                <!-- BOTÕES -->
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function abrirModalAula(){
    document.getElementById('modalAula').classList.remove('hidden')
}

function fecharModalAula(){
    document.getElementById('modalAula').classList.add('hidden')
}

function confirmarExclusao(botao){
    Swal.fire({
        title:'Tem certeza?',
        text:'Essa aula será excluída!',
        icon:'warning',
        showCancelButton:true,
        confirmButtonColor:'#dc2626',
        cancelButtonColor:'#2563eb',
        confirmButtonText:'Sim, excluir'
    }).then((r)=>{
        if(r.isConfirmed){
            botao.closest('form').submit()
        }
    })
}
</script>

@endsection