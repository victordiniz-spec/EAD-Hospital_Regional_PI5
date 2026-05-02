@extends('layout.app')

@section('title', 'Controle de Usuários')

@section('content')

<style>
    html, body {
        background: #0B1120 !important;
        margin: 0;
        padding: 0;
        width: 100%;
        min-height: 100%;
    }

    #app {
        background: #0B1120 !important;
        min-height: 100vh;
        width: 100%;
    }
</style>

<div class="flex min-h-screen w-full bg-[#0B1120] text-white overflow-x-hidden">

    <!-- SIDEBAR -->
    @include('partials.sidebar-professor')

    <!-- CONTEÚDO -->
    <main class="flex-1 min-w-0 w-full p-4 pt-20 sm:p-6 lg:p-8 lg:pt-8 bg-[#0B1120] overflow-x-hidden">

        <!-- CABEÇALHO -->
        <div class="w-full mb-6">

            <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">

                <div>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold">
                        Controle de Usuários
                    </h2>

                    <p class="text-gray-400 text-sm mt-1">
                        Administre acessos, perfis e permissões da instituição.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 w-full xl:w-auto">

                    <!-- PESQUISA -->
                    <div class="relative w-full xl:w-[420px]">
                        <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-5 h-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0z"/>
                            </svg>
                        </span>

                        <input
                            type="text"
                            id="pesquisaUsuarios"
                            onkeyup="filtrarUsuarios()"
                            placeholder="Pesquisar aluno, CPF, e-mail..."
                            class="w-full bg-[#1E293B] border border-slate-700 text-white placeholder-gray-500 rounded-2xl pl-10 pr-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        >
                    </div>

                    <!-- BOTÃO LIMPAR -->
                    <button type="button"
                        onclick="limparPesquisa()"
                        class="bg-[#1E293B] border border-slate-700 px-5 py-3 rounded-2xl hover:bg-slate-700 transition flex items-center justify-center gap-2 text-sm">

                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="w-5 h-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M16.023 9.348h4.992M2.985 19.644v-4.992m0 0h4.992m-4.992 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M7.977 14.652H2.985m18.03-9.296v4.992m0 0h-4.992m4.992 0-3.181-3.183a8.25 8.25 0 0 0-13.803 3.7"/>
                        </svg>

                        Limpar
                    </button>

                </div>

            </div>

        </div>

        <!-- RESUMO -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">

            <div class="bg-[#1E293B] border border-slate-700 rounded-2xl p-5 shadow-lg">
                <p class="text-sm text-gray-400">Total de usuários</p>
                <h3 class="text-3xl font-bold mt-2">{{ $usuarios->count() }}</h3>
            </div>

            <div class="bg-[#1E293B] border border-slate-700 rounded-2xl p-5 shadow-lg">
                <p class="text-sm text-gray-400">Ativos</p>
                <h3 class="text-3xl font-bold mt-2 text-green-400">
                    {{ $usuarios->where('status', 'aprovado')->count() }}
                </h3>
            </div>

            <div class="bg-[#1E293B] border border-slate-700 rounded-2xl p-5 shadow-lg sm:col-span-2 xl:col-span-1">
                <p class="text-sm text-gray-400">Inativos / Pendentes</p>
                <h3 class="text-3xl font-bold mt-2 text-yellow-400">
                    {{ $usuarios->where('status', '!=', 'aprovado')->count() }}
                </h3>
            </div>

        </div>

        <!-- SEM RESULTADOS -->
        <div id="semResultados"
            class="hidden bg-[#1E293B] border border-slate-700 rounded-2xl p-8 text-center text-gray-400 mb-6">

            <div class="w-14 h-14 mx-auto rounded-full bg-slate-800 flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-7 h-7 text-gray-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.8"
                        d="M15.75 15.75 21 21m-5.25-5.25a7.5 7.5 0 1 0-10.607 0 7.5 7.5 0 0 0 10.607 0z"/>
                </svg>
            </div>

            <p class="font-semibold text-white">Nenhum usuário encontrado</p>
            <p class="text-sm mt-1">Tente pesquisar por outro nome, CPF ou e-mail.</p>
        </div>

        <!-- CARDS MOBILE / TABLET -->
        <div class="xl:hidden space-y-4" id="listaUsuariosMobile">

            @foreach($usuarios as $user)
                <div class="usuario-item bg-[#1E293B] border border-slate-700 rounded-2xl p-5 shadow-lg"
                    data-search="{{ strtolower($user->name . ' ' . $user->email . ' ' . $user->cpf . ' ' . $user->tipo . ' ' . $user->status) }}">

                    <div class="flex items-start gap-4">

                        <div class="w-12 h-12 rounded-full bg-green-600 flex items-center justify-center font-bold shrink-0">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-white break-words">{{ $user->name }}</p>
                            <p class="text-sm text-gray-400 break-words">{{ $user->email }}</p>
                        </div>

                    </div>

                    <div class="mt-4 space-y-2 text-sm">

                        <div class="bg-[#0F172A] rounded-xl px-3 py-2">
                            <span class="text-gray-500">CPF:</span>
                            <span class="text-gray-200">{{ $user->cpf }}</span>
                        </div>

                        <div class="flex flex-wrap gap-2 mt-2">

                            <span class="inline-flex items-center gap-1 bg-green-500/20 text-green-400 px-3 py-1 rounded-full text-xs border border-green-500/30">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="w-4 h-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0zM4.5 20.25a8.25 8.25 0 0 1 15 0"/>
                                </svg>

                                {{ strtoupper($user->tipo) }}
                            </span>

                            @if($user->status == 'aprovado')
                                <span class="inline-flex items-center gap-1 bg-emerald-500/20 text-emerald-400 px-3 py-1 rounded-full text-xs border border-emerald-500/30">
                                    ● ATIVO
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 bg-yellow-500/20 text-yellow-400 px-3 py-1 rounded-full text-xs border border-yellow-500/30">
                                    ● INATIVO
                                </span>
                            @endif

                        </div>

                    </div>

                    <div class="mt-4">
                        <button
                            onclick='abrirModalEditar(@json($user->id), @json($user->name), @json($user->email), @json($user->cpf))'
                            class="w-full bg-blue-600 hover:bg-blue-700 px-4 py-3 rounded-xl transition text-sm font-semibold flex items-center justify-center gap-2">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-4 h-4"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931z"/>
                            </svg>

                            Editar usuário
                        </button>
                    </div>

                </div>
            @endforeach

        </div>

        <!-- TABELA DESKTOP -->
        <div class="hidden xl:block bg-[#1E293B] rounded-2xl p-6 shadow-lg border border-slate-700 overflow-hidden w-full">

            <div class="overflow-x-auto w-full">
                <table class="w-full text-sm">

                    <thead class="text-gray-400 border-b border-gray-700">
                        <tr class="text-left">
                            <th class="pb-4 px-3">Nome</th>
                            <th class="pb-4 px-3">E-mail</th>
                            <th class="pb-4 px-3">CPF</th>
                            <th class="pb-4 px-3">Tipo</th>
                            <th class="pb-4 px-3">Status</th>
                            <th class="pb-4 px-3 text-right">Ações</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($usuarios as $user)
                            <tr class="usuario-item border-b border-gray-800 hover:bg-[#0F172A] transition"
                                data-search="{{ strtolower($user->name . ' ' . $user->email . ' ' . $user->cpf . ' ' . $user->tipo . ' ' . $user->status) }}">

                                <td class="py-4 px-3">
                                    <div class="flex items-center gap-3">

                                        <div class="w-10 h-10 rounded-full bg-green-600 flex items-center justify-center font-bold shrink-0">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>

                                        <div class="min-w-0">
                                            <p class="font-semibold break-words">{{ $user->name }}</p>
                                        </div>

                                    </div>
                                </td>

                                <td class="text-gray-300 break-words px-3">
                                    {{ $user->email }}
                                </td>

                                <td class="text-gray-300 whitespace-nowrap px-3">
                                    {{ $user->cpf }}
                                </td>

                                <td class="px-3">
                                    <span class="inline-flex items-center gap-1 bg-green-500/20 text-green-400 px-3 py-1 rounded-full text-xs border border-green-500/30 whitespace-nowrap">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="w-4 h-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.8"
                                                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0zM4.5 20.25a8.25 8.25 0 0 1 15 0"/>
                                        </svg>

                                        {{ strtoupper($user->tipo) }}
                                    </span>
                                </td>

                                <td class="px-3">
                                    @if($user->status == 'aprovado')
                                        <span class="inline-flex items-center gap-2 text-green-400 whitespace-nowrap">
                                            <span class="w-2 h-2 rounded-full bg-green-400"></span>
                                            ATIVO
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-2 text-yellow-400 whitespace-nowrap">
                                            <span class="w-2 h-2 rounded-full bg-yellow-400"></span>
                                            INATIVO
                                        </span>
                                    @endif
                                </td>

                                <td class="text-right px-3">
                                    <button
                                        onclick='abrirModalEditar(@json($user->id), @json($user->name), @json($user->email), @json($user->cpf))'
                                        class="bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 border border-blue-500/30 p-2 rounded-lg transition"
                                        title="Editar usuário">

                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="w-5 h-5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.8"
                                                d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931z"/>
                                        </svg>
                                    </button>
                                </td>

                            </tr>
                        @endforeach

                    </tbody>

                </table>
            </div>

        </div>

    </main>

</div>

<!-- MODAL EDITAR -->
<div id="modalEditar" class="fixed inset-0 hidden items-center justify-center bg-black/60 backdrop-blur-sm z-[60]">

    <div class="bg-[#1E293B] w-full max-w-lg mx-4 p-6 rounded-2xl border border-slate-700 shadow-2xl">

        <div class="flex items-start justify-between mb-5">

            <div>
                <h2 class="text-xl font-bold">Editar Usuário</h2>
                <p class="text-sm text-gray-400 mt-1">
                    Atualize os dados principais do usuário.
                </p>
            </div>

            <button type="button"
                onclick="fecharModal()"
                class="bg-slate-800 hover:bg-slate-700 p-2 rounded-lg transition">

                <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-5 h-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.8"
                        d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>

        </div>

        <form id="formEditar" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-4">

                <div>
                    <label class="block text-xs uppercase tracking-widest text-gray-400 mb-2">
                        Nome
                    </label>

                    <input id="nomeEdit" name="name" type="text"
                        class="w-full p-3 rounded-xl bg-[#0F172A] border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>

                <div>
                    <label class="block text-xs uppercase tracking-widest text-gray-400 mb-2">
                        E-mail
                    </label>

                    <input id="emailEdit" name="email" type="email"
                        class="w-full p-3 rounded-xl bg-[#0F172A] border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>

                <div>
                    <label class="block text-xs uppercase tracking-widest text-gray-400 mb-2">
                        CPF
                    </label>

                    <input id="cpfEdit" name="cpf" type="text"
                        class="w-full p-3 rounded-xl bg-[#0F172A] border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>

            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-3 mt-6">
                <button type="button"
                    onclick="fecharModal()"
                    class="px-5 py-3 rounded-xl bg-slate-700 text-white font-semibold hover:bg-slate-600 transition">
                    Cancelar
                </button>

                <button class="px-5 py-3 rounded-xl bg-green-600 text-white font-semibold hover:bg-green-700 transition">
                    Salvar Alterações
                </button>
            </div>

        </form>

    </div>

</div>

<script>
function abrirModalEditar(id, nome, email, cpf) {
    const modal = document.getElementById('modalEditar');

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    document.getElementById('nomeEdit').value = nome ?? '';
    document.getElementById('emailEdit').value = email ?? '';
    document.getElementById('cpfEdit').value = cpf ?? '';

    document.getElementById('formEditar').action = "/usuarios/" + id;
}

function fecharModal() {
    const modal = document.getElementById('modalEditar');

    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function filtrarUsuarios() {
    const termo = document.getElementById('pesquisaUsuarios').value.toLowerCase().trim();
    const itens = document.querySelectorAll('.usuario-item');
    const semResultados = document.getElementById('semResultados');

    let encontrados = 0;

    itens.forEach((item) => {
        const texto = item.dataset.search || '';

        if (texto.includes(termo)) {
            item.classList.remove('hidden');
            encontrados++;
        } else {
            item.classList.add('hidden');
        }
    });

    if (encontrados === 0) {
        semResultados.classList.remove('hidden');
    } else {
        semResultados.classList.add('hidden');
    }
}

function limparPesquisa() {
    const input = document.getElementById('pesquisaUsuarios');

    input.value = '';
    filtrarUsuarios();
    input.focus();
}

const modalEditar = document.getElementById('modalEditar');

if (modalEditar) {
    modalEditar.addEventListener('click', function(e) {
        if (e.target === this) {
            fecharModal();
        }
    });
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        fecharModal();
    }
});
</script>

@endsection