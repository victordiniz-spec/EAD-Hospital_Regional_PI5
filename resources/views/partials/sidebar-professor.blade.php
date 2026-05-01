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
            <a href="{{ route('prova.final.criar') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition
            {{ request()->routeIs('prova.final.criar') ? 'bg-green-600 text-white shadow' : 'hover:bg-gray-200' }}">
                
                <!-- ICON PROVA -->
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" 
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 12h6m-6 4h6M9 8h6M5 4h14v16H5z"/>
                </svg>

                <span>Prova Final</span>
            </a>

            <!-- CERTIFICADOS -->
            <a href="{{ route('certificados.criar') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition
            {{ request()->routeIs('certificados.*') ? 'bg-green-600 text-white shadow' : 'hover:bg-gray-200' }}">

                <!-- ICON CERTIFICADO -->
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 12l2 2 4-4m5-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>

                <span>Certificados</span>
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

    <!-- BOTÃO SAIR -->
    <div class="mt-10">
        <button type="button"
            onclick="abrirModalSair()"
            class="w-full flex items-center justify-center gap-3 px-4 py-3 rounded-xl bg-red-50 text-red-600 font-semibold hover:bg-red-100 transition">

            <!-- ICON SAIR -->
            <svg xmlns="http://www.w3.org/2000/svg" 
                class="w-5 h-5" 
                fill="none" 
                viewBox="0 0 24 24" 
                stroke="currentColor">
                <path stroke-linecap="round" 
                    stroke-linejoin="round" 
                    stroke-width="1.7"
                    d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75"/>
            </svg>

            <span>Sair</span>
        </button>
    </div>

</aside>

<!-- MODAL DE CONFIRMAÇÃO DE SAIR -->
<div id="modalSair"
    class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50">

    <div class="bg-white w-full max-w-sm mx-4 rounded-2xl shadow-2xl p-6 text-center">

        <!-- ÍCONE -->
        <div class="w-16 h-16 mx-auto rounded-full bg-red-100 flex items-center justify-center mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" 
                class="w-8 h-8 text-red-600" 
                fill="none" 
                viewBox="0 0 24 24" 
                stroke="currentColor">
                <path stroke-linecap="round" 
                    stroke-linejoin="round" 
                    stroke-width="1.8"
                    d="M12 9v3.75m0 3.75h.008v.008H12V16.5zm9-4.5a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
            </svg>
        </div>

        <!-- TEXTO -->
        <h2 class="text-xl font-bold text-gray-800 mb-2">
            Deseja sair?
        </h2>

        <p class="text-sm text-gray-500 mb-6">
            Você será desconectado da sua conta e voltará para a tela de login.
        </p>

        <!-- BOTÕES -->
        <div class="flex gap-3">
            <button type="button"
                onclick="fecharModalSair()"
                class="w-1/2 px-4 py-3 rounded-xl bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200 transition">
                Cancelar
            </button>

            <form method="POST" action="{{ route('logout') }}" class="w-1/2">
                @csrf

                <button type="submit"
                    class="w-full px-4 py-3 rounded-xl bg-red-600 text-white font-semibold hover:bg-red-700 transition shadow">
                    Sim, sair
                </button>
            </form>
        </div>

    </div>
</div>

<!-- SCRIPT DO MODAL -->
<script>
    function abrirModalSair() {
        const modal = document.getElementById('modalSair');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function fecharModalSair() {
        const modal = document.getElementById('modalSair');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Fechar ao clicar fora da caixinha
    document.getElementById('modalSair').addEventListener('click', function(e) {
        if (e.target === this) {
            fecharModalSair();
        }
    });

    // Fechar ao apertar ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            fecharModalSair();
        }
    });
</script>