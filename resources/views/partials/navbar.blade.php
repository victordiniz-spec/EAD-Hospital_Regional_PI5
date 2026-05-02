<nav class="w-full bg-white/90 backdrop-blur border-b border-[#E3EBE4] px-4 sm:px-6 lg:px-8 py-3 flex justify-end items-center shadow-sm">

    <div class="flex items-center gap-3">

        <!-- NOME E PERFIL -->
        <div class="text-right hidden sm:block">
            <p class="text-sm font-semibold text-[#003C2F] leading-tight">
                {{ auth()->user()->name ?? 'Usuário' }}
            </p>

            <p class="text-xs text-[#6B7C73]">
                Meu Perfil
            </p>
        </div>

        <!-- AVATAR -->
        <div class="w-11 h-11 rounded-full bg-[#00A63E] flex items-center justify-center text-white font-bold shadow-md ring-4 ring-green-100">

            @php
                $nome = trim(auth()->user()->name ?? 'Usuario');
                $partes = preg_split('/\s+/', $nome);

                $primeiraLetra = strtoupper(substr($partes[0] ?? 'U', 0, 1));
                $segundaLetra = '';

                if (count($partes) > 1) {
                    $segundaLetra = strtoupper(substr($partes[count($partes) - 1], 0, 1));
                }

                $iniciais = $primeiraLetra . $segundaLetra;
            @endphp

            {{ $iniciais }}

        </div>

    </div>

</nav>