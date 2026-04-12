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

        <!-- ALERTA DE SUCESSO -->
        @if(session('success'))
            <div class="bg-green-500/20 text-green-400 p-3 mb-4 rounded border border-green-500">
                {{ session('success') }}
            </div>
        @endif

        <!-- 🔥 USUÁRIOS PENDENTES -->
        @if($usuariosPendentes->count() > 0)
            <div class="bg-yellow-500/10 border border-yellow-500 p-4 rounded-xl mb-6">

                <h3 class="text-yellow-400 font-bold mb-4">
                    ⚠️ Solicitações de acesso pendentes
                </h3>

                <div class="space-y-4">

                    @foreach($usuariosPendentes as $user)
                        <div class="bg-[#1E293B] p-4 rounded-lg flex justify-between items-center">

                            <div>
                                <p><strong>Nome:</strong> {{ $user->name }}</p>
                                <p><strong>CPF:</strong> {{ $user->cpf }}</p>
                                <p><strong>Email:</strong> {{ $user->email }}</p>
                                <p><strong>Tipo:</strong> {{ ucfirst($user->tipo) }}</p>
                            </div>

                            <div class="flex gap-2">

                                <!-- APROVAR -->
                                <form method="POST" action="{{ route('aprovar.usuario', $user->id) }}">
                                    @csrf
                                    <button class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded">
                                        Aprovar
                                    </button>
                                </form>

                                <!-- REJEITAR -->
                                <form method="POST" action="{{ route('rejeitar.usuario', $user->id) }}">
                                    @csrf
                                    <button class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded">
                                        Rejeitar
                                    </button>
                                </form>

                            </div>

                        </div>
                    @endforeach

                </div>

            </div>
        @endif

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