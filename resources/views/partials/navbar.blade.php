<nav class="w-full bg-white/90 backdrop-blur border-b border-[#E3EBE4] px-4 sm:px-6 lg:px-8 py-3 flex justify-end items-center shadow-sm relative z-50">

    <div class="flex items-center gap-3">

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
                'admin' => 'Administrador',
                default => ucfirst($tipoUsuario),
            };

            /*
            |--------------------------------------------------------------------------
            | AVISOS SOMENTE PARA ALUNO
            |--------------------------------------------------------------------------
            | Aqui deixamos aparecer para residente e preceptor.
            | Não aparece para admin/professor.
            */
            $mostrarAvisosAluno = in_array($tipoUsuario, ['residente', 'preceptor']);

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
        @endphp

        @if($mostrarAvisosAluno)
            <!-- NOTIFICAÇÕES DE AVISOS -->
            <div class="relative">

                <button type="button"
                        onclick="toggleDropdownAvisosNavbar()"
                        class="relative w-11 h-11 rounded-full bg-[#F8FBF8] border border-[#DCE7DE] flex items-center justify-center text-[#004D3A] hover:bg-[#EAF5EF] transition shadow-sm"
                        title="Avisos">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-6 h-6"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="1.8"
                              d="M14.857 17.082a23.848 23.848 0 0 1-5.714 0M18 8a6 6 0 1 0-12 0c0 7-3 7-3 7h18s-3 0-3-7"/>
                    </svg>

                    <!-- BOLINHA -->
                    <span id="badgeAvisosNavbar"
                          class="absolute -top-1 -right-1 min-w-[20px] h-5 px-1 rounded-full bg-red-600 text-white text-[10px] font-extrabold flex items-center justify-center ring-2 ring-white">
                        0
                    </span>
                </button>

                <!-- DROPDOWN -->
                <div id="dropdownAvisosNavbar"
                     class="hidden absolute right-0 mt-3 w-[340px] sm:w-[390px] bg-white border border-[#E3EBE4] rounded-3xl shadow-2xl overflow-hidden z-[999]">

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
        <div class="text-right hidden sm:block">
            <p class="text-sm font-semibold text-[#003C2F] leading-tight">
                {{ $nome }}
            </p>

            <p class="text-xs text-[#6B7C73]">
                {{ $tipoFormatado }}
            </p>
        </div>

        <!-- AVATAR -->
        <div class="w-11 h-11 rounded-full bg-[#00A63E] flex items-center justify-center text-white font-bold shadow-md ring-4 ring-green-100">
            {{ $iniciais }}
        </div>

    </div>

</nav>

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

        document.addEventListener('click', function (event) {
            const dropdown = document.getElementById('dropdownAvisosNavbar');
            const badge = document.getElementById('badgeAvisosNavbar');

            if (!dropdown || !badge) return;

            const clicouDentroDropdown = dropdown.contains(event.target);
            const clicouNoBotao = event.target.closest('button') && event.target.closest('button').contains(badge);

            if (!clicouDentroDropdown && !event.target.closest('[onclick="toggleDropdownAvisosNavbar()"]')) {
                dropdown.classList.add('hidden');
            }
        });
    </script>
@endif