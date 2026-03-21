@extends('layout.app')

@section('title', 'Dashboard Aluno')

@section('content')

<div class="flex h-screen bg-slate-950 text-white">

    <!-- SIDEBAR -->
    <aside class="w-60 bg-slate-900 border-r border-slate-800 flex flex-col justify-between py-6 px-4">

        <div>
            <h1 class="text-lg font-bold mb-8">ResidentEAD</h1>

            <nav class="space-y-2">
                <a href="{{ route('dashboard.aluno') }}"
                   class="block px-4 py-2 rounded-lg text-sm bg-blue-900 text-emerald-400">
                    Dashboard
                </a>

                <a href="#"
                   class="block px-4 py-2 rounded-lg text-sm text-slate-400 hover:bg-slate-800">
                    Minhas Aulas
                </a>

                <a href="#"
                   class="block px-4 py-2 rounded-lg text-sm text-slate-400 hover:bg-slate-800">
                    Pós-testes
                </a>

                <a href="#"
                   class="block px-4 py-2 rounded-lg text-sm text-slate-400 hover:bg-slate-800">
                    Desempenho
                </a>
            </nav>
        </div>

        <!-- PERFIL -->
        <div class="border-t border-slate-800 pt-4">
            @php
                $fotoUsuario = auth()->user()->foto 
                    ? asset('storage/' . auth()->user()->foto) 
                    : asset('images/usuario-padrao.png');
            @endphp

            <div class="flex items-center gap-3">
                <img src="{{ $fotoUsuario }}"
                     class="w-10 h-10 rounded-full object-cover">

                <div>
                    <div class="text-sm font-semibold">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-slate-500">{{ ucfirst(auth()->user()->tipo) }}</div>
                </div>
            </div>
        </div>

    </aside>

    <!-- MAIN -->
    <div class="flex-1 flex flex-col">

        <!-- TOPBAR -->
        <header class="bg-slate-900 border-b border-slate-800 px-8 h-16 flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold">Dashboard</h1>
                <p class="text-xs text-slate-500">Continue seu aprendizado</p>
            </div>
        </header>

        <!-- CONTEÚDO -->
        <main class="flex-1 overflow-auto p-8">

            <!-- CARDS -->
            <div class="grid grid-cols-4 gap-4 mb-8">

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                    <p class="text-slate-400 text-sm mb-2">Progresso</p>
                    <h3 class="text-3xl font-bold">{{ number_format($progresso, 0) }}%</h3>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                    <p class="text-slate-400 text-sm mb-2">Aulas</p>
                    <h3 class="text-3xl font-bold">
                        {{ $aulasAssistidas }} / {{ $totalAulas }}
                    </h3>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                    <p class="text-slate-400 text-sm mb-2">Testes</p>
                    <h3 class="text-3xl font-bold">{{ $testesPendentes }}</h3>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                    <p class="text-slate-400 text-sm mb-2">Média</p>
                    <h3 class="text-3xl font-bold">{{ number_format($media, 1) }}</h3>
                </div>

            </div>

            <!-- GRID -->
            <div class="grid grid-cols-3 gap-6">

                <!-- PRÓXIMAS AULAS -->
                <div class="col-span-2">
                    <h2 class="mb-4 font-bold">Próximas Aulas</h2>

                    <div class="space-y-3">
                        @forelse($proximasAulas as $aula)

                        <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl flex justify-between items-center">

                            <span>{{ $aula->titulo }}</span>

                            <button onclick="abrirModal('{{ $aula->video_url }}', '{{ $aula->id }}')"
                                class="bg-emerald-600 px-4 py-2 rounded-lg text-sm hover:bg-emerald-700">
                                Assistir
                            </button>

                        </div>

                        @empty
                        <p class="text-slate-500">Nenhuma aula pendente</p>
                        @endforelse
                    </div>
                </div>

                <!-- TESTES -->
                <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl">
                    <h2 class="mb-4 font-bold">Testes</h2>

                    @forelse($listaTestes as $teste)

                    <div class="mb-3 flex justify-between items-center text-sm">

                        <span>{{ $teste->titulo }}</span>

                        @if($teste->assistido)
                            <a href="{{ route('avaliacoes.show', $teste->id) }}"
                               class="bg-blue-600 px-3 py-1 rounded hover:bg-blue-700">
                                Fazer
                            </a>
                        @else
                            <button onclick="bloqueado()"
                                class="bg-gray-600 px-3 py-1 rounded">
                                🔒
                            </button>
                        @endif

                    </div>

                    @empty
                    <p class="text-slate-500">Nenhum teste</p>
                    @endforelse

                </div>

            </div>

        </main>

    </div>

</div>

<!-- MODAL -->
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

<!-- SWEETALERT -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

function bloqueado() {
    Swal.fire({
        icon: 'warning',
        title: 'Bloqueado',
        text: 'Assista a aula primeiro!',
        confirmButtonColor: '#2563eb'
    });
}
</script>

@endsection