<!-- BOTÃO MOBILE - ABRIR SIDEBAR -->
<button
    type="button"
    onclick="abrirSidebarAluno()"
    class="
        lg:hidden fixed left-4 top-[88px] z-[9997]
        bg-[#004D3A] text-white p-3 rounded-2xl shadow-2xl
        border border-white/30
        hover:bg-[#003C2F] transition
    "
    aria-label="Abrir menu"
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
    class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[9998] hidden lg:hidden">
</div>

<!-- SIDEBAR -->
<aside
    id="sidebarAluno"
    class="
        fixed lg:static top-0 left-0 z-[9999]
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
                aria-label="Fechar menu"
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
               onclick="fecharSidebarAluno()"
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
               onclick="fecharSidebarAluno()"
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

            <!-- PROVA FINAL COM SENHA DE TESTE -->
            <button type="button"
                    onclick="abrirModalSenhaProvaFinalSidebar()"
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-lg transition text-left
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
            </button>

            <!-- CERTIFICADO -->
            <a href="{{ route('certificado.aluno') }}"
               onclick="fecharSidebarAluno()"
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

            @if(auth()->check() && auth()->user()->tipo === 'super_admin')
                <!-- VOLTAR AO PAINEL ADMIN -->
                <a href="{{ route('dashboard.professor') }}"
                   onclick="fecharSidebarAluno()"
                   class="flex items-center gap-3 px-4 py-3 rounded-lg transition
                   bg-yellow-50 text-yellow-800 border border-yellow-200 hover:bg-yellow-100">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-5 h-5"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="1.5"
                              d="M10.5 6h9.75M10.5 12h9.75M10.5 18h9.75M3.75 6h.008v.008H3.75V6zm0 6h.008v.008H3.75V12zm0 6h.008v.008H3.75V18z"/>
                    </svg>

                    <span>Painel Admin</span>
                </a>
            @endif

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

<!-- MODAL SENHA PROVA FINAL TESTE -->
<div id="modalSenhaProvaFinalSidebar"
     class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-[10000] px-4">

    <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl p-6 text-center border border-yellow-100">

        <div class="w-16 h-16 mx-auto rounded-full bg-yellow-100 text-yellow-700 flex items-center justify-center mb-4">
            <span class="text-2xl">🔐</span>
        </div>

        <h2 class="text-xl font-extrabold text-gray-800 mb-2">
            Acesso de teste
        </h2>

        <p class="text-sm text-gray-500 mb-5 leading-relaxed">
            Digite a senha temporária para liberar a prova final apenas para teste.
        </p>

        <input
            type="password"
            id="senhaProvaFinalSidebar"
            placeholder="Digite a senha"
            class="w-full px-4 py-3 rounded-2xl border border-yellow-200 bg-yellow-50 text-gray-800 text-center font-bold focus:outline-none focus:ring-2 focus:ring-yellow-400 mb-3"
        >

        <p id="erroSenhaProvaFinalSidebar"
           class="hidden text-sm text-red-600 font-bold mb-4">
            Senha incorreta. Tente novamente.
        </p>

        <div class="flex gap-3">
            <button type="button"
                    onclick="fecharModalSenhaProvaFinalSidebar()"
                    class="w-1/2 px-4 py-3 rounded-2xl bg-gray-100 text-gray-700 font-bold hover:bg-gray-200 transition">
                Cancelar
            </button>

            <button type="button"
                    onclick="validarSenhaProvaFinalSidebar()"
                    class="w-1/2 px-4 py-3 rounded-2xl bg-[#004D3A] text-white font-bold hover:bg-[#003C2F] transition">
                Entrar
            </button>
        </div>

        <a href="{{ route('prova.final') }}"
           class="block mt-4 text-xs text-gray-500 hover:text-[#004D3A] font-bold">
            Entrar sem senha e verificar liberação normal
        </a>

    </div>
</div>

<!-- MODAL DE CONFIRMAÇÃO DE SAIR -->
<div id="modalSairAluno"
     class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-[10000] px-4">

    <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl p-6 text-center">

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

    function abrirModalSenhaProvaFinalSidebar() {
        fecharSidebarAluno();

        const modal = document.getElementById('modalSenhaProvaFinalSidebar');
        const input = document.getElementById('senhaProvaFinalSidebar');
        const erro = document.getElementById('erroSenhaProvaFinalSidebar');

        if (!modal) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        if (input) {
            input.value = '';
            setTimeout(() => input.focus(), 150);
        }

        if (erro) {
            erro.classList.add('hidden');
        }
    }

    function fecharModalSenhaProvaFinalSidebar() {
        const modal = document.getElementById('modalSenhaProvaFinalSidebar');

        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function validarSenhaProvaFinalSidebar() {
        const input = document.getElementById('senhaProvaFinalSidebar');
        const erro = document.getElementById('erroSenhaProvaFinalSidebar');

        const senha = input ? input.value.trim() : '';

        if (senha === '123') {
            window.location.href = "{{ route('prova.final') }}?teste=123";
            return;
        }

        if (erro) {
            erro.classList.remove('hidden');
        }

        if (input) {
            input.value = '';
            input.focus();
        }
    }

    function abrirModalSairAluno() {
        fecharSidebarAluno();

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
    const modalSenhaProvaFinalSidebar = document.getElementById('modalSenhaProvaFinalSidebar');

    if (modalSairAluno) {
        modalSairAluno.addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalSairAluno();
            }
        });
    }

    if (modalSenhaProvaFinalSidebar) {
        modalSenhaProvaFinalSidebar.addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalSenhaProvaFinalSidebar();
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            fecharModalSairAluno();
            fecharSidebarAluno();
            fecharModalSenhaProvaFinalSidebar();
        }

        if (e.key === 'Enter') {
            const modalSenha = document.getElementById('modalSenhaProvaFinalSidebar');

            if (modalSenha && !modalSenha.classList.contains('hidden')) {
                validarSenhaProvaFinalSidebar();
            }
        }
    });
</script>