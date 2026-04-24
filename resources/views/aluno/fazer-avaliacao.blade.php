@extends('layout.app')

@section('title', 'Minhas Aulas')

@section('content')

<div class="flex h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-white">

    <!-- SIDEBAR -->
    @include('partials.sidebar-aluno')

    <!-- MAIN -->
    <div class="flex-1 flex flex-col">

        <!-- TOPBAR -->
        <header class="bg-slate-900 border-b border-slate-800 px-8 h-16 flex items-center justify-between shadow">

            <div>
                <h1 class="text-lg font-bold">Minhas Aulas</h1>
                <p class="text-xs text-slate-500">Assista e acompanhe seu progresso</p>
            </div>

        </header>

        <!-- CONTEÚDO -->
        <main class="flex-1 overflow-auto p-8">

            <h2 class="text-xl font-bold mb-6">📚 Módulos do Curso</h2>

            @if(isset($modulos) && count($modulos) > 0)

                @foreach($modulos as $modulo)
                    <div class="bg-slate-900 border border-slate-800 p-5 rounded-xl mb-4 shadow">

                        <!-- TÍTULO DO MÓDULO -->
                        <div class="flex justify-between items-center cursor-pointer"
                             onclick="toggleModulo({{ $modulo->id }})">

                            <h3 class="font-semibold text-lg">
                                ▶ {{ $modulo->nome }}
                            </h3>

                            <span class="text-xs text-slate-400">
                                {{ count($modulo->aulas ?? []) }} aulas
                            </span>
                        </div>

                        <!-- AULAS -->
                        <div id="modulo-{{ $modulo->id }}" class="hidden mt-4 space-y-3">

                            @if(isset($modulo->aulas) && count($modulo->aulas) > 0)

                                @foreach($modulo->aulas as $aula)
                                    <div class="flex justify-between items-center bg-slate-800 p-3 rounded-lg hover:bg-slate-700 transition">

                                        <div>
                                            <p class="font-medium">{{ $aula->titulo }}</p>
                                        </div>

                                        <button onclick="abrirModal('{{ $aula->video_url }}', '{{ $aula->id }}')"
                                            class="bg-emerald-600 px-4 py-1 rounded text-sm hover:bg-emerald-700 transition">
                                            ▶ Assistir
                                        </button>

                                    </div>
                                @endforeach

                            @else
                                <p class="text-sm text-slate-500">Nenhuma aula disponível</p>
                            @endif

                        </div>

                    </div>
                @endforeach

            @else
                <div class="bg-slate-900 p-6 rounded-xl text-center text-slate-400">
                    Nenhum módulo disponível ainda.
                </div>
            @endif

        </main>

    </div>

</div>

<!-- MODAL DE VIDEO -->
<div id="modalVideo" class="fixed inset-0 bg-black bg-opacity-70 hidden items-center justify-center z-50">

    <div class="bg-slate-900 w-[800px] rounded-xl p-4 relative">

        <button onclick="fecharModal()" class="absolute top-2 right-3 text-xl">×</button>

        <iframe id="videoFrame"
            class="w-full h-[400px] rounded"
            src="">
        </iframe>

        <div class="mt-4 flex justify-between">
            <button onclick="fecharModal()" class="bg-gray-600 px-4 py-2 rounded">
                Fechar
            </button>

            <button onclick="marcarAssistida()"
                class="bg-emerald-600 px-4 py-2 rounded">
                ✔ Concluir
            </button>
        </div>

    </div>
</div>

<script>
let aulaIdAtual = null;

function abrirModal(url, aulaId) {
    aulaIdAtual = aulaId;
    document.getElementById('videoFrame').src = url;
    document.getElementById('modalVideo').classList.remove('hidden');
    document.getElementById('modalVideo').classList.add('flex');
}

function fecharModal() {
    document.getElementById('modalVideo').classList.add('hidden');
    document.getElementById('videoFrame').src = "";
}

function marcarAssistida() {
    fetch('/assistir-aula/' + aulaIdAtual)
        .then(() => location.reload());
}

function toggleModulo(id) {
    const el = document.getElementById('modulo-' + id);
    el.classList.toggle('hidden');
}
</script>

@endsection