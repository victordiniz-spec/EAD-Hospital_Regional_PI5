@extends('layout.app')

@section('title', 'Videoaulas')

@section('content')

<div class="flex min-h-screen bg-[#0B1120] text-white">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-[#0F172A] p-6">
        <h1 class="text-xl font-bold mb-8">ResidentEAD</h1>

        <nav class="space-y-4">
            <a href="{{ route('dashboard.professor') }}" class="block hover:text-blue-400">Dashboard</a>
            <a href="{{ route('videoaulas') }}" class="block bg-blue-600 p-2 rounded">Videoaulas</a>
            <a href="{{ route('postestes') }}" class="block hover:text-blue-400">Pós-testes</a>
            <a href="{{ route('alunos') }}" class="block hover:text-blue-400">Alunos</a>
        </nav>
    </aside>

    <!-- CONTEÚDO -->
    <main class="flex-1 p-8">

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Videoaulas</h2>

            <a href="{{ route('aulas.criar') }}"
               class="bg-blue-600 px-4 py-2 rounded hover:bg-blue-700">
                + Adicionar Aula
            </a>
        </div>

        <!-- ALERTAS MODERNOS -->
        @if(session('success'))
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: '{{ session('success') }}'
            });
        </script>
        @endif

        @if(session('error'))
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: '{{ session('error') }}'
            });
        </script>
        @endif

        <!-- GRID -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            @forelse($aulas as $aula)
                <div class="bg-[#1E293B] p-4 rounded-xl shadow">

                    <h3 class="text-lg font-bold mb-2">
                        {{ $aula->titulo }}
                    </h3>

                    <p class="text-sm text-gray-300 mb-4">
                        {{ $aula->descricao }}
                    </p>

                    <div class="flex flex-wrap gap-2">

                        <!-- Assistir -->
                        <a href="{{ route('aulas.assistir', $aula->id) }}"
                           class="bg-blue-600 px-3 py-1 rounded text-sm hover:bg-blue-700">
                            Assistir
                        </a>

                        <!-- Pós-teste -->
                        <a href="{{ route('avaliacoes.criar', $aula->id) }}"
                           class="bg-purple-600 px-3 py-1 rounded text-sm hover:bg-purple-700">
                            Pós-teste
                        </a>

                        <!-- 🔥 EXCLUIR -->
                        <form action="{{ route('aulas.destroy', $aula->id) }}" method="POST">
                            @csrf
                            @method('DELETE')

                            <button type="button"
                                onclick="confirmarExclusao(this)"
                                class="bg-red-600 px-3 py-1 rounded text-sm hover:bg-red-700">
                                Excluir
                            </button>
                        </form>

                    </div>

                </div>
            @empty
                <p class="text-gray-400">Nenhuma videoaula cadastrada.</p>
            @endforelse

        </div>

    </main>

</div>

<!-- SWEET ALERT -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function confirmarExclusao(botao) {

    Swal.fire({
        title: 'Tem certeza?',
        text: "Essa aula será excluída!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#2563eb',
        confirmButtonText: 'Sim, excluir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            botao.closest('form').submit();
        }
    });
}
</script>

@endsection