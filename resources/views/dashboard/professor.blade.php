@extends('layout.app')

@section('title', 'Dashboard Professor')

@section('content')

<div class="flex min-h-screen bg-[#0B1120] text-white">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-[#0F172A] p-6 flex flex-col justify-between h-screen">
        <div>
            <h1 class="text-xl font-bold mb-8">ResidentEAD</h1>

            <nav class="space-y-4">
                <a href="{{ route('dashboard.professor') }}" class="block bg-blue-600 p-2 rounded">Dashboard</a>
                <a href="{{ route('videoaulas') }}" class="block hover:text-blue-400">Videoaulas</a>
                <a href="{{ route('postestes') }}" class="block hover:text-blue-400">Pós-testes</a>
                <a href="{{ route('alunos') }}" class="block hover:text-blue-400">Alunos</a>
            </nav>
        </div>

        <!-- USUÁRIO -->
        <div class="text-center mt-6">
            <div class="w-20 h-20 mx-auto rounded-full bg-gray-700 flex items-center justify-center text-3xl mb-2">
                👤
            </div>
            <h2 class="font-bold">{{ auth()->user()->name }}</h2>
            <span class="text-sm text-gray-400">{{ ucfirst(auth()->user()->tipo) }}</span>
        </div>
    </aside>

    <!-- CONTEÚDO -->
    <main class="flex-1 p-8">

        <h2 class="text-2xl font-bold mb-6">Dashboard</h2>

        <!-- ALERTA -->
        @if(session('success'))
            <div class="bg-green-500/20 text-green-400 p-3 mb-4 rounded border border-green-500">
                {{ session('success') }}
            </div>
        @endif

        <!-- 🔥 USUÁRIOS PENDENTES -->
        @if($usuariosPendentes->count() > 0)
            <div class="bg-yellow-500/10 border border-yellow-500 p-6 rounded-xl mb-8 shadow-lg">

                <h3 class="text-yellow-400 font-bold text-lg mb-4">
                    ⚠️ Solicitações de acesso pendentes
                </h3>

                <div class="space-y-4">
                    @foreach($usuariosPendentes as $user)
                        <div class="bg-[#1E293B] p-5 rounded-xl flex justify-between items-center">

                            <div>
                                <p><strong>Nome:</strong> {{ $user->name }}</p>
                                <p><strong>CPF:</strong> {{ $user->cpf }}</p>
                                <p><strong>Email:</strong> {{ $user->email }}</p>
                                <p><strong>Tipo:</strong> {{ ucfirst($user->tipo) }}</p>
                            </div>

                            <div class="flex gap-3">
                                <form method="POST" action="{{ route('usuario.aprovar', $user->id) }}">
                                    @csrf
                                    <button class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg transition">
                                        ✅ Aprovar
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('usuario.rejeitar', $user->id) }}">
                                    @csrf
                                    <button class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg transition">
                                        ❌ Rejeitar
                                    </button>
                                </form>
                            </div>

                        </div>
                    @endforeach
                </div>

            </div>
        @endif

        <!-- GRID PRINCIPAL -->
        <div class="grid grid-cols-3 gap-6 mb-8">

            <!-- ESQUERDA -->
            <div class="col-span-2">

                <!-- CARDS -->
                <div class="grid grid-cols-4 gap-4 mb-6">

                    <div class="bg-[#1E293B] p-6 rounded-xl shadow-md">
                        <p class="text-sm text-gray-400">Total Usuários</p>
                        <h3 class="text-2xl font-bold">{{ $totalAlunos }}</h3>
                    </div>

                    <div class="bg-[#1E293B] p-6 rounded-xl shadow-md">
                        <p class="text-sm text-gray-400">Aulas Publicadas</p>
                        <h3 class="text-2xl font-bold">{{ $totalAulas }}</h3>
                    </div>

                    <div class="bg-[#1E293B] p-6 rounded-xl shadow-md">
                        <p class="text-sm text-gray-400">Pós-testes</p>
                        <h3 class="text-2xl font-bold">{{ $totalProvas }}</h3>
                    </div>

                    <div class="bg-[#1E293B] p-6 rounded-xl shadow-md">
                        <p class="text-sm text-gray-400">Média Geral</p>
                        <h3 class="text-2xl font-bold">{{ number_format($mediaGeral, 2) }}</h3>
                    </div>

                </div>

                <!-- AULAS -->
                <div class="bg-[#1E293B] p-6 rounded-xl shadow-md">
                    <h3 class="mb-4 font-semibold">Videoaulas Recentes</h3>

                    <ul class="space-y-3">
                        @forelse($aulasRecentes as $aula)
                            <li class="flex justify-between items-center">
                                <span>{{ $aula->titulo }}</span>
                                <span class="text-green-400 text-sm">✔ Publicada</span>
                            </li>
                        @empty
                            <li class="text-gray-400">Nenhuma aula recente encontrada.</li>
                        @endforelse
                    </ul>
                </div>

            </div>

            <!-- DIREITA - AVISOS -->
            <div class="bg-[#1E293B] p-6 rounded-xl h-fit shadow-md">

                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold">Avisos Recentes</h3>
                    <button class="text-sm text-blue-400 hover:underline">
                        Ver todos
                    </button>
                </div>

                <div class="space-y-4">

                    @forelse($avisosRecentes as $aviso)
                        <div class="bg-[#0F172A] p-4 rounded-lg">

                            <span class="text-xs px-2 py-1 rounded
                                @if($aviso->categoria == 'urgente') bg-red-500
                                @elseif($aviso->categoria == 'informativo') bg-blue-500
                                @else bg-gray-500
                                @endif">
                                {{ strtoupper($aviso->categoria) }}
                            </span>

                            <p class="font-semibold mt-2">{{ $aviso->titulo }}</p>
                            <p class="text-sm text-gray-400">{{ $aviso->mensagem }}</p>

                        </div>
                    @empty
                        <p class="text-gray-400">Nenhum aviso encontrado</p>
                    @endforelse

                </div>

                <!-- BOTÃO -->
                <div class="mt-4 text-center">
                    <button onclick="abrirModalAviso()"
                        class="border border-dashed border-gray-500 px-4 py-2 rounded hover:bg-gray-700 transition">
                        + Criar Novo Aviso
                    </button>
                </div>

            </div>

        </div>

    </main>

</div>

<!-- 🔥 MODAL GLASSMORPHISM -->
<div id="modalAviso"
    class="fixed inset-0 hidden items-center justify-center backdrop-blur-md bg-black/40 z-50">

    <div class="bg-white/10 backdrop-blur-xl border border-white/20 
                w-[700px] rounded-2xl p-6 text-white shadow-2xl">

        <h2 class="text-xl font-bold mb-4">Criar Novo Aviso</h2>

        <form method="POST" action="{{ route('avisos.store') }}">
            @csrf

            <input type="text" name="titulo"
                placeholder="Título do aviso"
                class="w-full mb-3 p-3 rounded-lg bg-white/20 border border-white/30 placeholder-gray-300">

            <select name="categoria"
                class="w-full mb-3 p-3 rounded-lg bg-white/20 border border-white/30">
                <option value="urgente">Urgente</option>
                <option value="informativo">Informativo</option>
            </select>

            <textarea name="mensagem"
                placeholder="Digite o conteúdo..."
                class="w-full mb-4 p-3 rounded-lg bg-white/20 border border-white/30"></textarea>

            <!-- BOTÕES -->
            <div class="flex justify-between items-center">

                <button type="button" onclick="fecharModalAviso()"
                    class="text-gray-300 hover:text-white">
                    Cancelar
                </button>

                <div class="flex gap-3">

                    <button type="button"
                        class="bg-gray-500 hover:bg-gray-600 px-4 py-2 rounded-lg transition">
                        Deixar rascunho
                    </button>

                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg transition">
                        Publicar agora
                    </button>

                </div>
            </div>

        </form>

    </div>

</div>

<!-- SCRIPT -->
<script>
function abrirModalAviso() {
    document.getElementById('modalAviso').classList.remove('hidden');
    document.getElementById('modalAviso').classList.add('flex');
}

function fecharModalAviso() {
    document.getElementById('modalAviso').classList.add('hidden');
}
</script>

@endsection