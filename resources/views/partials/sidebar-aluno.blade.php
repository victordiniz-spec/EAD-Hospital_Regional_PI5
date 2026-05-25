@php
    $usuarioLogado = auth()->user();
    $ehSuperAdmin = auth()->check() && ($usuarioLogado->tipo ?? null) === 'super_admin';

    $itensAluno = [
        [
            'titulo' => 'Home',
            'url' => route('dashboard.aluno'),
            'ativo' => request()->routeIs('dashboard.aluno'),
            'onclick' => 'fecharSidebarAluno()',
            'icone' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/>',
        ],
        [
            'titulo' => 'Videoaulas',
            'url' => route('aluno.aulas'),
            'ativo' => request()->routeIs('aluno.aulas') || request()->routeIs('aluno.aulas.*'),
            'onclick' => 'fecharSidebarAluno()',
            'icone' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M5.25 5.653c0-1.427 1.54-2.33 2.79-1.637l9.54 5.347c1.26.707 1.26 2.567 0 3.274l-9.54 5.347c-1.25.693-2.79-.21-2.79-1.637V5.653z"/>',
        ],
        [
            'titulo' => 'Prova Final',
            'url' => '#',
            'ativo' => request()->routeIs('prova.final') || request()->routeIs('prova.final.*'),
            'onclick' => 'abrirModalSenhaProvaFinalSidebar()',
            'button' => true,
            'icone' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M9 12h6m-6 4h6M9 8h6M5 4h14v16H5z"/>',
        ],
        [
            'titulo' => 'Certificado',
            'url' => route('certificado.aluno'),
            'ativo' => request()->routeIs('certificado.aluno'),
            'onclick' => 'fecharSidebarAluno()',
            'icone' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>',
        ],
        [
            'titulo' => 'Suporte',
            'url' => route('suporte.index'),
            'ativo' => request()->routeIs('suporte.index'),
            'onclick' => 'fecharSidebarAluno()',
            'icone' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0zm3.75 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0zm3.75 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0zM21 12c0 4.418-4.03 8-9 8a9.77 9.77 0 0 1-3.58-.66L3 21l1.66-4.15A7.55 7.55 0 0 1 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>',
        ],
    ];
@endphp

<!-- BOTÃO MOBILE - ABRIR SIDEBAR -->
<button
    type="button"
    onclick="abrirSidebarAluno()"
    class="
        lg:hidden fixed left-4 top-[calc(env(safe-area-inset-top)+16px)] z-[9997]
        bg-[#004D3A] text-white p-3 rounded-2xl shadow-2xl
        border border-white/40 hover:bg-[#003C2F] active:scale-95 transition
    "
    aria-label="Abrir menu do aluno"
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

<!-- SIDEBAR ALUNO -->
<aside
    id="sidebarAluno"
    class="
        sidebar-aluno
        fixed lg:sticky top-0 left-0 z-[9999]
        w-72 lg:w-72 min-h-screen h-screen
        bg-white text-[#003C2F]
        border-r border-[#E3EBE4]
        px-5 py-5
        flex flex-col justify-between
        transform -translate-x-full lg:translate-x-0
        transition-all duration-300 ease-in-out
        shadow-2xl lg:shadow-none
        overflow-y-auto overflow-x-hidden
    "
    data-collapsed="false"
>

    <div class="min-w-0">

        <!-- FECHAR MOBILE -->
        <div class="lg:hidden flex justify-end mb-4">
            <button
                type="button"
                onclick="fecharSidebarAluno()"
                class="bg-[#F1F6F2] hover:bg-[#E6EFE8] text-[#003C2F] p-2 rounded-xl transition"
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

        <!-- LOGO / MARCA -->
        <div class="flex items-center gap-3 mb-8 px-1">
            <div class="
                w-14 h-14 rounded-full bg-white
                border border-[#DCE7DE]
                shadow-sm flex items-center justify-center shrink-0 overflow-hidden
            ">
                <img src="{{ asset('images/logo.png') }}"
                     alt="Logo"
                     class="w-full h-full object-cover rounded-full"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">

                <span class="hidden w-full h-full items-center justify-center bg-[#004D3A] text-white font-extrabold rounded-full">
                    IR
                </span>
            </div>

            <div class="sidebar-label min-w-0 transition-all duration-300">
                <h1 class="text-lg font-extrabold leading-tight truncate">
                    Integrar ReSaúde
                </h1>
                <p class="text-xs font-bold text-[#60756B] truncate">
                    Área do aluno
                </p>
            </div>
        </div>

        <!-- BOTÃO RECOLHER DESKTOP -->
        <div class="hidden lg:flex mb-5">
            <button
                type="button"
                onclick="alternarSidebarAluno()"
                class="
                    w-full flex items-center justify-center gap-2
                    px-4 py-3 rounded-2xl
                    bg-[#F8FBF8]
                    border border-[#DCE7DE]
                    text-[#004D3A]
                    hover:bg-[#EAF5EF]
                    transition font-extrabold text-xs
                "
                title="Recolher ou expandir menu"
            >
                <svg id="iconeRecolherAluno"
                     xmlns="http://www.w3.org/2000/svg"
                     class="w-5 h-5 shrink-0 transition-transform"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M15 19l-7-7 7-7" />
                </svg>

                <span class="sidebar-label">Recolher menu</span>
            </button>
        </div>

        <!-- MENU -->
        <nav class="space-y-7 text-sm font-bold">

            <div>
                <p class="sidebar-label px-4 mb-2 text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                    Menu principal
                </p>

                <div class="space-y-2">
                    @foreach($itensAluno as $item)
                        @if(!empty($item['button']))
                            <button type="button"
                                    onclick="{{ $item['onclick'] }}"
                                    title="{{ $item['titulo'] }}"
                                    class="
                                        sidebar-item w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition text-left
                                        {{ $item['ativo']
                                            ? 'bg-[#004D3A] text-white shadow-lg shadow-[#004D3A]/15'
                                            : 'text-[#3F5D51] hover:bg-[#EAF5EF]'
                                        }}
                                    ">
                                <span class="sidebar-icon w-6 h-6 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="w-5 h-5"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        {!! $item['icone'] !!}
                                    </svg>
                                </span>

                                <span class="sidebar-label truncate">
                                    {{ $item['titulo'] }}
                                </span>
                            </button>
                        @else
                            <a href="{{ $item['url'] }}"
                               onclick="{{ $item['onclick'] }}"
                               title="{{ $item['titulo'] }}"
                               class="
                                    sidebar-item flex items-center gap-3 px-4 py-3 rounded-2xl transition
                                    {{ $item['ativo']
                                        ? 'bg-[#004D3A] text-white shadow-lg shadow-[#004D3A]/15'
                                        : 'text-[#3F5D51] hover:bg-[#EAF5EF]'
                                    }}
                               ">
                                <span class="sidebar-icon w-6 h-6 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="w-5 h-5"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        {!! $item['icone'] !!}
                                    </svg>
                                </span>

                                <span class="sidebar-label truncate">
                                    {{ $item['titulo'] }}
                                </span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>

            @if($ehSuperAdmin)
                <div>
                    <p class="sidebar-label px-4 mb-2 text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                        Administração
                    </p>

                    <a href="{{ route('dashboard.professor') }}"
                       onclick="fecharSidebarAluno()"
                       title="Painel Admin"
                       class="
                            sidebar-item flex items-center gap-3 px-4 py-3 rounded-2xl transition
                            bg-yellow-50 text-yellow-800
                            border border-yellow-200 hover:bg-yellow-100
                       ">
                        <span class="sidebar-icon w-6 h-6 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-5 h-5"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.7"
                                      d="M10.5 6h9.75M10.5 12h9.75M10.5 18h9.75M3.75 6h.008v.008H3.75V6zm0 6h.008v.008H3.75V12zm0 6h.008v.008H3.75V18z"/>
                            </svg>
                        </span>

                        <span class="sidebar-label truncate">
                            Painel Admin
                        </span>
                    </a>
                </div>
            @endif

        </nav>

    </div>

    <!-- PERFIL / SAIR -->
    <div class="mt-8 space-y-3">

        <div class="
            sidebar-profile flex items-center gap-3 px-3 py-3 rounded-3xl
            bg-[#F8FBF8]
            border border-[#DCE7DE]
            shadow-sm
        ">
            <div class="w-11 h-11 rounded-full bg-[#004D3A] text-white flex items-center justify-center font-extrabold shrink-0 overflow-hidden">
                @if(!empty($usuarioLogado->foto ?? null))
                    <img src="{{ asset($usuarioLogado->foto) }}"
                         alt="Foto"
                         class="w-full h-full object-cover rounded-full">
                @else
                    {{ strtoupper(mb_substr($usuarioLogado->name ?? 'A', 0, 1)) }}
                @endif
            </div>

            <div class="sidebar-label min-w-0">
                <p class="text-sm font-extrabold truncate">
                    {{ $usuarioLogado->name ?? 'Aluno' }}
                </p>
                <p class="text-xs font-bold text-[#60756B] truncate">
                    {{ ucfirst($usuarioLogado->tipo ?? 'aluno') }}
                </p>
            </div>
        </div>

        <button type="button"
                onclick="abrirModalSairAluno()"
                title="Sair"
                class="
                    sidebar-item w-full flex items-center justify-center gap-3
                    px-4 py-3 rounded-2xl
                    bg-red-50
                    text-red-600
                    font-extrabold hover:bg-red-100 transition
                    border border-red-100
                ">

            <span class="sidebar-icon w-6 h-6 flex items-center justify-center shrink-0">
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
            </span>

            <span class="sidebar-label">
                Sair
            </span>
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

    <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl p-6 text-center border border-[#E3EBE4]">

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

        <h2 class="text-xl font-extrabold text-gray-800 mb-2">
            Deseja sair?
        </h2>

        <p class="text-sm text-gray-500 mb-6">
            Você será desconectado da sua conta e voltará para a tela de login.
        </p>

        <div class="flex gap-3">
            <button type="button"
                    onclick="fecharModalSairAluno()"
                    class="w-1/2 px-4 py-3 rounded-2xl bg-gray-100 text-gray-700 font-bold hover:bg-gray-200 transition">
                Cancelar
            </button>

            <form method="POST" action="{{ route('logout') }}" class="w-1/2">
                @csrf

                <button type="submit"
                        class="w-full px-4 py-3 rounded-2xl bg-red-600 text-white font-bold hover:bg-red-700 transition shadow">
                    Sim, sair
                </button>
            </form>
        </div>

    </div>
</div>

<style>
    #sidebarAluno[data-collapsed="true"] {
        width: 6rem;
        padding-left: 1rem;
        padding-right: 1rem;
    }

    #sidebarAluno[data-collapsed="true"] .sidebar-label {
        width: 0;
        opacity: 0;
        visibility: hidden;
        overflow: hidden;
        white-space: nowrap;
    }

    #sidebarAluno[data-collapsed="true"] .sidebar-item {
        justify-content: center;
        padding-left: 0.85rem;
        padding-right: 0.85rem;
    }

    #sidebarAluno[data-collapsed="true"] .sidebar-profile {
        justify-content: center;
        padding-left: 0.65rem;
        padding-right: 0.65rem;
    }

    #sidebarAluno[data-collapsed="true"] #iconeRecolherAluno {
        transform: rotate(180deg);
    }

    @media (max-width: 1023px) {
        #sidebarAluno[data-collapsed="true"] {
            width: 18rem;
            padding-left: 1.25rem;
            padding-right: 1.25rem;
        }

        #sidebarAluno[data-collapsed="true"] .sidebar-label {
            width: auto;
            opacity: 1;
            visibility: visible;
            overflow: visible;
            white-space: normal;
        }

        #sidebarAluno[data-collapsed="true"] .sidebar-item,
        #sidebarAluno[data-collapsed="true"] .sidebar-profile {
            justify-content: flex-start;
        }
    }
</style>

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

    function alternarSidebarAluno() {
        const sidebar = document.getElementById('sidebarAluno');

        if (!sidebar) return;

        const recolhida = sidebar.getAttribute('data-collapsed') === 'true';
        const novoEstado = recolhida ? 'false' : 'true';

        sidebar.setAttribute('data-collapsed', novoEstado);

        try {
            localStorage.setItem('sidebarAlunoCollapsed', novoEstado);
        } catch (e) {}
    }

    function aplicarEstadoSidebarAluno() {
        const sidebar = document.getElementById('sidebarAluno');

        if (!sidebar) return;

        try {
            const salvo = localStorage.getItem('sidebarAlunoCollapsed');

            if (salvo === 'true') {
                sidebar.setAttribute('data-collapsed', 'true');
            }
        } catch (e) {}
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

    document.addEventListener('DOMContentLoaded', aplicarEstadoSidebarAluno);
</script>
