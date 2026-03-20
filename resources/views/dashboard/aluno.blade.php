@extends('layout.app')

@section('title', 'Dashboard Aluno')

@section('content')

<div class="flex min-h-screen bg-[#0B1120] text-white">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-[#0F172A] p-6">
        <h1 class="text-xl font-bold mb-8">ResidentEAD</h1>

        <nav class="space-y-4">
            <a href="{{ route('dashboard.aluno') }}" class="block bg-blue-600 p-2 rounded">Dashboard</a>
            <a href="#" class="block hover:text-blue-400">Minhas Aulas</a>
            <a href="#" class="block hover:text-blue-400">Pós-testes</a>
            <a href="#" class="block hover:text-blue-400">Desempenho</a>
        </nav>
    </aside>

    <!-- CONTEÚDO -->
    <main class="flex-1 p-8">

        <h2 class="text-2xl font-bold mb-6">Dashboard</h2>

        <!-- CARDS -->
        <div class="grid grid-cols-4 gap-6 mb-8">

            <div class="bg-[#1E293B] p-6 rounded-xl">
                <p>Progresso Geral</p>
                <h3 class="text-3xl font-bold">{{ number_format($progresso, 0) }}%</h3>
            </div>

            <div class="bg-[#1E293B] p-6 rounded-xl">
                <p>Aulas Assistidas</p>
                <h3 class="text-3xl font-bold">
                    {{ $aulasAssistidas }} / {{ $totalAulas }}
                </h3>
            </div>

            <div class="bg-[#1E293B] p-6 rounded-xl">
                <p>Testes Pendentes</p>
                <h3 class="text-3xl font-bold">{{ $testesPendentes }}</h3>
            </div>

            <div class="bg-[#1E293B] p-6 rounded-xl">
                <p>Média Atual</p>
                <h3 class="text-3xl font-bold">{{ number_format($media, 1) }}</h3>
            </div>

        </div>

        <!-- PRÓXIMAS AULAS -->
        <div class="bg-[#1E293B] p-6 rounded-xl mb-6">
            <h3 class="mb-4">Próximas Aulas</h3>

            @forelse($proximasAulas as $aula)
                <div class="mb-4 flex justify-between items-center">
                    <span>{{ $aula->titulo }}</span>

                    <button onclick="abrirModal('{{ $aula->video_url }}', '{{ $aula->id }}')"
                        class="bg-green-500 px-4 py-2 rounded">
                        Assistir
                    </button>
                </div>
            @empty
                <p>Nenhuma aula pendente</p>
            @endforelse
        </div>

        <!-- AULAS ASSISTIDAS -->
        <div class="bg-[#1E293B] p-6 rounded-xl mb-6">
            <h3 class="mb-4">Aulas Assistidas</h3>

            @forelse($aulasAssistidasLista as $aula)
                <div class="mb-4 flex justify-between items-center">

                    <div>
                        <span>{{ $aula->titulo }}</span>
                        <span class="text-green-400 text-sm ml-2">✔ Assistida</span>
                    </div>

                    <button onclick="abrirModal('{{ $aula->video_url }}', '{{ $aula->id }}')"
                        class="bg-blue-500 px-4 py-2 rounded">
                        Assistir novamente
                    </button>

                </div>
            @empty
                <p>Nenhuma aula assistida ainda</p>
            @endforelse
        </div>

        <!-- TESTES -->
        <div class="bg-[#1E293B] p-6 rounded-xl">
            <h3 class="mb-4">Testes Pendentes</h3>

            @forelse($listaTestes as $teste)
                <div class="mb-4 flex justify-between items-center">
                    <span>{{ $teste->titulo }}</span>
                    <a href="#" class="bg-blue-500 px-4 py-2 rounded">Fazer Teste</a>
                </div>
            @empty
                <p>Nenhum teste pendente</p>
            @endforelse
        </div>

    </main>

</div>

<!-- MODAL -->
<div id="modalVideo" class="fixed inset-0 bg-black bg-opacity-70 hidden items-center justify-center z-50">

    <div class="bg-[#1E293B] w-[800px] rounded-xl p-4 relative">

        <button onclick="fecharModal()" class="absolute top-2 right-3 text-white text-xl">×</button>

        <h2 class="text-lg mb-4">Assistindo Aula</h2>

        <iframe id="videoFrame"
            class="w-full h-[400px] rounded"
            src=""
            frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen>
        </iframe>

        <div class="mt-4 flex justify-between">
            <button onclick="fecharModal()" class="bg-gray-500 px-4 py-2 rounded">
                Fechar
            </button>

            <button onclick="marcarAssistida()"
                class="bg-green-600 px-4 py-2 rounded">
                ✔ Marcar como Concluído
            </button>
        </div>

    </div>
</div>

<!-- SCRIPT -->
<script>
let aulaIdAtual = null;

function abrirModal(url, aulaId) {

    aulaIdAtual = aulaId;

    // 🔥 AGORA USA DIRETO (SEM CONVERSÃO)
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
        .then(() => {
            location.reload();
        });
}
</script>

@endsection