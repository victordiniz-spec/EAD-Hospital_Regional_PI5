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

        <!-- TÍTULO + BOTÃO -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Videoaulas</h2>

            <a href="{{ route('aulas.criar') }}"
               class="bg-blue-600 px-4 py-2 rounded hover:bg-blue-700 transition">
                + Adicionar Aula
            </a>
        </div>

        <!-- MENSAGEM DE SUCESSO -->
        @if(session('success'))
            <div class="bg-green-600 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- GRID DE AULAS -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            @forelse($aulas as $aula)
                <div class="bg-[#1E293B] p-4 rounded-xl shadow">

                    <!-- TÍTULO -->
                    <h3 class="text-lg font-bold mb-2">
                        {{ $aula->titulo }}
                    </h3>

                    <!-- DESCRIÇÃO -->
                    <p class="text-sm text-gray-300 mb-4">
                        {{ $aula->descricao }}
                    </p>

                    <!-- BOTÕES -->
                    <div class="flex flex-wrap gap-2">

                        <!-- ✅ ASSISTIR (AGORA FUNCIONANDO) -->
                        <a href="{{ route('aulas.assistir', $aula->id) }}"
                           class="bg-blue-600 px-3 py-1 rounded text-sm hover:bg-blue-700">
                            Assistir
                        </a>

                        <!-- CRIAR PÓS-TESTE -->
                        <a href="{{ route('avaliacoes.criar', $aula->id) }}"
                           class="bg-purple-600 px-3 py-1 rounded text-sm hover:bg-purple-700">
                            Pós-teste
                        </a>

                    </div>

                </div>
            @empty
                <p class="text-gray-400">Nenhuma videoaula cadastrada.</p>
            @endforelse

        </div>

    </main>

</div>

@endsection