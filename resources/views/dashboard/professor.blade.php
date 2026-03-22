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

        <div class="text-center mt-6">
            <img 
                src="{{ auth()->user()->foto ? asset('storage/' . auth()->user()->foto) : asset('images/usuario-padrao.png') }}" 
                class="w-20 h-20 mx-auto rounded-full object-cover mb-2"
            >
            <h2 class="font-bold">{{ auth()->user()->name }}</h2>
            <span class="text-sm text-gray-400">{{ ucfirst(auth()->user()->tipo) }}</span>
        </div>
    </aside>

    <!-- CONTEÚDO -->
    <main class="flex-1 p-8">

        <h2 class="text-2xl font-bold mb-6">Dashboard</h2>

        <!-- CARDS -->
        <div class="grid grid-cols-4 gap-6 mb-8">
            <div class="bg-[#1E293B] p-6 rounded-xl">
                <p>Total de Aulas</p>
                <h3 class="text-3xl font-bold">{{ $totalAulas }}</h3>
            </div>

            <div class="bg-[#1E293B] p-6 rounded-xl">
                <p>Alunos Ativos</p>
                <h3 class="text-3xl font-bold">{{ $totalAlunos }}</h3>
            </div>

            <div class="bg-[#1E293B] p-6 rounded-xl">
                <p>Pós-testes</p>
                <h3 class="text-3xl font-bold">{{ $totalProvas }}</h3>
            </div>

            <div class="bg-[#1E293B] p-6 rounded-xl">
                <p>Média Geral</p>
                <h3 class="text-3xl font-bold">{{ number_format($mediaGeral, 2) }}</h3>
            </div>
        </div>

        <!-- LISTA DE AULAS -->
        <div class="bg-[#1E293B] p-6 rounded-xl">
            <h3 class="mb-4">Videoaulas Recentes</h3>

            <ul class="space-y-3">
                @forelse($aulasRecentes as $aula)
                    <li class="flex justify-between">
                        <span>{{ $aula->titulo }}</span>
                        <span class="text-green-400">Publicada</span>
                    </li>
                @empty
                    <li>Nenhuma aula recente encontrada.</li>
                @endforelse
            </ul>
        </div>

    </main>

</div>

@endsection