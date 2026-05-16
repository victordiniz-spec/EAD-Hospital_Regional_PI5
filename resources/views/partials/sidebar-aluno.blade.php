<!-- BOTÃO MOBILE - ABRIR SIDEBAR -->
<button
    type="button"
    onclick="abrirSidebarAluno()"
    class="lg:hidden fixed top-4 left-4 z-50 bg-white text-gray-800 p-3 rounded-xl shadow-lg border border-gray-200"
>
    <svg xmlns="http://www.w3.org/2000/svg"
         class="w-6 h-6"
         fill="none"
         viewBox="0 0 24 24"
         stroke="currentColor">
        <path stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 6h16M4 12h16M4 18h16" />
    </svg>
</button>

<!-- OVERLAY MOBILE -->
<div
    id="overlaySidebarAluno"
    onclick="fecharSidebarAluno()"
    class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 hidden lg:hidden">
</div>

<!-- SIDEBAR -->
<aside
    id="sidebarAluno"
    class="
        fixed lg:static top-0 left-0 z-50
        w-64 bg-gray-100 text-gray-700 min-h-screen p-6
        flex flex-col justify-between
        transform -translate-x-full lg:translate-x-0
        transition-transform duration-300 ease-in-out
        shadow-2xl lg:shadow-none
    "
>

    <div>

        <!-- FECHAR MOBILE -->
        <div class="lg:hidden flex justify-end mb-4">
            <button
                type="button"
                onclick="fecharSidebarAluno()"
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 p-2 rounded-lg transition"
            >
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="w-5 h-5"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

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

                <svg xmlns="http://www.w3.org/2000/svg"
                     class="w-5 h-5"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="1.5"
                          d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/>
                </svg>

                <span>Home</span>
            </a>

            <!-- VIDEO AULAS -->
            <a href="{{ route('aluno.aulas') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg transition
               {{ request()->routeIs('aluno.aulas') || request()->routeIs('aluno.aulas.*')
                    ? 'bg-green-600 text-white shadow'
                    : 'hover:bg-gray-200' }}">

                <svg xmlns="http://www.w3.org/2000/svg"
                     class="w-5 h-5"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="1.5"
                          d="M5.25 5.653c0-1.427 1.54-2.33 2.79-1.637l9.54 5.347c1.26.707 1.26 2.567 0 3.274l-9.54 5.347c-1.25.693-2.79-.21-2.79-1.637V5.653z"/>
                </svg>

                <span>Video Aulas</span>
            </a>

            <!-- PROVA FINAL -->
            <a href="{{ route('prova.final') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg transition
               {{ request()->routeIs('prova.final') || request()->routeIs('prova.final.*')
                    ? 'bg-green-600 text-white shadow'
                    : 'hover:bg-gray-200' }}">

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
            <a href="{{ route('certificado.aluno') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg transition
               {{ request()->routeIs('certificado.aluno')
                    ? 'bg-green-600 text-white shadow'
                    : 'hover:bg-gray-200' }}">

                <svg xmlns="http://www.w3.org/2000/svg"
                     class="w-5 h-5"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="1.5"
                          d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                </svg>

                <span>Certificado</span>
            </a>

        </nav>

    </div>

    <!-- BOTÃO SAIR -->
    <div class="mt-10">
        <button type="button"
                onclick="abrirModalSairAluno()"
                class="w-full flex items-center justify-center gap-3 px-4 py-3 rounded-xl bg-red-50 text-red-600 font-semibold hover:bg-red-100 transition">

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
<div id="modalSairAluno"
     class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-[60]">

    <div class="bg-white w-full max-w-sm mx-4 rounded-2xl shadow-2xl p-6 text-center">

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

        <h2 class="text-xl font-bold text-gray-800 mb-2">
            Deseja sair?
        </h2>

        <p class="text-sm text-gray-500 mb-6">
            Você será desconectado da sua conta e voltará para a tela de login.
        </p>

        <div class="flex gap-3">
            <button type="button"
                    onclick="fecharModalSairAluno()"
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

<script>
    function abrirSidebarAluno() {
        const sidebar = document.getElementById('sidebarAluno');
        const overlay = document.getElementById('overlaySidebarAluno');

        if (!sidebar || !overlay) return;

        sidebar.classList.remove('-translate-x-full');
        sidebar.classList.add('translate-x-0');

        overlay.classList.remove('hidden');
    }

    function fecharSidebarAluno() {
        const sidebar = document.getElementById('sidebarAluno');
        const overlay = document.getElementById('overlaySidebarAluno');

        if (!sidebar || !overlay) return;

        sidebar.classList.add('-translate-x-full');
        sidebar.classList.remove('translate-x-0');

        overlay.classList.add('hidden');
    }

    function abrirModalSairAluno() {
        const modal = document.getElementById('modalSairAluno');

        if (!modal) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function fecharModalSairAluno() {
        const modal = document.getElementById('modalSairAluno');

        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    const modalSairAluno = document.getElementById('modalSairAluno');

    if (modalSairAluno) {
        modalSairAluno.addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalSairAluno();
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            fecharModalSairAluno();
            fecharSidebarAluno();
        }
    });
</script>