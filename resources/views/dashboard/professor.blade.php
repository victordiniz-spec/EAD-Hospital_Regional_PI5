@extends('layout.app')

@section('title', 'Dashboard Professor')

@section('content')

<div class="flex min-h-screen bg-[#0B1120]">

    @include('partials.sidebar-professor')

    <!-- CONTEÚDO -->
    <main class="flex-1 p-4 pt-20 sm:p-6 lg:p-8 lg:pt-8 bg-[#0B1120] text-white overflow-x-hidden">

        <!-- CABEÇALHO -->
        <div class="mb-6">
            <h2 class="text-2xl sm:text-3xl font-bold">Dashboard</h2>
            <p class="text-sm text-gray-400 mt-1">
                Acompanhe usuários, aulas, avisos e solicitações de acesso.
            </p>
        </div>

        <!-- ALERTAS -->
        @if(session('success'))
            <div class="bg-green-500/20 text-green-400 p-3 mb-4 rounded border border-green-500">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-500/20 text-red-400 p-3 mb-4 rounded border border-red-500">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-500/20 text-red-400 p-3 mb-4 rounded border border-red-500">
                <p class="font-semibold mb-2">Corrija os erros abaixo:</p>
                <ul class="list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- GRID PRINCIPAL -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">

            <div class="xl:col-span-2">

                <!-- CARDS -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

                    <div class="bg-[#1E293B] p-5 sm:p-6 rounded-xl shadow-md border-l-4 border-green-500">
                        <p class="text-sm text-gray-400">Total Usuários</p>
                        <h3 class="text-2xl font-bold mt-1">{{ $totalAlunos }}</h3>
                    </div>

                    <div class="bg-[#1E293B] p-5 sm:p-6 rounded-xl shadow-md border-l-4 border-green-500">
                        <p class="text-sm text-gray-400">Aulas Publicadas</p>
                        <h3 class="text-2xl font-bold mt-1">{{ $totalAulas }}</h3>
                    </div>

                    <div class="bg-[#1E293B] p-5 sm:p-6 rounded-xl shadow-md border-l-4 border-green-500">
                        <p class="text-sm text-gray-400">Pós-testes</p>
                        <h3 class="text-2xl font-bold mt-1">{{ $totalProvas }}</h3>
                    </div>

                    <div class="bg-[#1E293B] p-5 sm:p-6 rounded-xl shadow-md border-l-4 border-green-500">
                        <p class="text-sm text-gray-400">Média Geral</p>
                        <h3 class="text-2xl font-bold mt-1">{{ number_format($mediaGeral, 2) }}</h3>
                    </div>

                </div>

                <!-- AULAS -->
                <div class="bg-[#1E293B] p-5 sm:p-6 rounded-xl shadow-md">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                        <h3 class="font-semibold">Videoaulas Recentes</h3>

                        <a href="{{ route('videoaulas') }}"
                           class="text-sm text-green-400 hover:text-green-300 transition">
                            Ver todas
                        </a>
                    </div>

                    <ul class="space-y-3">
                        @forelse($aulasRecentes as $aula)
                            <li class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 bg-[#0F172A] p-3 rounded-lg">
                                <span class="break-words">{{ $aula->titulo }}</span>
                                <span class="text-green-400 text-sm whitespace-nowrap">✔ Publicada</span>
                            </li>
                        @empty
                            <li class="text-gray-400 bg-[#0F172A] p-4 rounded-lg">
                                Nenhuma aula recente encontrada.
                            </li>
                        @endforelse
                    </ul>
                </div>

            </div>

            <!-- AVISOS -->
            <div class="bg-[#1E293B] p-5 sm:p-6 rounded-xl h-fit shadow-md">

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                    <h3 class="font-bold">Avisos Recentes</h3>

                    <button onclick="abrirModalAviso()"
                        class="text-sm border border-dashed border-gray-500 px-3 py-2 rounded hover:bg-gray-700 transition">
                        + Novo
                    </button>
                </div>

                <div class="space-y-4">
                    @forelse($avisosRecentes as $aviso)
                        <div class="bg-[#0F172A] p-4 rounded-lg border-l-4 border-green-500">
                            <p class="font-semibold break-words">{{ $aviso->titulo }}</p>
                            <p class="text-sm text-gray-400 break-words">
                                {{ $aviso->mensagem ?? $aviso->descricao ?? '' }}
                            </p>
                        </div>
                    @empty
                        <p class="text-gray-400 bg-[#0F172A] p-4 rounded-lg">
                            Nenhum aviso encontrado.
                        </p>
                    @endforelse
                </div>

            </div>

        </div>

        <!-- SOLICITAÇÕES PENDENTES -->
        <div class="bg-[#1E293B] border border-slate-700 p-5 sm:p-6 rounded-xl mb-8 shadow-lg">

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-yellow-400 font-bold text-lg">
                        Solicitações de acesso pendentes
                    </h3>
                    <p class="text-sm text-gray-400 mt-1">
                        Aprove ou rejeite os usuários que solicitaram acesso ao sistema.
                    </p>
                </div>

                <div class="bg-yellow-500/10 border border-yellow-500/40 text-yellow-300 px-4 py-2 rounded-lg text-sm w-fit">
                    {{ $usuariosPendentes->count() }} pendente(s)
                </div>
            </div>

            @if($usuariosPendentes->count() > 0)

                <div class="space-y-4">

                    @foreach($usuariosPendentes as $index => $user)
                        <div class="bg-[#0F172A] p-4 sm:p-5 rounded-xl flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 border border-slate-700
                            {{ $index >= 3 ? 'hidden extra-user' : '' }}">

                            <div class="space-y-1 text-sm min-w-0">
                                <p class="break-words"><strong class="text-gray-300">Nome:</strong> {{ $user->name }}</p>
                                <p class="break-words"><strong class="text-gray-300">CPF:</strong> {{ $user->cpf }}</p>
                                <p class="break-words"><strong class="text-gray-300">Email:</strong> {{ $user->email }}</p>
                                <p class="break-words"><strong class="text-gray-300">Tipo:</strong> {{ ucfirst($user->tipo) }}</p>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
                                <form method="POST" action="{{ route('usuario.aprovar', $user->id) }}" class="w-full sm:w-auto">
                                    @csrf
                                    <button class="w-full sm:w-auto bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg transition">
                                        ✅ Aprovar
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('usuario.rejeitar', $user->id) }}" class="w-full sm:w-auto">
                                    @csrf
                                    <button class="w-full sm:w-auto bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg transition">
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

            @else

                <!-- ESTADO VAZIO -->
                <div class="bg-[#0F172A] border border-slate-700 rounded-xl p-6 sm:p-8 text-center">
                    <div class="mx-auto w-14 h-14 rounded-full bg-green-500/10 border border-green-500/30 flex items-center justify-center mb-4">
                        <span class="text-2xl">✅</span>
                    </div>

                    <h4 class="text-lg font-bold text-white mb-2">
                        Nenhum usuário aguardando aprovação
                    </h4>

                    <p class="text-gray-400 text-sm max-w-md mx-auto">
                        No momento, não há nenhum aluno ou usuário solicitando acesso ao sistema.
                        Quando alguém fizer cadastro, a solicitação aparecerá aqui.
                    </p>
                </div>

            @endif

        </div>

    </main>

</div>

<!-- MODAL AVISO -->
<div id="modalAviso" class="fixed inset-0 hidden items-center justify-center z-50"
    style="background: rgba(0,0,0,0.45); backdrop-filter: blur(4px);">

    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden"
        style="max-height: 90vh; overflow-y: auto;">

        <!-- Header -->
        <div class="px-5 sm:px-8 pt-8 pb-2">
            <h2 class="text-2xl font-bold text-gray-800">Gerenciar Avisos Institucionais</h2>
        </div>

        <div class="px-5 sm:px-8 pb-8 pt-4">

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

                <form method="POST" id="formAviso" action="{{ route('avisos.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="methodAviso" value="POST">

                    <!-- Título + Categoria -->
                    <div class="flex flex-col sm:flex-row gap-4 mb-4">

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

                        <div class="w-full sm:w-52">
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

                        <div class="border border-gray-200 rounded-xl overflow-x-auto">
                            <table class="w-full min-w-[560px] text-sm">
                                <thead>
                                    <tr class="bg-gray-50 border-b border-gray-200">
                                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Título</th>
                                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Data</th>
                                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Ações</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse ($avisosRecentes as $aviso)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-4 py-3 text-gray-800 font-medium">
                                                {{ $aviso->titulo }}
                                            </td>

                                            <td class="px-4 py-3">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                                    @if (($aviso->status ?? 'publicado') === 'publicado') bg-green-100 text-green-700
                                                    @else bg-yellow-100 text-yellow-700
                                                    @endif">
                                                    {{ strtoupper($aviso->status ?? 'PUBLICADO') }}
                                                </span>
                                            </td>

                                            <td class="px-4 py-3 text-gray-500 text-xs">
                                                {{ $aviso->created_at ? $aviso->created_at->diffForHumans() : '-' }}
                                            </td>

                                            <td class="px-4 py-3">
                                                <div class="flex justify-end gap-2">

                                                    <button
                                                        type="button"
                                                        onclick='editarAviso(@json($aviso->id), @json($aviso->titulo), @json($aviso->mensagem ?? $aviso->descricao ?? ""), @json($aviso->categoria))'
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
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                                                Nenhum aviso recente encontrado.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex flex-col sm:flex-row justify-end gap-3">
                        <button
                            type="button"
                            onclick="fecharModalAviso()"
                            class="px-6 py-2.5 rounded-xl border border-gray-200 text-gray-600 text-sm font-medium hover:bg-gray-50 transition">
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            class="px-6 py-2.5 rounded-xl bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold transition shadow-sm">
                            Salvar e Publicar
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>

<script>
    // =========================
    // VER MAIS USUÁRIOS
    // =========================
    function toggleUsuarios() {
        const extras = document.querySelectorAll('.extra-user');
        const btn = document.getElementById('btnVerMais');

        if (!extras.length || !btn) return;

        const existeOculto = Array.from(extras).some(el => el.classList.contains('hidden'));

        extras.forEach(el => {
            el.classList.toggle('hidden');
        });

        btn.innerText = existeOculto ? 'Ver menos' : 'Ver mais';
    }

    // =========================
    // MODAL AVISO
    // =========================
    function abrirModalAviso() {
        const modal = document.getElementById('modalAviso');
        const form = document.getElementById('formAviso');
        const method = document.getElementById('methodAviso');

        if (!modal || !form || !method) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        form.reset();
        form.action = "{{ route('avisos.store') }}";
        method.value = "POST";
    }

    function fecharModalAviso() {
        const modal = document.getElementById('modalAviso');
        const form = document.getElementById('formAviso');
        const method = document.getElementById('methodAviso');

        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        if (form) {
            form.reset();
            form.action = "{{ route('avisos.store') }}";
        }

        if (method) {
            method.value = "POST";
        }
    }

    function editarAviso(id, titulo, mensagem, categoria) {
        abrirModalAviso();

        const tituloInput = document.getElementById('tituloAviso');
        const mensagemInput = document.getElementById('mensagemAviso');
        const categoriaInput = document.getElementById('categoriaAviso');
        const form = document.getElementById('formAviso');
        const method = document.getElementById('methodAviso');

        if (tituloInput) tituloInput.value = titulo ?? '';
        if (mensagemInput) mensagemInput.value = mensagem ?? '';
        if (categoriaInput) categoriaInput.value = categoria ?? 'informativo';

        if (form) form.action = "/avisos/" + id;
        if (method) method.value = "PUT";
    }

    // Fechar ao clicar fora do modal
    const modalAviso = document.getElementById('modalAviso');

    if (modalAviso) {
        modalAviso.addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalAviso();
            }
        });
    }
</script>

@endsection