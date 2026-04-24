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
            <a href="{{ route('dashboard.aluno') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition
            {{ request()->routeIs('dashboard.aluno') ? 'bg-green-600 text-white shadow' : 'hover:bg-gray-200' }}">

                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/>
                </svg>

                <span>Home</span>
            </a>

            <!-- VIDEO AULAS -->
            <a href="{{ route('aluno.aulas') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition
            hover:bg-gray-200">

                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M5.25 5.653c0-1.427 1.54-2.33 2.79-1.637l9.54 5.347c1.26.707 1.26 2.567 0 3.274l-9.54 5.347c-1.25.693-2.79-.21-2.79-1.637V5.653z"/>
                </svg>

                <span>Video Aulas</span>
            </a>

            <!-- PROVA FINAL -->
            <a href="{{ route('prova.final') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition
            {{ request()->routeIs('prova.final') || request()->routeIs('prova.final.*') 
                ? 'bg-emerald-600 text-white shadow' 
                : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">

                <!-- ICON DOCUMENTO -->
                <svg xmlns="http://www.w3.org/2000/svg" 
                    class="w-5 h-5"
                    fill="none" 
                    viewBox="0 0 24 24" 
                    stroke="currentColor">

                    <path stroke-linecap="round" 
                        stroke-linejoin="round" 
                        stroke-width="1.5"
                        d="M9 12h6m-6 4h6M9 8h6M5 4h14v16H5z"/>
                </svg>

                <span>Prova Final</span>
            </a>

            <!-- CERTIFICADO -->
            <a href="{{ route('certificado.gerar', 1) }}"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition
            hover:bg-gray-200">

                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 12l2 2 4-4m5-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>

                <span>Certificado</span>
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