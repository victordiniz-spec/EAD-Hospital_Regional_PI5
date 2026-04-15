@extends('layout.app')

@section('title', 'Dashboard Professor')

@section('content')

<div class="flex min-h-screen bg-[#0B1120] text-white">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-[#0F172A] p-6 flex flex-col justify-between h-screen">

        <div>

            <!-- LOGO -->
            <div class="flex items-center gap-3 mb-8">
                <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center text-white font-bold">
                    +
                </div>
                <h1 class="text-lg font-bold">ResidentEAD</h1>
            </div>

            <!-- MENU -->
            <nav class="space-y-4 text-gray-300">

                <a href="{{ route('dashboard.professor') }}"
                class="flex items-center gap-3 p-2 rounded bg-green-600 text-white">

                    🏠 <span>Home</span>
                </a>

                <a href="{{ route('videoaulas') }}"
                class="flex items-center gap-3 p-2 rounded hover:bg-[#1E293B]">

                    🎥 <span>Video Aulas</span>
                </a>

                <a href="{{ route('postestes') }}"
                class="flex items-center gap-3 p-2 rounded hover:bg-[#1E293B]">

                    📝 <span>Provas</span>
                </a>

                <a href="#"
                class="flex items-center gap-3 p-2 rounded hover:bg-[#1E293B]">

                    🎓 <span>Certificados</span>
                </a>

                <a href="{{ route('alunos') }}"
                class="flex items-center gap-3 p-2 rounded hover:bg-[#1E293B]">

                    👥 <span>Usuários</span>
                </a>

                <a href="{{ route('avisos') }}"
                class="flex items-center gap-3 p-2 rounded hover:bg-[#1E293B]">

                    🔔 <span>Avisos</span>
                </a>

                <a href="#"
                class="flex items-center gap-3 p-2 rounded hover:bg-[#1E293B]">

                    📊 <span>Relatórios</span>
                </a>

            </nav>

        </div>

        <!-- USUÁRIO -->
        <div class="text-center mt-6">

            <!-- INICIAIS -->
            <div class="w-16 h-16 mx-auto rounded-full bg-green-600 flex items-center justify-center text-xl font-bold mb-2">
                {{ strtoupper(substr(auth()->user()->name, 0, 1) . substr(strstr(auth()->user()->name, ' '), 1, 1)) }}
            </div>

            <h2 class="font-bold">{{ auth()->user()->name }}</h2>
            <span class="text-sm text-gray-400">
                {{ ucfirst(auth()->user()->tipo) }}
            </span>

        </div>

</aside>

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

        @if(session('success'))
            <div class="bg-green-500/20 text-green-400 p-3 mb-4 rounded border border-green-500">
                {{ session('success') }}
            </div>
        @endif

        <!-- 🔥 GRID PRINCIPAL (AGORA VEM PRIMEIRO) -->
        <div class="grid grid-cols-3 gap-6 mb-8">

            <div class="col-span-2">

                <!-- CARDS -->
                <div class="grid grid-cols-4 gap-4 mb-6">

                    <div class="bg-[#1E293B] p-6 rounded-xl shadow-md border-l-4 border-green-500">
                        <p class="text-sm text-gray-400">Total Usuários</p>
                        <h3 class="text-2xl font-bold">{{ $totalAlunos }}</h3>
                    </div>

                    <div class="bg-[#1E293B] p-6 rounded-xl shadow-md border-l-4 border-green-500">
                        <p class="text-sm text-gray-400">Aulas Publicadas</p>
                        <h3 class="text-2xl font-bold">{{ $totalAulas }}</h3>
                    </div>

                    <div class="bg-[#1E293B] p-6 rounded-xl shadow-md border-l-4 border-green-500">
                        <p class="text-sm text-gray-400">Pós-testes</p>
                        <h3 class="text-2xl font-bold">{{ $totalProvas }}</h3>
                    </div>

                    <div class="bg-[#1E293B] p-6 rounded-xl shadow-md border-l-4 border-green-500">
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

            <!-- AVISOS -->
            <div class="bg-[#1E293B] p-6 rounded-xl h-fit shadow-md">

                <h3 class="font-bold mb-4">Avisos Recentes</h3>

                <div class="space-y-4">
                    @forelse($avisosRecentes as $aviso)
                        <div class="bg-[#0F172A] p-4 rounded-lg border-l-4 border-green-500">
                            <p class="font-semibold">{{ $aviso->titulo }}</p>
                            <p class="text-sm text-gray-400">{{ $aviso->mensagem }}</p>
                        </div>
                    @empty
                        <p class="text-gray-400">Nenhum aviso encontrado</p>
                    @endforelse
                </div>

                <div class="mt-4 text-center">
                    <button onclick="abrirModalAviso()"
                        class="border border-dashed border-gray-500 px-4 py-2 rounded hover:bg-gray-700 transition">
                        + Criar Novo Aviso
                    </button>
                </div>

            </div>

        </div>

        <!-- 🔥 AGORA FICA EMBAIXO (SÓ MOVI, NÃO REMOVI NADA) -->
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

    </main>

</div>

<!-- MODAL (NÃO MEXI) -->
<div id="modalAviso" class="fixed inset-0 hidden items-center justify-center backdrop-blur-md bg-black/40 z-50">

    <div class="bg-white/10 backdrop-blur-xl border border-white/20 w-[900px] rounded-2xl p-6 text-white">

        <h2 class="text-xl font-bold mb-4">Gerenciar Avisos</h2>

        <!-- FORM -->
        <form method="POST" id="formAviso">
            @csrf
            <input type="hidden" name="_method" id="methodAviso" value="POST">

            <input id="tituloAviso" type="text" name="titulo"
                class="w-full mb-3 p-3 rounded-lg bg-white/20 border border-white/30">

            <select id="categoriaAviso" name="categoria"
                class="w-full mb-3 p-3 rounded-lg bg-white/20 border border-white/30">
                <option value="urgente">Urgente</option>
                <option value="informativo">Informativo</option>
            </select>

            <textarea id="mensagemAviso" name="mensagem"
                class="w-full mb-4 p-3 rounded-lg bg-white/20 border border-white/30"></textarea>

            <div class="flex justify-between mb-6">
                <button type="button" onclick="fecharModalAviso()">Cancelar</button>

                <button type="submit" class="bg-blue-600 px-4 py-2 rounded-lg">
                    Salvar
                </button>
            </div>
        </form>

        <!-- HISTÓRICO -->
        <div class="space-y-3 border-t pt-4">

            @foreach($avisosRecentes as $aviso)
                <div class="flex justify-between items-center bg-[#0F172A] p-3 rounded-lg">

                    <div>
                        <p>{{ $aviso->titulo }}</p>
                        <p class="text-xs text-gray-400">
                            {{ $aviso->created_at }}
                        </p>
                    </div>

                    <div class="flex gap-3">

                        <button onclick="editarAviso(
                            {{ $aviso->id }},
                            '{{ $aviso->titulo }}',
                            '{{ $aviso->mensagem }}',
                            '{{ $aviso->categoria }}'
                        )">✏️</button>

                        <form method="POST" action="{{ route('avisos.destroy', $aviso->id) }}">
                            @csrf
                            @method('DELETE')
                            <button>🗑️</button>
                        </form>

                    </div>

                </div>
            @endforeach

        </div>

    </div>

</div>

<script>
function abrirModalAviso() {
    document.getElementById('modalAviso').classList.remove('hidden');
    document.getElementById('modalAviso').classList.add('flex');

    document.getElementById('formAviso').reset();
    document.getElementById('formAviso').action = "{{ route('avisos.store') }}";
    document.getElementById('methodAviso').value = "POST";
}

function fecharModalAviso() {
    document.getElementById('modalAviso').classList.add('hidden');
}

function editarAviso(id, titulo, mensagem, categoria) {

    abrirModalAviso();

    document.getElementById('tituloAviso').value = titulo;
    document.getElementById('mensagemAviso').value = mensagem;
    document.getElementById('categoriaAviso').value = categoria;

    document.getElementById('formAviso').action = "/avisos/" + id;
    document.getElementById('methodAviso').value = "PUT";
}
</script>

@endsection