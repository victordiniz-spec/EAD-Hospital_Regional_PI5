<nav class="bg-white border-b border-gray-200 px-6 py-3 flex justify-between items-center shadow-sm">

    <!-- LOGO -->
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center text-white font-bold">
            +
        </div>
        <h1 class="font-bold text-gray-700">Integrar ReSaúde</h1>
    </div>

    <!-- MENU -->
    <div class="hidden md:flex items-center gap-6 text-gray-600 font-medium">

        <a href="{{ route('dashboard.professor') }}" class="hover:text-green-600">Home</a>

        <a href="{{ route('videoaulas') }}" class="hover:text-green-600">Video Aulas</a>

        <a href="{{ route('postestes') }}" class="hover:text-green-600">Provas</a>

        <a href="#" class="hover:text-green-600">Certificados</a>

        <a href="{{ route('alunos') }}" class="hover:text-green-600">Usuários</a>

        <a href="{{ route('avisos') }}" class="hover:text-green-600">Avisos</a>

        <a href="#" class="hover:text-green-600">Relatórios</a>

    </div>

    <!-- PERFIL + SAIR -->
    <div class="flex items-center gap-4">

        <span class="text-gray-600 hidden md:block">
            {{ auth()->user()->name }}
        </span>

        <!-- INICIAIS -->
        <div class="w-10 h-10 rounded-full bg-green-600 flex items-center justify-center text-white font-bold">
            {{ strtoupper(substr(auth()->user()->name, 0, 1) . substr(strstr(auth()->user()->name, ' '), 1, 1)) }}
        </div>

        <!-- SAIR -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="text-red-500 hover:text-red-700 font-semibold">
                Sair
            </button>
        </form>

    </div>

</nav>