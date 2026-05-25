@php
    $usuarioLogado = auth()->user();
    $ehSuperAdmin = auth()->check() && ($usuarioLogado->tipo ?? null) === 'super_admin';

    $abaAvisosAtual = request('aba', 'meus');
    $avisosAbertoSidebar = request()->routeIs('avisos') || request()->routeIs('avisos.*');
    $avisosCriarAtivo = request()->routeIs('avisos') && $abaAvisosAtual === 'criar';
    $avisosMeusAtivo = request()->routeIs('avisos') && $abaAvisosAtual !== 'criar';

    $itensProfessor = [
        [
            'titulo' => 'Home',
            'url' => route('dashboard.professor'),
            'ativo' => request()->routeIs('dashboard.professor'),
            'icone' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/>',
        ],
        [
            'titulo' => 'Acompanhamento',
            'url' => route('acompanhamento.residentes'),
            'ativo' => request()->routeIs('acompanhamento.residentes'),
            'icone' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3 13.5h3.75v6H3v-6zM10.125 9h3.75v10.5h-3.75V9zM17.25 4.5H21v15h-3.75v-15zM3 21h18"/>',
        ],
        [
            'titulo' => 'Videoaulas',
            'url' => route('videoaulas'),
            'ativo' => request()->routeIs('videoaulas') || request()->routeIs('aulas.*'),
            'icone' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M5.25 5.653c0-1.427 1.54-2.33 2.79-1.637l9.54 5.347c1.26.707 1.26 2.567 0 3.274l-9.54 5.347c-1.25.693-2.79-.21-2.79-1.637V5.653z"/>',
        ],
        [
            'titulo' => 'Prova Final',
            'url' => route('prova.final.criar'),
            'ativo' => request()->routeIs('prova.final.criar') || request()->routeIs('prova.final.store'),
            'icone' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M9 12h6m-6 4h6M9 8h6M5 4h14v16H5z"/>',
        ],
        [
            'titulo' => 'Certificados',
            'url' => route('certificados.criar'),
            'ativo' => request()->routeIs('certificados.*'),
            'icone' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M9 12l2 2 4-4m5-2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        ],
        [
            'titulo' => 'Usuários',
            'url' => route('controle.usuarios'),
            'ativo' => request()->routeIs('controle.usuarios'),
            'icone' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M18 18.72a8.94 8.94 0 0 0-6-2.22 8.94 8.94 0 0 0-6 2.22M15 11.25a3 3 0 1 0-6 0 3 3 0 0 0 6 0z"/>',
        ],
        [
            'titulo' => 'Suporte',
            'url' => route('suporte.admin'),
            'ativo' => request()->routeIs('suporte.*'),
            'icone' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0zm3.75 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0zm3.75 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0zM21 12c0 4.418-4.03 8-9 8a9.77 9.77 0 0 1-3.58-.66L3 21l1.66-4.15A7.55 7.55 0 0 1 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>',
        ],
    ];
@endphp

<!-- BOTÃO MOBILE - ABRIR SIDEBAR -->
<button
    type="button"
    onclick="abrirSidebarProfessor()"
    class="
        lg:hidden fixed left-4 top-[calc(env(safe-area-inset-top)+16px)] z-[9997]
        bg-[#004D3A] text-white p-3 rounded-2xl shadow-2xl
        border border-white/40 hover:bg-[#003C2F] active:scale-95 transition
    "
    aria-label="Abrir menu do professor"
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
    id="overlaySidebarProfessor"
    onclick="fecharSidebarProfessor()"
    class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[9998] hidden lg:hidden">
</div>

<!-- SIDEBAR PROFESSOR -->
<aside
    id="sidebarProfessor"
    class="
        sidebar-professor
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
                onclick="fecharSidebarProfessor()"
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
                    Painel do professor
                </p>
            </div>
        </div>

        <!-- BOTÃO RECOLHER DESKTOP -->
        <div class="hidden lg:flex mb-5">
            <button
                type="button"
                onclick="alternarSidebarProfessor()"
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
                <svg id="iconeRecolherProfessor"
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
                    @foreach($itensProfessor as $item)
                        <a href="{{ $item['url'] }}"
                           onclick="fecharSidebarProfessor()"
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
                    @endforeach

                    <!-- CASCATA DE AVISOS -->
                    <div class="sidebar-cascade">
                        <button type="button"
                                onclick="alternarCascataAvisosProfessor()"
                                title="Avisos"
                                class="
                                    sidebar-item w-full flex items-center gap-3 px-4 py-3 rounded-2xl transition text-left
                                    {{ $avisosAbertoSidebar
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
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="1.7"
                                          d="M14.857 17.082a23.848 23.848 0 0 1-5.714 0M18 8a6 6 0 1 0-12 0c0 7-3 7-3 7h18s-3 0-3-7"/>
                                </svg>
                            </span>

                            <span class="sidebar-label truncate flex-1">
                                Avisos
                            </span>

                            <span id="iconeCascataAvisosProfessor"
                                  class="sidebar-label transition-transform duration-300 {{ $avisosAbertoSidebar ? 'rotate-180' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-4 h-4"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="m19 9-7 7-7-7"/>
                                </svg>
                            </span>
                        </button>

                        <div id="cascataAvisosProfessor"
                             class="{{ $avisosAbertoSidebar ? '' : 'hidden' }} sidebar-submenu mt-2 ml-10 space-y-2">
                            <a href="{{ route('avisos', ['aba' => 'criar']) }}"
                               onclick="fecharSidebarProfessor()"
                               class="
                                    sidebar-subitem flex items-center gap-2 px-4 py-2.5 rounded-2xl text-sm font-extrabold transition
                                    {{ $avisosCriarAtivo
                                        ? 'bg-[#EAF5EF] text-[#004D3A] border border-[#DCE7DE]'
                                        : 'text-[#60756B] hover:bg-[#F8FBF8] hover:text-[#004D3A]'
                                    }}
                               ">
                                <span class="w-2 h-2 rounded-full {{ $avisosCriarAtivo ? 'bg-[#00A63E]' : 'bg-[#AFC5B5]' }}"></span>
                                <span class="sidebar-label truncate">Criar aviso</span>
                            </a>

                            <a href="{{ route('avisos', ['aba' => 'meus']) }}"
                               onclick="fecharSidebarProfessor()"
                               class="
                                    sidebar-subitem flex items-center gap-2 px-4 py-2.5 rounded-2xl text-sm font-extrabold transition
                                    {{ $avisosMeusAtivo
                                        ? 'bg-[#EAF5EF] text-[#004D3A] border border-[#DCE7DE]'
                                        : 'text-[#60756B] hover:bg-[#F8FBF8] hover:text-[#004D3A]'
                                    }}
                               ">
                                <span class="w-2 h-2 rounded-full {{ $avisosMeusAtivo ? 'bg-[#00A63E]' : 'bg-[#AFC5B5]' }}"></span>
                                <span class="sidebar-label truncate">Meus avisos</span>
                            </a>
                        </div>
                    </div>

                </div>
            </div>

            @if($ehSuperAdmin)
                <div>
                    <p class="sidebar-label px-4 mb-2 text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                        Acesso especial
                    </p>

                    <a href="{{ route('dashboard.aluno') }}"
                       onclick="fecharSidebarProfessor()"
                       title="Ver como aluno"
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
                                      d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0zM4.5 20.25a8.25 8.25 0 0 1 15 0"/>
                            </svg>
                        </span>

                        <span class="sidebar-label truncate">
                            Ver como aluno
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
                    {{ strtoupper(mb_substr($usuarioLogado->name ?? 'P', 0, 1)) }}
                @endif
            </div>

            <div class="sidebar-label min-w-0">
                <p class="text-sm font-extrabold truncate">
                    {{ $usuarioLogado->name ?? 'Professor' }}
                </p>
                <p class="text-xs font-bold text-[#60756B] truncate">
                    {{ ucfirst(str_replace('_', ' ', $usuarioLogado->tipo ?? 'professor')) }}
                </p>
            </div>
        </div>

        <button type="button"
                onclick="abrirModalSair()"
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

<!-- MODAL DE CONFIRMAÇÃO DE SAIR -->
<div id="modalSair"
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
                    onclick="fecharModalSair()"
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
    #sidebarProfessor[data-collapsed="true"] {
        width: 6rem;
        padding-left: 1rem;
        padding-right: 1rem;
    }

    #sidebarProfessor[data-collapsed="true"] .sidebar-label {
        width: 0;
        opacity: 0;
        visibility: hidden;
        overflow: hidden;
        white-space: nowrap;
    }

    #sidebarProfessor[data-collapsed="true"] .sidebar-item {
        justify-content: center;
        padding-left: 0.85rem;
        padding-right: 0.85rem;
    }

    #sidebarProfessor[data-collapsed="true"] .sidebar-profile {
        justify-content: center;
        padding-left: 0.65rem;
        padding-right: 0.65rem;
    }

    #sidebarProfessor[data-collapsed="true"] .sidebar-submenu {
        display: none !important;
    }

    #sidebarProfessor[data-collapsed="true"] #iconeRecolherProfessor {
        transform: rotate(180deg);
    }

    @media (max-width: 1023px) {
        #sidebarProfessor[data-collapsed="true"] {
            width: 18rem;
            padding-left: 1.25rem;
            padding-right: 1.25rem;
        }

        #sidebarProfessor[data-collapsed="true"] .sidebar-label {
            width: auto;
            opacity: 1;
            visibility: visible;
            overflow: visible;
            white-space: normal;
        }

        #sidebarProfessor[data-collapsed="true"] .sidebar-item,
        #sidebarProfessor[data-collapsed="true"] .sidebar-profile {
            justify-content: flex-start;
        }
    }
</style>

<script>

    function alternarCascataAvisosProfessor() {
        const submenu = document.getElementById('cascataAvisosProfessor');
        const icone = document.getElementById('iconeCascataAvisosProfessor');
        const sidebar = document.getElementById('sidebarProfessor');

        if (!submenu) return;

        if (sidebar && sidebar.getAttribute('data-collapsed') === 'true') {
            window.location.href = "{{ route('avisos', ['aba' => 'meus']) }}";
            return;
        }

        submenu.classList.toggle('hidden');

        if (icone) {
            icone.classList.toggle('rotate-180');
        }
    }

    function abrirSidebarProfessor() {
        const sidebar = document.getElementById('sidebarProfessor');
        const overlay = document.getElementById('overlaySidebarProfessor');

        if (!sidebar || !overlay) return;

        sidebar.classList.remove('-translate-x-full');
        sidebar.classList.add('translate-x-0');

        overlay.classList.remove('hidden');
    }

    function fecharSidebarProfessor() {
        const sidebar = document.getElementById('sidebarProfessor');
        const overlay = document.getElementById('overlaySidebarProfessor');

        if (!sidebar || !overlay) return;

        sidebar.classList.add('-translate-x-full');
        sidebar.classList.remove('translate-x-0');

        overlay.classList.add('hidden');
    }

    function alternarSidebarProfessor() {
        const sidebar = document.getElementById('sidebarProfessor');

        if (!sidebar) return;

        const recolhida = sidebar.getAttribute('data-collapsed') === 'true';
        const novoEstado = recolhida ? 'false' : 'true';

        sidebar.setAttribute('data-collapsed', novoEstado);

        try {
            localStorage.setItem('sidebarProfessorCollapsed', novoEstado);
        } catch (e) {}
    }

    function aplicarEstadoSidebarProfessor() {
        const sidebar = document.getElementById('sidebarProfessor');

        if (!sidebar) return;

        try {
            const salvo = localStorage.getItem('sidebarProfessorCollapsed');

            if (salvo === 'true') {
                sidebar.setAttribute('data-collapsed', 'true');
            }
        } catch (e) {}
    }

    function abrirModalSair() {
        fecharSidebarProfessor();

        const modal = document.getElementById('modalSair');

        if (!modal) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function fecharModalSair() {
        const modal = document.getElementById('modalSair');

        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    const modalSair = document.getElementById('modalSair');

    if (modalSair) {
        modalSair.addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalSair();
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            fecharModalSair();
            fecharSidebarProfessor();
        }
    });

    document.addEventListener('DOMContentLoaded', aplicarEstadoSidebarProfessor);
</script>
