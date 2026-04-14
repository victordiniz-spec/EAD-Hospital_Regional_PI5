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

                <h3 class="text-yellow-400 font-bold text-lg mb-4 flex items-center gap-2">
                    ⚠️ Solicitações de acesso pendentes
                </h3>

                <div class="space-y-4">

                    @foreach($usuariosPendentes as $user)
                        <div class="bg-[#1E293B] p-5 rounded-xl flex justify-between items-center hover:bg-[#273449] transition">

                            <!-- INFO -->
                            <div class="space-y-1">
                                <p class="text-sm"><span class="text-gray-400">Nome:</span> {{ $user->name }}</p>
                                <p class="text-sm"><span class="text-gray-400">CPF:</span> {{ $user->cpf }}</p>
                                <p class="text-sm"><span class="text-gray-400">Email:</span> {{ $user->email }}</p>
                                <p class="text-sm">
                                    <span class="text-gray-400">Tipo:</span> 
                                    <span class="text-blue-400 font-semibold">
                                        {{ ucfirst($user->tipo) }}
                                    </span>
                                </p>
                            </div>

                            <!-- BOTÕES -->
                            <div class="flex gap-3">

                                <!-- APROVAR -->
                                <form method="POST" action="{{ route('aprovar.usuario', $user->id) }}">
                                    @csrf
                                    <button 
                                        class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg flex items-center gap-2 transition shadow-md"
                                    >
                                        ✅ Aprovar
                                    </button>
                                </form>

                                <!-- REJEITAR -->
                                <form method="POST" action="{{ route('rejeitar.usuario', $user->id) }}">
                                    @csrf
                                    <button 
                                        class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg flex items-center gap-2 transition shadow-md"
                                    >
                                        ❌ Rejeitar
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

    </main>

</div>

@endsection