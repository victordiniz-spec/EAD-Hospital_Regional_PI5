@extends('layout.app')

@section('title', 'Controle de Usuários')

@section('content')

<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    @include('partials.sidebar-professor')

    <!-- CONTEÚDO -->
    <main class="flex-1 p-8 bg-[#0B1120] text-white">

        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold">Controle de Usuários</h2>
                <p class="text-gray-400 text-sm">
                    Administre acessos, perfis e permissões da instituição.
                </p>
            </div>

            <button class="bg-[#1E293B] px-4 py-2 rounded hover:bg-gray-700 transition">
                ⚙️ Filtros Avançados
            </button>
        </div>

        <!-- TABELA -->
        <div class="bg-[#1E293B] rounded-xl p-6 shadow-md">

            <table class="w-full text-sm">

                <thead class="text-gray-400 border-b border-gray-700">
                    <tr class="text-left">
                        <th class="pb-3">Nome</th>
                        <th class="pb-3">E-mail</th>
                        <th class="pb-3">CPF</th>
                        <th class="pb-3">Tipo</th>
                        <th class="pb-3">Status</th>
                        <th class="pb-3 text-right">Ações</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($usuarios as $user)
                    <tr class="border-b border-gray-800 hover:bg-[#0F172A] transition">

                        <!-- NOME -->
                        <td class="py-4 flex items-center gap-3">

                            <div class="w-10 h-10 rounded-full bg-green-600 flex items-center justify-center font-bold">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>

                            <div>
                                <p class="font-semibold">{{ $user->name }}</p>
                            </div>

                        </td>

                        <!-- EMAIL -->
                        <td>{{ $user->email }}</td>

                        <!-- CPF -->
                        <td>{{ $user->cpf }}</td>

                        <!-- TIPO -->
                        <td>
                            <span class="bg-green-500/20 text-green-400 px-3 py-1 rounded text-xs">
                                {{ strtoupper($user->tipo) }}
                            </span>
                        </td>

                        <!-- STATUS -->
                        <td>
                            @if($user->status == 'aprovado')
                                <span class="text-green-400">● ATIVO</span>
                            @else
                                <span class="text-gray-400">● INATIVO</span>
                            @endif
                        </td>

                        <!-- AÇÕES -->
                        <td class="text-right">
                            <div class="flex justify-end gap-3">

                                <!-- EDITAR -->
                                <button onclick="abrirModalEditar({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}', '{{ $user->cpf }}')">
                                    ✏️
                                </button>

                                <!-- MAIS -->
                                <button>
                                    ⋮
                                </button>

                            </div>
                        </td>

                    </tr>
                    @endforeach

                </tbody>

            </table>

        </div>

    </main>

</div>

<!-- MODAL EDITAR -->
<div id="modalEditar" class="fixed inset-0 hidden items-center justify-center bg-black/60 z-50">

    <div class="bg-[#1E293B] w-[500px] p-6 rounded-xl">

        <h2 class="text-xl font-bold mb-4">Editar Usuário</h2>

        <form id="formEditar" method="POST">
            @csrf
            @method('PUT')

            <input id="nomeEdit" name="name" type="text"
                class="w-full mb-3 p-3 rounded bg-[#0F172A] text-white">

            <input id="emailEdit" name="email" type="email"
                class="w-full mb-3 p-3 rounded bg-[#0F172A] text-white">

            <input id="cpfEdit" name="cpf" type="text"
                class="w-full mb-4 p-3 rounded bg-[#0F172A] text-white">

            <div class="flex justify-between">
                <button type="button" onclick="fecharModal()">Cancelar</button>

                <button class="bg-green-600 px-4 py-2 rounded hover:bg-green-700">
                    Salvar
                </button>
            </div>

        </form>

    </div>

</div>

<script>
function abrirModalEditar(id, nome, email, cpf) {

    document.getElementById('modalEditar').classList.remove('hidden');
    document.getElementById('modalEditar').classList.add('flex');

    document.getElementById('nomeEdit').value = nome;
    document.getElementById('emailEdit').value = email;
    document.getElementById('cpfEdit').value = cpf;

    document.getElementById('formEditar').action = "/usuarios/" + id;
}

function fecharModal() {
    document.getElementById('modalEditar').classList.add('hidden');
}
</script>

@endsection