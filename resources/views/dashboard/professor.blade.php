@extends('layout.app')

@section('title', 'Dashboard Professor')

@section('content')

    <div class="flex min-h-screen">

        @include('partials.sidebar-professor')

        <!-- CONTEÚDO -->
        <main class="flex-1 p-8 bg-[#0B1120] text-white">

            <h2 class="text-2xl font-bold mb-6">Dashboard</h2>

            @if(session('success'))
                <div class="bg-green-500/20 text-green-400 p-3 mb-4 rounded border border-green-500">
                    {{ session('success') }}
                </div>
            @endif

            <!-- 🔥 GRID PRINCIPAL -->
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

            <!-- SOLICITAÇÕES PENDENTES -->
            @if($usuariosPendentes->count() > 0)
                <div class="bg-yellow-500/10 border border-yellow-500 p-6 rounded-xl mb-8 shadow-lg">

                    <h3 class="text-yellow-400 font-bold text-lg mb-4">
                        ⚠️ Solicitações de acesso pendentes
                    </h3>

                    <div class="space-y-4">

                        @foreach($usuariosPendentes as $index => $user)
                            <div class="bg-[#1E293B] p-5 rounded-xl flex justify-between items-center 
                                {{ $index >= 3 ? 'hidden extra-user' : '' }}">

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

                    <!-- BOTÃO VER MAIS -->
                    @if($usuariosPendentes->count() > 3)
                        <div class="mt-4 text-center">
                            <button onclick="toggleUsuarios()" id="btnVerMais"
                                class="border border-yellow-500 text-yellow-400 px-4 py-2 rounded hover:bg-yellow-500/20 transition">
                                Ver mais
                            </button>
                        </div>
                    @endif

                </div>
            @endif

        </main>

    </div>

<!-- MODAL -->
<div id="modalAviso" class="fixed inset-0 hidden items-center justify-center z-50"
    style="background: rgba(0,0,0,0.45); backdrop-filter: blur(4px);">

    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden"
        style="max-height: 90vh; overflow-y: auto;">

        <!-- Header -->
        <div class="px-8 pt-8 pb-2">
            <h2 class="text-2xl font-bold text-gray-800">Gerenciar Avisos Institucionais</h2>
        </div>

        <div class="px-8 pb-8 pt-4">

            <!-- Seção Novo Comunicado -->
            <div class="mb-6">

                <div class="flex items-center gap-2 mb-5">
                    <div class="w-6 h-6 rounded-full border-2 border-teal-600 flex items-center justify-center">
                        <svg class="w-3 h-3 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <span class="text-base font-semibold text-teal-700">Novo Comunicado</span>
                </div>

                <form method="POST" id="formAviso">
                    @csrf
                    <input type="hidden" name="_method" id="methodAviso" value="POST">

                    <!-- Título + Categoria -->
                    <div class="flex gap-4 mb-4">

                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">
                                Título do Aviso
                            </label>
                            <input
                                id="tituloAviso"
                                type="text"
                                name="titulo"
                                placeholder="Ex: Atualização do Protocolo de Triagem"
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-700 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                            >
                        </div>

                        <div class="w-52">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">
                                Categoria
                            </label>
                            <div class="relative">
                                <select
                                    id="categoriaAviso"
                                    name="categoria"
                                    class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-700 text-sm appearance-none focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition cursor-pointer"
                                >
                                    <option value="urgente">Urgente</option>
                                    <option value="informativo">Informativo</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Mensagem / Descrição -->
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">
                            Mensagem / Descrição
                        </label>
                        <textarea
                            id="mensagemAviso"
                            name="mensagem"
                            rows="4"
                            placeholder="Descreva os detalhes do aviso aqui..."
                            class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-gray-50 text-gray-700 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition resize-none"
                        ></textarea>
                    </div>

                    <!-- Toggle Publicar Agora -->
                    <div class="flex items-center gap-3 mb-6">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="publicar_agora" id="publicarAgora" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer
                                peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-teal-400
                                peer-checked:bg-teal-600
                                after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                after:bg-white after:border after:border-gray-300 after:rounded-full
                                after:h-5 after:w-5 after:transition-all
                                peer-checked:after:translate-x-full peer-checked:after:border-white">
                            </div>
                        </label>
                        <span class="text-sm font-medium text-gray-700">Publicar Agora</span>
                    </div>

                    <!-- Histórico Recente -->
                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-3">Histórico Recente</h3>

                        <div class="border border-gray-200 rounded-xl overflow-hidden">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-gray-50 border-b border-gray-200">
                                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Título</th>
                                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Data</th>
                                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Ações</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($avisosRecentes as $aviso)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-4 py-3 text-gray-800 font-medium">
                                                {{ $aviso->titulo }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                                    @if ($aviso->status === 'publicado') bg-green-100 text-green-700
                                                    @else bg-yellow-100 text-yellow-700
                                                    @endif">
                                                    {{ strtoupper($aviso->status ?? 'PUBLICADO') }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-gray-500 text-xs">
                                                {{ $aviso->created_at->diffForHumans() }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex justify-end gap-2">

                                                    <button
                                                        type="button"
                                                        onclick="editarAviso({{ $aviso->id }}, '{{ addslashes($aviso->titulo) }}', '{{ addslashes($aviso->mensagem) }}', '{{ $aviso->categoria }}')"
                                                        class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500 hover:text-teal-600 transition"
                                                        title="Editar"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                    </button>

                                                    <form method="POST" action="{{ route('avisos.destroy', $aviso->id) }}" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button
                                                            type="submit"
                                                            onclick="return confirm('Deseja excluir este aviso?')"
                                                            class="p-1.5 rounded-lg hover:bg-red-50 text-gray-500 hover:text-red-600 transition"
                                                            title="Excluir"
                                                        >
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </form>

                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex justify-end gap-3">
                        <button
                            type="button"
                            onclick="fecharModalAviso()"
                            class="px-6 py-2.5 rounded-xl border border-gray-200 text-gray-600 text-sm font-medium hover:bg-gray-50 transition"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="px-6 py-2.5 rounded-xl bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold transition shadow-sm"
                        >
                            Salvar e Publicar
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>

<script>
    function abrirModalAviso() {
        const modal = document.getElementById('modalAviso');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function fecharModalAviso() {
        const modal = document.getElementById('modalAviso');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.getElementById('formAviso').reset();
        document.getElementById('methodAviso').value = 'POST';
        document.getElementById('formAviso').action = '';
    }

    function editarAviso(id, titulo, mensagem, categoria) {
        document.getElementById('tituloAviso').value = titulo;
        document.getElementById('mensagemAviso').value = mensagem;
        document.getElementById('categoriaAviso').value = categoria;
        document.getElementById('methodAviso').value = 'PUT';
        document.getElementById('formAviso').action = `/avisos/${id}`;
    }

    // Fechar ao clicar fora do modal
    document.getElementById('modalAviso').addEventListener('click', function (e) {
        if (e.target === this) fecharModalAviso();
    });
</script>

    <script>
        function abrirModalAviso() {
            const modal = document.getElementById('modalAviso');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function fecharModalAviso() {
            const modal = document.getElementById('modalAviso');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            // Reset form
            document.getElementById('formAviso').reset();
            document.getElementById('methodAviso').value = 'POST';
            document.getElementById('formAviso').action = '';
        }

        function editarAviso(id, titulo, mensagem, categoria) {
            document.getElementById('tituloAviso').value = titulo;
            document.getElementById('mensagemAviso').value = mensagem;
            document.getElementById('categoriaAviso').value = categoria;
            document.getElementById('methodAviso').value = 'PUT';
            document.getElementById('formAviso').action = `/avisos/${id}`;
        }

        // Fechar ao clicar fora do modal
        document.getElementById('modalAviso').addEventListener('click', function(e) {
            if (e.target === this) fecharModalAviso();
        });
    </script>
    <script>

    // =========================
    // 🔥 VER MAIS USUÁRIOS
    // =========================
    function toggleUsuarios() {
        let extras = document.querySelectorAll('.extra-user');
        let btn = document.getElementById('btnVerMais');

        if (!extras.length || !btn) return;

        let ocultos = Array.from(extras).some(el => el.classList.contains('hidden'));

        extras.forEach(el => {
            el.classList.toggle('hidden');
        });

        btn.innerText = ocultos ? 'Ver menos' : 'Ver mais';
    }


    // =========================
    // 🔥 MODAL AVISO
    // =========================
    function abrirModalAviso() {
        const modal = document.getElementById('modalAviso');
        if (!modal) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.getElementById('formAviso').reset();
        document.getElementById('formAviso').action = "{{ route('avisos.store') }}";
        document.getElementById('methodAviso').value = "POST";
    }

    function fecharModalAviso() {
        const modal = document.getElementById('modalAviso');
        if (!modal) return;

        modal.classList.add('hidden');
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