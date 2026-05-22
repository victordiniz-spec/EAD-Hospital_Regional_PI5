<nav class="w-full bg-white/90 backdrop-blur border-b border-[#E3EBE4] px-4 sm:px-6 lg:px-8 py-3 flex justify-end items-center shadow-sm relative z-50">

    <div class="flex items-center gap-2 sm:gap-3 max-w-full">

        @php
            use Illuminate\Support\Facades\DB;
            use Illuminate\Support\Facades\Schema;

            $usuario = auth()->user();
            $nome = trim($usuario->name ?? 'Usuário');
            $partes = preg_split('/\s+/', $nome);

            $iniciais = strtoupper(substr($partes[0] ?? 'U', 0, 1));

            if (count($partes) > 1) {
                $iniciais .= strtoupper(substr($partes[count($partes) - 1], 0, 1));
            }

            $tipoUsuario = strtolower($usuario->tipo ?? 'usuario');

            $tipoFormatado = match ($tipoUsuario) {
                'residente' => 'Residente',
                'preceptor' => 'Preceptor',
                'professor' => 'Preceptor',
                'admin' => 'Administrador',
                'administrador' => 'Administrador',
                'super_admin' => 'Super Administrador',
                default => ucfirst(str_replace('_', ' ', $tipoUsuario)),
            };

            $mostrarAvisosAluno = in_array($tipoUsuario, ['residente', 'preceptor', 'aluno']);
            $mostrarPendentesProfessor = in_array($tipoUsuario, ['preceptor', 'professor', 'admin', 'administrador', 'super_admin']);

            $usuariosPendentesNavbar = collect();
            $totalPendentesNavbar = 0;

            if ($mostrarPendentesProfessor && Schema::hasTable('users')) {
                $queryPendentesNavbar = DB::table('users')
                    ->where(function ($query) {
                        $query->where('status', 'pendente')
                              ->orWhere('status', 'aguardando')
                              ->orWhere('status', 'aguardando_aprovacao');
                    });

                $totalPendentesNavbar = (clone $queryPendentesNavbar)->count();

                $usuariosPendentesNavbar = $queryPendentesNavbar
                    ->orderByDesc('created_at')
                    ->limit(10)
                    ->get();
            }

            $avisosNavbar = collect();

            if ($mostrarAvisosAluno && Schema::hasTable('avisos')) {
                $queryAvisos = DB::table('avisos');

                if (Schema::hasColumn('avisos', 'expires_at')) {
                    $queryAvisos->where(function ($query) {
                        $query->whereNull('expires_at')
                              ->orWhere('expires_at', '>=', now());
                    });
                }

                if (Schema::hasColumn('avisos', 'status')) {
                    $queryAvisos->where(function ($query) {
                        $query->whereNull('status')
                              ->orWhere('status', 'publicado');
                    });
                }

                $avisosNavbar = $queryAvisos
                    ->orderByRaw("
                        CASE
                            WHEN categoria = 'urgente' THEN 0
                            WHEN tipo = 'urgente' THEN 0
                            ELSE 1
                        END
                    ")
                    ->orderByDesc('created_at')
                    ->limit(10)
                    ->get();
            }

            $idsAvisosNavbar = $avisosNavbar->pluck('id')->values()->toArray();

            $primeiroNome = $partes[0] ?? $nome;
            $ultimoNome = count($partes) > 1 ? $partes[count($partes) - 1] : '';
            $nomeMobile = trim($primeiroNome . ' ' . $ultimoNome);
        @endphp

        @if($mostrarPendentesProfessor)
            <!-- NOTIFICAÇÕES DE USUÁRIOS PENDENTES DO PROFESSOR/ADMIN -->
            <div class="relative shrink-0">

                <button type="button"
                        onclick="toggleDropdownPendentesNavbar()"
                        class="relative w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-[#F8FBF8] border border-[#DCE7DE] flex items-center justify-center text-[#004D3A] hover:bg-[#EAF5EF] transition shadow-sm"
                        title="Usuários pendentes">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-5 h-5 sm:w-6 sm:h-6"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="1.8"
                              d="M18 18.72a8.94 8.94 0 0 0-6-2.22 8.94 8.94 0 0 0-6 2.22M15 11.25a3 3 0 1 0-6 0 3 3 0 0 0 6 0z"/>
                    </svg>

                    <span id="badgePendentesNavbar"
                          class="absolute -top-1 -right-1 min-w-[20px] h-5 px-1 rounded-full {{ $totalPendentesNavbar > 0 ? 'bg-red-600' : 'bg-green-600' }} text-white text-[10px] font-extrabold flex items-center justify-center ring-2 ring-white">
                        {{ $totalPendentesNavbar > 99 ? '99+' : $totalPendentesNavbar }}
                    </span>
                </button>

                <!-- DROPDOWN USUÁRIOS PENDENTES -->
                <div id="dropdownPendentesNavbar"
                     class="hidden fixed sm:absolute right-3 sm:right-0 top-[72px] sm:top-auto sm:mt-3 w-[calc(100vw-24px)] sm:w-[420px] bg-white border border-[#E3EBE4] rounded-3xl shadow-2xl overflow-hidden z-[999]">

                    <div class="p-5 border-b border-[#E3EBE4] bg-[#F8FBF8]">

                        <div class="flex items-start justify-between gap-3">

                            <div>
                                <h3 class="text-lg font-extrabold text-[#003C2F]">
                                    Usuários pendentes
                                </h3>

                                <p id="textoStatusPendentesNavbar" class="text-xs text-[#60756B] mt-1">
                                    {{ $totalPendentesNavbar > 0 ? $totalPendentesNavbar . ' solicitação(ões) aguardando aprovação.' : 'Nenhuma solicitação pendente agora.' }}
                                </p>
                            </div>

                            <a href="{{ route('controle.usuarios') }}"
                               class="text-xs bg-[#004D3A] text-white px-3 py-2 rounded-xl font-bold hover:bg-[#003C2F] transition whitespace-nowrap">
                                Abrir controle
                            </a>

                        </div>

                    </div>

                    <div id="listaPendentesNavbar" class="max-h-[380px] overflow-y-auto p-3 space-y-3">

                        @forelse($usuariosPendentesNavbar as $pendente)

                            @php
                                $nomePendente = $pendente->name ?? 'Usuário sem nome';
                                $emailPendente = $pendente->email ?? 'Sem e-mail';
                                $tipoPendente = ucfirst(str_replace('_', ' ', $pendente->tipo ?? 'usuário'));
                                $inicialPendente = strtoupper(substr($nomePendente, 0, 1));
                            @endphp

                            <div class="pendente-navbar-item bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-4 border-l-4 border-l-yellow-500"
                                 data-user-id="{{ $pendente->id }}">

                                <div class="flex items-start gap-3">

                                    <div class="w-11 h-11 rounded-2xl bg-yellow-100 text-yellow-700 flex items-center justify-center font-extrabold shrink-0">
                                        {{ $inicialPendente }}
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="min-w-0">
                                                <p class="font-extrabold text-[#003C2F] text-sm leading-tight break-words">
                                                    {{ $nomePendente }}
                                                </p>

                                                <p class="text-xs text-[#60756B] mt-1 break-words">
                                                    {{ $emailPendente }}
                                                </p>
                                            </div>

                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-yellow-100 text-yellow-700 whitespace-nowrap">
                                                PENDENTE
                                            </span>
                                        </div>

                                        <div class="mt-3 flex flex-wrap items-center gap-2">
                                            <span class="inline-flex bg-[#EAF5EF] text-[#004D3A] px-2.5 py-1 rounded-full text-[10px] font-bold uppercase">
                                                {{ $tipoPendente }}
                                            </span>

                                            @if(!empty($pendente->cpf))
                                                <span class="text-[11px] text-[#60756B] font-semibold">
                                                    CPF: {{ $pendente->cpf }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="mt-3 flex flex-col sm:flex-row gap-2">
                                            <form method="POST" action="{{ route('usuario.aprovar', $pendente->id) }}" class="w-full">
                                                @csrf
                                                <button type="submit"
                                                        class="w-full bg-[#00A63E] hover:bg-[#008F35] text-white px-3 py-2 rounded-xl text-xs font-extrabold transition">
                                                    Aprovar
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('usuario.rejeitar', $pendente->id) }}" class="w-full">
                                                @csrf
                                                <button type="submit"
                                                        class="w-full bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 px-3 py-2 rounded-xl text-xs font-extrabold transition">
                                                    Rejeitar
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                </div>

                            </div>

                        @empty

                            <div id="pendentesNavbarVazio" class="p-6 text-center text-[#60756B]">
                                <div class="w-12 h-12 rounded-full bg-[#EAF5EF] text-[#004D3A] mx-auto mb-3 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="w-6 h-6"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="1.8"
                                              d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                                    </svg>
                                </div>

                                <p class="text-sm font-bold">
                                    Nenhum usuário pendente.
                                </p>
                            </div>

                        @endforelse

                    </div>

                </div>

            </div>
        @endif

        @if($mostrarAvisosAluno)
            <!-- NOTIFICAÇÕES DE AVISOS -->
            <div class="relative shrink-0">

                <button type="button"
                        onclick="toggleDropdownAvisosNavbar()"
                        class="relative w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-[#F8FBF8] border border-[#DCE7DE] flex items-center justify-center text-[#004D3A] hover:bg-[#EAF5EF] transition shadow-sm"
                        title="Avisos">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-5 h-5 sm:w-6 sm:h-6"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="1.8"
                              d="M14.857 17.082a23.848 23.848 0 0 1-5.714 0M18 8a6 6 0 1 0-12 0c0 7-3 7-3 7h18s-3 0-3-7"/>
                    </svg>

                    <span id="badgeAvisosNavbar"
                          class="absolute -top-1 -right-1 min-w-[20px] h-5 px-1 rounded-full bg-red-600 text-white text-[10px] font-extrabold flex items-center justify-center ring-2 ring-white">
                        0
                    </span>
                </button>

                <!-- DROPDOWN AVISOS -->
                <div id="dropdownAvisosNavbar"
                     class="hidden fixed sm:absolute right-3 sm:right-0 top-[72px] sm:top-auto sm:mt-3 w-[calc(100vw-24px)] sm:w-[390px] bg-white border border-[#E3EBE4] rounded-3xl shadow-2xl overflow-hidden z-[999]">

                    <div class="p-5 border-b border-[#E3EBE4] bg-[#F8FBF8]">

                        <div class="flex items-start justify-between gap-3">

                            <div>
                                <h3 class="text-lg font-extrabold text-[#003C2F]">
                                    Avisos
                                </h3>

                                <p id="textoStatusAvisosNavbar" class="text-xs text-[#60756B] mt-1">
                                    Verificando avisos...
                                </p>
                            </div>

                            <button type="button"
                                    onclick="marcarTodosAvisosComoVistosNavbar()"
                                    class="text-xs bg-[#004D3A] text-white px-3 py-2 rounded-xl font-bold hover:bg-[#003C2F] transition">
                                Marcar vistos
                            </button>

                        </div>

                    </div>

                    <div class="max-h-[360px] overflow-y-auto p-3 space-y-3">

                        @forelse($avisosNavbar as $aviso)

                            @php
                                $categoriaAviso = strtolower($aviso->categoria ?? $aviso->tipo ?? 'importante');
                                $urgente = $categoriaAviso === 'urgente';
                                $mensagemAviso = $aviso->mensagem ?? $aviso->descricao ?? '';
                            @endphp

                            <div class="aviso-navbar-item bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-4 border-l-4 {{ $urgente ? 'border-l-red-500' : 'border-l-[#004D3A]' }}"
                                 data-aviso-id="{{ $aviso->id }}">

                                <div class="flex items-start justify-between gap-3 mb-2">

                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-extrabold
                                        {{ $urgente ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                        {{ $urgente ? 'URGENTE' : 'IMPORTANTE' }}
                                    </span>

                                    <span class="status-aviso-navbar text-[10px] font-bold text-red-600">
                                        Não visto
                                    </span>

                                </div>

                                <p class="font-extrabold text-[#003C2F] text-sm leading-tight">
                                    {{ $aviso->titulo }}
                                </p>

                                <p class="text-xs text-[#60756B] mt-2 leading-relaxed">
                                    {{ \Illuminate\Support\Str::limit($mensagemAviso, 120) }}
                                </p>

                                @if(isset($aviso->expires_at) && $aviso->expires_at)
                                    <p class="text-[10px] text-[#8A9B92] mt-3 font-semibold">
                                        Disponível até {{ \Carbon\Carbon::parse($aviso->expires_at)->format('d/m/Y H:i') }}
                                    </p>
                                @endif

                            </div>

                        @empty

                            <div class="p-6 text-center text-[#60756B]">
                                <div class="w-12 h-12 rounded-full bg-[#EAF5EF] text-[#004D3A] mx-auto mb-3 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="w-6 h-6"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="1.8"
                                              d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                                    </svg>
                                </div>

                                <p class="text-sm font-bold">
                                    Nenhum aviso ativo.
                                </p>
                            </div>

                        @endforelse

                    </div>

                </div>

            </div>
        @endif

        <!-- NOME E TIPO DO USUÁRIO -->
        <button type="button"
                onclick="toggleMenuPerfilNavbar()"
                class="text-right min-w-0 max-w-[150px] sm:max-w-[260px] hover:bg-[#F8FBF8] px-2 py-1.5 rounded-2xl transition">

            <p class="text-xs sm:text-sm font-extrabold text-[#003C2F] leading-tight truncate">
                <span class="sm:hidden">{{ $nomeMobile }}</span>
                <span class="hidden sm:inline">{{ $nome }}</span>
            </p>

            <p class="text-[10px] sm:text-xs text-[#6B7C73] font-bold truncate">
                {{ $tipoFormatado }}
            </p>
        </button>

        <!-- MENU DO PERFIL -->
        <div class="relative shrink-0">

            <button type="button"
                    onclick="toggleMenuPerfilNavbar()"
                    class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-[#00A63E] flex items-center justify-center text-white font-bold shadow-md ring-4 ring-green-100 hover:scale-105 transition"
                    title="Menu do perfil">
                {{ $iniciais }}
            </button>

            <div id="menuPerfilNavbar"
                 class="hidden fixed sm:absolute right-3 sm:right-0 top-[72px] sm:top-auto sm:mt-3 w-[calc(100vw-24px)] sm:w-[300px] bg-white border border-[#E3EBE4] rounded-3xl shadow-2xl overflow-hidden z-[999]">

                <!-- TOPO DO MENU -->
                <div class="p-5 bg-[#F8FBF8] border-b border-[#E3EBE4]">

                    <div class="flex items-center gap-3">

                        <div class="w-12 h-12 rounded-full bg-[#00A63E] text-white flex items-center justify-center font-extrabold shadow shrink-0">
                            {{ $iniciais }}
                        </div>

                        <div class="min-w-0">
                            <p class="text-sm font-extrabold text-[#003C2F] truncate">
                                {{ $nome }}
                            </p>

                            <p class="text-xs text-[#60756B] font-bold">
                                {{ $tipoFormatado }}
                            </p>
                        </div>

                    </div>

                </div>

                <!-- BOTÃO MODO ESCURO -->
                <button type="button"
                        onclick="alternarTemaSistema()"
                        class="w-full px-5 py-4 flex items-center gap-3 text-left hover:bg-[#F8FBF8] transition">

                    <span class="w-10 h-10 rounded-2xl bg-[#EAF5EF] text-[#004D3A] flex items-center justify-center">

                        <!-- LUA -->
                        <svg data-tema-icone-lua
                             xmlns="http://www.w3.org/2000/svg"
                             class="w-5 h-5"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="1.8"
                                  d="M21.752 15.002A9.718 9.718 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998z"/>
                        </svg>

                        <!-- SOL -->
                        <svg data-tema-icone-sol
                             xmlns="http://www.w3.org/2000/svg"
                             class="w-5 h-5 hidden"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="1.8"
                                  d="M12 3v2.25m0 13.5V21m9-9h-2.25M5.25 12H3m15.364-6.364-1.591 1.591M7.227 16.773l-1.591 1.591m12.728 0-1.591-1.591M7.227 7.227 5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0z"/>
                        </svg>

                    </span>

                    <div>
                        <p class="text-sm font-extrabold text-[#003C2F]" data-tema-texto>
                            Modo escuro
                        </p>

                        <p class="text-xs text-[#60756B]">
                            Alterar aparência do sistema
                        </p>
                    </div>

                </button>

                <!-- SAIR -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit"
                            class="w-full px-5 py-4 flex items-center gap-3 text-left hover:bg-red-50 transition border-t border-[#E3EBE4]">

                        <span class="w-10 h-10 rounded-2xl bg-red-100 text-red-600 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-5 h-5"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/>
                            </svg>
                        </span>

                        <div>
                            <p class="text-sm font-extrabold text-red-600">
                                Sair
                            </p>

                            <p class="text-xs text-red-500">
                                Encerrar sessão
                            </p>
                        </div>

                    </button>
                </form>

            </div>

        </div>

    </div>

</nav>

@if($mostrarPendentesProfessor)
    <script>
        let totalPendentesNavbarAtual = {{ (int) $totalPendentesNavbar }};
        let idsPendentesNavbarAtual = @json($usuariosPendentesNavbar->pluck('id')->values()->toArray());
        let primeiraVerificacaoPendentes = true;
        const urlPendentesNavbar = "{{ url('/navbar/usuarios-pendentes') }}";
        const urlControleUsuariosNavbar = "{{ route('controle.usuarios') }}";
        const csrfNavbar = "{{ csrf_token() }}";

        function atualizarBadgePendentesNavbar(total) {
            const badge = document.getElementById('badgePendentesNavbar');
            const textoStatus = document.getElementById('textoStatusPendentesNavbar');

            if (!badge) return;

            if (total > 0) {
                badge.innerText = total > 99 ? '99+' : total;
                badge.className = 'absolute -top-1 -right-1 min-w-[20px] h-5 px-1 rounded-full bg-red-600 text-white text-[10px] font-extrabold flex items-center justify-center ring-2 ring-white animate-pulse';

                if (textoStatus) {
                    textoStatus.innerText = total + ' solicitação(ões) aguardando aprovação.';
                }
            } else {
                badge.innerText = '✓';
                badge.className = 'absolute -top-1 -right-1 min-w-[20px] h-5 px-1 rounded-full bg-green-600 text-white text-[10px] font-extrabold flex items-center justify-center ring-2 ring-white';

                if (textoStatus) {
                    textoStatus.innerText = 'Nenhuma solicitação pendente agora.';
                }
            }
        }

        function montarItemPendenteNavbar(usuario) {
            const nome = usuario.name || 'Usuário sem nome';
            const email = usuario.email || 'Sem e-mail';
            const tipo = usuario.tipo_formatado || 'Usuário';
            const inicial = nome.substring(0, 1).toUpperCase();
            const cpf = usuario.cpf ? `<span class="text-[11px] text-[#60756B] font-semibold">CPF: ${usuario.cpf}</span>` : '';

            return `
                <div class="pendente-navbar-item bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-4 border-l-4 border-l-yellow-500"
                     data-user-id="${usuario.id}">

                    <div class="flex items-start gap-3">

                        <div class="w-11 h-11 rounded-2xl bg-yellow-100 text-yellow-700 flex items-center justify-center font-extrabold shrink-0">
                            ${inicial}
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="font-extrabold text-[#003C2F] text-sm leading-tight break-words">
                                        ${nome}
                                    </p>

                                    <p class="text-xs text-[#60756B] mt-1 break-words">
                                        ${email}
                                    </p>
                                </div>

                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-yellow-100 text-yellow-700 whitespace-nowrap">
                                    PENDENTE
                                </span>
                            </div>

                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <span class="inline-flex bg-[#EAF5EF] text-[#004D3A] px-2.5 py-1 rounded-full text-[10px] font-bold uppercase">
                                    ${tipo}
                                </span>
                                ${cpf}
                            </div>

                            <div class="mt-3 flex flex-col sm:flex-row gap-2">
                                <form method="POST" action="${usuario.aprovar_url || ('/usuarios/' + usuario.id + '/aprovar')}" class="w-full">
                                    <input type="hidden" name="_token" value="${csrfNavbar}">
                                    <button type="submit"
                                            class="w-full bg-[#00A63E] hover:bg-[#008F35] text-white px-3 py-2 rounded-xl text-xs font-extrabold transition">
                                        Aprovar
                                    </button>
                                </form>

                                <form method="POST" action="${usuario.rejeitar_url || ('/usuarios/' + usuario.id + '/rejeitar')}" class="w-full">
                                    <input type="hidden" name="_token" value="${csrfNavbar}">
                                    <button type="submit"
                                            class="w-full bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 px-3 py-2 rounded-xl text-xs font-extrabold transition">
                                        Rejeitar
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>

                </div>
            `;
        }

        function renderizarPendentesNavbar(usuarios) {
            const lista = document.getElementById('listaPendentesNavbar');

            if (!lista) return;

            if (!usuarios || usuarios.length === 0) {
                lista.innerHTML = `
                    <div id="pendentesNavbarVazio" class="p-6 text-center text-[#60756B]">
                        <div class="w-12 h-12 rounded-full bg-[#EAF5EF] text-[#004D3A] mx-auto mb-3 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-6 h-6"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                            </svg>
                        </div>

                        <p class="text-sm font-bold">Nenhum usuário pendente.</p>
                    </div>
                `;
                return;
            }

            lista.innerHTML = usuarios.map(montarItemPendenteNavbar).join('');
        }

        function mostrarToastNovoPendenteNavbar(usuario, totalNovos) {
            let toast = document.getElementById('toastNovoPendenteNavbar');

            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'toastNovoPendenteNavbar';
                toast.className = 'fixed bottom-5 right-5 z-[9999] w-[calc(100vw-40px)] sm:w-[360px] bg-white border border-[#E3EBE4] rounded-3xl shadow-2xl p-4 transition-all duration-300';
                document.body.appendChild(toast);
            }

            const nome = usuario && usuario.name ? usuario.name : 'Novo usuário';
            const textoNovos = totalNovos > 1
                ? `${totalNovos} novos usuários aguardando aprovação.`
                : `${nome} acabou de solicitar acesso.`;

            toast.innerHTML = `
                <div class="flex items-start gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-yellow-100 text-yellow-700 flex items-center justify-center font-extrabold shrink-0">
                        !
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-extrabold text-[#003C2F]">Nova solicitação de acesso</p>
                        <p class="text-xs text-[#60756B] mt-1 leading-relaxed">${textoNovos}</p>

                        <div class="mt-3 flex gap-2">
                            <button type="button"
                                    onclick="toggleDropdownPendentesNavbar()"
                                    class="bg-[#004D3A] text-white px-3 py-2 rounded-xl text-xs font-extrabold hover:bg-[#003C2F] transition">
                                Ver agora
                            </button>

                            <a href="${urlControleUsuariosNavbar}"
                               class="bg-[#F8FBF8] border border-[#DCE7DE] text-[#004D3A] px-3 py-2 rounded-xl text-xs font-extrabold hover:bg-[#EAF5EF] transition">
                                Controle
                            </a>
                        </div>
                    </div>

                    <button type="button" onclick="this.closest('#toastNovoPendenteNavbar').remove()" class="text-[#8A9B92] hover:text-[#003C2F] font-bold">
                        ×
                    </button>
                </div>
            `;

            setTimeout(() => {
                const atual = document.getElementById('toastNovoPendenteNavbar');
                if (atual) atual.remove();
            }, 9000);
        }

        async function verificarPendentesNavbar() {
            try {
                const resposta = await fetch(urlPendentesNavbar, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    cache: 'no-store'
                });

                if (!resposta.ok) return;

                const dados = await resposta.json();
                const total = parseInt(dados.total || 0);
                const usuarios = dados.usuarios || [];
                const novosIds = usuarios.map(usuario => usuario.id);

                const idsNovosDetectados = novosIds.filter(id => !idsPendentesNavbarAtual.includes(id));

                atualizarBadgePendentesNavbar(total);
                renderizarPendentesNavbar(usuarios);

                if (!primeiraVerificacaoPendentes && idsNovosDetectados.length > 0) {
                    const primeiroNovo = usuarios.find(usuario => idsNovosDetectados.includes(usuario.id));
                    mostrarToastNovoPendenteNavbar(primeiroNovo, idsNovosDetectados.length);
                }

                totalPendentesNavbarAtual = total;
                idsPendentesNavbarAtual = novosIds;
                primeiraVerificacaoPendentes = false;
            } catch (erro) {
                console.warn('Não foi possível verificar usuários pendentes agora.', erro);
            }
        }

        function toggleDropdownPendentesNavbar() {
            const dropdown = document.getElementById('dropdownPendentesNavbar');

            if (!dropdown) return;

            dropdown.classList.toggle('hidden');

            const menuPerfil = document.getElementById('menuPerfilNavbar');
            const avisos = document.getElementById('dropdownAvisosNavbar');

            if (menuPerfil) menuPerfil.classList.add('hidden');
            if (avisos) avisos.classList.add('hidden');

            verificarPendentesNavbar();
        }

        document.addEventListener('DOMContentLoaded', function () {
            atualizarBadgePendentesNavbar(totalPendentesNavbarAtual);
            verificarPendentesNavbar();
            setInterval(verificarPendentesNavbar, 10000);
        });
    </script>
@endif

@if($mostrarAvisosAluno)
    <script>
        const avisosNavbarIds = @json($idsAvisosNavbar);
        const alunoNavbarId = "{{ auth()->id() }}";

        function chaveAvisoNavbar(id) {
            return 'aviso_urgente_lido_' + alunoNavbarId + '_' + id;
        }

        function atualizarAvisosNavbar() {
            const badge = document.getElementById('badgeAvisosNavbar');
            const textoStatus = document.getElementById('textoStatusAvisosNavbar');
            const itens = document.querySelectorAll('.aviso-navbar-item');

            if (!badge) return;

            let naoVistos = 0;

            avisosNavbarIds.forEach(id => {
                if (!localStorage.getItem(chaveAvisoNavbar(id))) {
                    naoVistos++;
                }
            });

            if (naoVistos > 0) {
                badge.innerText = naoVistos > 99 ? '99+' : naoVistos;
                badge.className = 'absolute -top-1 -right-1 min-w-[20px] h-5 px-1 rounded-full bg-red-600 text-white text-[10px] font-extrabold flex items-center justify-center ring-2 ring-white';

                if (textoStatus) {
                    textoStatus.innerText = naoVistos + ' aviso(s) ainda não visto(s).';
                }
            } else {
                badge.innerText = '✓';
                badge.className = 'absolute -top-1 -right-1 min-w-[20px] h-5 px-1 rounded-full bg-green-600 text-white text-[10px] font-extrabold flex items-center justify-center ring-2 ring-white';

                if (textoStatus) {
                    textoStatus.innerText = avisosNavbarIds.length > 0
                        ? 'Todos os avisos foram vistos.'
                        : 'Nenhum aviso ativo no momento.';
                }
            }

            itens.forEach(item => {
                const id = item.dataset.avisoId;
                const status = item.querySelector('.status-aviso-navbar');

                if (!status) return;

                if (localStorage.getItem(chaveAvisoNavbar(id))) {
                    status.innerText = 'Visto';
                    status.className = 'status-aviso-navbar text-[10px] font-bold text-green-600';
                    item.classList.add('opacity-75');
                } else {
                    status.innerText = 'Não visto';
                    status.className = 'status-aviso-navbar text-[10px] font-bold text-red-600';
                    item.classList.remove('opacity-75');
                }
            });
        }

        function toggleDropdownAvisosNavbar() {
            const dropdown = document.getElementById('dropdownAvisosNavbar');

            if (!dropdown) return;

            dropdown.classList.toggle('hidden');

            const menuPerfil = document.getElementById('menuPerfilNavbar');

            if (menuPerfil) {
                menuPerfil.classList.add('hidden');
            }

            atualizarAvisosNavbar();
        }

        function marcarTodosAvisosComoVistosNavbar() {
            avisosNavbarIds.forEach(id => {
                localStorage.setItem(chaveAvisoNavbar(id), '1');
            });

            atualizarAvisosNavbar();
        }

        document.addEventListener('DOMContentLoaded', function () {
            atualizarAvisosNavbar();
        });
    </script>
@endif

<script>
    function toggleMenuPerfilNavbar() {
        const menu = document.getElementById('menuPerfilNavbar');
        const avisos = document.getElementById('dropdownAvisosNavbar');
        const pendentes = document.getElementById('dropdownPendentesNavbar');

        if (!menu) return;

        if (avisos) {
            avisos.classList.add('hidden');
        }

        if (pendentes) {
            pendentes.classList.add('hidden');
        }

        menu.classList.toggle('hidden');
    }

    document.addEventListener('click', function (event) {
        const menuPerfil = document.getElementById('menuPerfilNavbar');
        const dropdownAvisos = document.getElementById('dropdownAvisosNavbar');
        const dropdownPendentes = document.getElementById('dropdownPendentesNavbar');

        const clicouNoPerfil = event.target.closest('[onclick="toggleMenuPerfilNavbar()"]');
        const clicouNoAvisos = event.target.closest('[onclick="toggleDropdownAvisosNavbar()"]');
        const clicouNoPendentes = event.target.closest('[onclick="toggleDropdownPendentesNavbar()"]');

        if (menuPerfil && !menuPerfil.contains(event.target) && !clicouNoPerfil) {
            menuPerfil.classList.add('hidden');
        }

        if (dropdownAvisos && !dropdownAvisos.contains(event.target) && !clicouNoAvisos) {
            dropdownAvisos.classList.add('hidden');
        }

        if (dropdownPendentes && !dropdownPendentes.contains(event.target) && !clicouNoPendentes) {
            dropdownPendentes.classList.add('hidden');
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            const menuPerfil = document.getElementById('menuPerfilNavbar');
            const dropdownAvisos = document.getElementById('dropdownAvisosNavbar');
            const dropdownPendentes = document.getElementById('dropdownPendentesNavbar');

            if (menuPerfil) menuPerfil.classList.add('hidden');
            if (dropdownAvisos) dropdownAvisos.classList.add('hidden');
            if (dropdownPendentes) dropdownPendentes.classList.add('hidden');
        }
    });
</script>