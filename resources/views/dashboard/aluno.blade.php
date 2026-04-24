@extends('layout.app')

@section('title', 'Dashboard Aluno')

@section('content')

<div class="flex h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-white">

    <!-- 🔥 SIDEBAR (ADICIONADA) -->
    @include('partials.sidebar-aluno')

    <!-- MAIN -->
    <div class="flex-1 flex flex-col">

        <!-- TOPBAR -->
        <header class="bg-slate-900 border-b border-slate-800 px-8 h-16 flex items-center justify-between shadow">

            <div>
                <h1 class="text-lg font-bold">Dashboard</h1>
                <p class="text-xs text-slate-500">Continue seu aprendizado</p>
            </div>

        </header>

        <!-- CONTEÚDO -->
        <main class="flex-1 overflow-auto p-8">

            <!-- 🔥 NOVO BLOCO DE MÓDULOS -->
            @if(isset($modulos))
            <div id="modulos" class="mb-10">

                <h2 class="text-xl font-bold mb-4">📚 Módulos</h2>

                @foreach($modulos as $modulo)
                    <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl mb-3">

                        <!-- 🔥 CLIQUE PARA ABRIR -->
                        <h3 class="font-semibold mb-2 cursor-pointer"
                            onclick="toggleModulo({{ $modulo->id }})">
                            ▶ {{ $modulo->nome }}
                        </h3>

                        <!-- 🔥 AULAS (INICIA FECHADO) -->
                        <div id="modulo-{{ $modulo->id }}" class="hidden">

                            @if(isset($modulo->aulas))
                                @foreach($modulo->aulas as $aula)
                                    <div class="flex justify-between bg-slate-800 p-2 rounded mb-2">
                                        <span>{{ $aula->titulo }}</span>

                                        <button onclick="abrirModal('{{ $aula->video_url }}', '{{ $aula->id }}')"
                                            class="bg-emerald-600 px-3 py-1 rounded text-sm">
                                            ▶ Assistir
                                        </button>
                                    </div>
                                @endforeach
                            @endif

                        </div>

                    </div>
                @endforeach

            </div>
            @endif

            <!-- 🔥 AVISOS -->
            @if(isset($avisosRecentes) && $avisosRecentes->count() > 0)
            <div class="mb-8">
                <h2 class="mb-4 font-bold text-lg flex items-center gap-2">
                    📢 Avisos
                </h2>

                <div class="grid gap-4">
                    @foreach($avisosRecentes as $aviso)
                        <div class="bg-slate-900 border border-slate-800 p-5 rounded-xl shadow hover:shadow-lg transition border-l-4 
                            @if($aviso->categoria == 'urgente') border-red-500
                            @elseif($aviso->categoria == 'informativo') border-blue-500
                            @else border-emerald-500
                            @endif">

                            <p class="font-semibold text-base mb-1">
                                {{ $aviso->titulo }}
                            </p>

                            <p class="text-sm text-slate-400">
                                {{ $aviso->mensagem }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif


            <!-- CARDS -->
            <div class="grid grid-cols-4 gap-5 mb-8">

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow hover:scale-[1.02] transition">
                    <p class="text-slate-400 text-sm mb-2">Progresso</p>
                    <h3 class="text-3xl font-bold text-emerald-400">
                        {{ number_format($progresso, 0) }}%
                    </h3>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow hover:scale-[1.02] transition">
                    <p class="text-slate-400 text-sm mb-2">Aulas</p>
                    <h3 class="text-3xl font-bold">
                        {{ $aulasAssistidas }} / {{ $totalAulas }}
                    </h3>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow hover:scale-[1.02] transition">
                    <p class="text-slate-400 text-sm mb-2">Testes</p>
                    <h3 class="text-3xl font-bold text-blue-400">
                        {{ $testesPendentes }}
                    </h3>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow hover:scale-[1.02] transition">
                    <p class="text-slate-400 text-sm mb-2">Média</p>
                    <h3 class="text-3xl font-bold text-yellow-400">
                        {{ number_format($media, 1) }}
                    </h3>
                </div>

            </div>

            <!-- 🎓 BOTÃO CERTIFICADO -->
            <div class="mb-8">
                <a href="{{ route('certificado.gerar', 1) }}"
                   class="bg-green-600 px-6 py-3 rounded-lg shadow hover:bg-green-700 transition">
                    🎓 Baixar Certificado
                </a>
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

function toggleModulo(id) {
    const el = document.getElementById('modulo-' + id);
    el.classList.toggle('hidden');
}
</script>

@endsection