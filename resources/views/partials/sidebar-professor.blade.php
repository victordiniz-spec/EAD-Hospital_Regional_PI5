<!-- SIDEBAR -->
<aside class="w-64 bg-gray-100 text-gray-700 min-h-screen p-6 flex flex-col justify-between">

    <div>

        <!-- LOGO -->
        <div class="flex flex-col items-center mb-10">
            <img src="{{ asset('images/logo.png') }}" 
                alt="Logo"
                class="w-14 h-14 object-contain mb-2">

            <h1 class="text-md font-bold text-gray-800">Integrar ReSaúde</h1>
        </div>

        <!-- MENU -->
        <nav class="space-y-2 text-sm font-medium">

            <!-- HOME -->
            <a href="{{ route('dashboard.professor') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition
            {{ request()->routeIs('dashboard.professor') ? 'bg-green-600 text-white shadow' : 'hover:bg-gray-200' }}">

                <!-- ICON HOME -->
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/>
                </svg>

                <span>Home</span>
            </a>

            <!-- VIDEOAULAS -->
            <a href="{{ route('videoaulas') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition
            {{ request()->routeIs('videoaulas') || request()->routeIs('aulas.*') ? 'bg-green-600 text-white shadow' : 'hover:bg-gray-200' }}">

                <!-- ICON PLAY -->
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M5.25 5.653c0-1.427 1.54-2.33 2.79-1.637l9.54 5.347c1.26.707 1.26 2.567 0 3.274l-9.54 5.347c-1.25.693-2.79-.21-2.79-1.637V5.653z"/>
                </svg>

                <span>VideoAulas</span>
            </a>

            <!-- PROVAS -->
            <!-- PROVAS -->
            <a href="{{ route('prova.final.criar') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition
            {{ request()->routeIs('prova.final.criar') ? 'bg-green-600 text-white shadow' : 'hover:bg-gray-200' }}">
                
                <!-- ICON -->
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 12h6m-6 4h6M9 8h6M5 4h14v16H5z"/>
                </svg>

                <span>Prova Final</span>
            </a>

            <!-- USUÁRIOS -->
            <a href="{{ route('controle.usuarios') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition
            {{ request()->routeIs('controle.usuarios') ? 'bg-green-600 text-white shadow' : 'hover:bg-gray-200' }}">

                <!-- ICON USERS -->
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M18 18.72a8.94 8.94 0 0 0-6-2.22 8.94 8.94 0 0 0-6 2.22M15 11.25a3 3 0 1 0-6 0 3 3 0 0 0 6 0z"/>
                </svg>

                <span>Usuários</span>
            </a>

            <!-- AVISOS -->
            <a href="{{ route('avisos') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition
            {{ request()->routeIs('avisos') || request()->routeIs('avisos.*') 
                ? 'bg-green-600 text-white shadow' 
                : 'text-gray-700 hover:bg-gray-200' }}">

                <!-- ICON BELL -->
                <svg xmlns="http://www.w3.org/2000/svg" 
                    class="w-5 h-5"
                    fill="none" 
                    viewBox="0 0 24 24" 
                    stroke="currentColor">

                    <path stroke-linecap="round" 
                        stroke-linejoin="round" 
                        stroke-width="1.5"
                        d="M14.857 17.082a23.848 23.848 0 0 1-5.714 0
                        M18 8a6 6 0 1 0-12 0c0 7-3 7-3 7h18s-3 0-3-7"/>
                </svg>

                <span>Avisos</span>
            </a>

        </nav>

    </div>

    <!-- USUÁRIO -->
    <div class="text-center mt-10">

        <div class="w-12 h-12 mx-auto rounded-full bg-green-600 flex items-center justify-center text-white font-bold text-lg">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </div>

        <h2 class="mt-2 font-semibold text-gray-800">
            {{ auth()->user()->name }}
        </h2>

        <span class="text-sm text-gray-500">
            {{ ucfirst(auth()->user()->tipo) }}
        </span>

    </div>

</aside>