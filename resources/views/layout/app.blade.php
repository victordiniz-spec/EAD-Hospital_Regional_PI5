<!DOCTYPE html>
<html lang="pt-br" class="theme-light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Integrar ReSaúde')</title>

    @php
        /*
        |--------------------------------------------------------------------------
        | PÁGINAS SEM MODO ESCURO
        |--------------------------------------------------------------------------
        | Login, cadastro e recuperação de senha devem permanecer sempre claros,
        | mesmo que o usuário tenha ativado modo escuro dentro do sistema.
        */
        $paginaSemModoEscuro = request()->routeIs(
            'login',
            'cadastro.*',
            'salvar.aluno',
            'senha.*'
        ) || request()->is(
            '/',
            'cadastro-aluno',
            'salvar-aluno',
            'verificar-email-cadastro',
            'reenviar-codigo-cadastro',
            'esqueci-minha-senha',
            'redefinir-senha',
            'reenviar-codigo-senha'
        );
    @endphp

    {{-- Aplica o tema antes da tela carregar para não piscar --}}
    <script>
        (function () {
            const paginaSemModoEscuro = @json($paginaSemModoEscuro);

            /*
            |--------------------------------------------------------------------------
            | LOGIN / CADASTRO SEMPRE CLARO
            |--------------------------------------------------------------------------
            | Não apaga a preferência salva. Apenas impede que o dark seja aplicado
            | nessas telas públicas.
            */
            if (paginaSemModoEscuro) {
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.add('theme-light', 'sem-modo-escuro');
                return;
            }

            const temaSalvo = localStorage.getItem('tema_sistema');

            if (temaSalvo === 'dark') {
                document.documentElement.classList.add('dark');
                document.documentElement.classList.remove('theme-light', 'sem-modo-escuro');
            } else {
                document.documentElement.classList.remove('dark', 'sem-modo-escuro');
                document.documentElement.classList.add('theme-light');
            }
        })();
    </script>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite('resources/css/app.css')

    <style>
        html,
        body {
            font-family: 'Outfit', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-weight: 400;
            letter-spacing: -0.01em;
            transition: background-color 0.25s ease, color 0.25s ease;
        }

        html.dark body {
            background: #070B14 !important;
            color: #E5E7EB !important;
        }

        html.dark #app,
        html.dark main,
        html.dark section {
            background: #070B14 !important;
            color: #E5E7EB !important;
        }

        html.dark .bg-\[\#F3F7F3\],
        html.dark .bg-\[\#F4F7F3\],
        html.dark .bg-\[\#F8FBF8\],
        html.dark .bg-\[\#F1F6F2\],
        html.dark .bg-\[\#EAF5EF\],
        html.dark .bg-\[\#E7F0EC\],
        html.dark .bg-\[\#F9FBFA\],
        html.dark .bg-white,
        html.dark .bg-gray-50,
        html.dark .bg-gray-100 {
            background-color: #101827 !important;
        }

        html.dark .bg-white\/90 {
            background-color: rgba(16, 24, 39, 0.94) !important;
        }

        html.dark aside {
            background: #0B1220 !important;
            color: #E5E7EB !important;
            border-color: #243044 !important;
        }

        html.dark aside * {
            border-color: #243044 !important;
        }

        html.dark aside h1,
        html.dark aside h2,
        html.dark aside h3,
        html.dark aside p,
        html.dark aside span {
            color: #E5E7EB !important;
            letter-spacing: -0.025em;
        }

        html.dark aside a {
            color: #CBD5E1 !important;
        }

        html.dark aside a:hover {
            background: #111C2E !important;
            color: #FFFFFF !important;
        }

        html.dark aside a.bg-green-600,
        html.dark aside .bg-green-600,
        html.dark aside a[class*="bg-green"],
        html.dark aside .bg-\[\#004D3A\],
        html.dark aside .bg-\[\#00A63E\] {
            background: #00A63E !important;
            color: #FFFFFF !important;
        }

        html.dark .bg-green-100,
        html.dark .bg-green-50 {
            background-color: rgba(22, 101, 52, 0.22) !important;
        }

        html.dark .bg-red-100,
        html.dark .bg-red-50 {
            background-color: rgba(153, 27, 27, 0.22) !important;
        }

        html.dark .bg-yellow-100,
        html.dark .bg-yellow-50 {
            background-color: rgba(161, 98, 7, 0.22) !important;
        }

        html.dark .bg-blue-100,
        html.dark .bg-blue-50 {
            background-color: rgba(30, 64, 175, 0.22) !important;
        }

        html.dark .text-\[\#003C2F\],
        html.dark .text-\[\#0B3B2E\],
        html.dark .text-\[\#1F2A24\],
        html.dark .text-\[\#15392D\],
        html.dark .text-\[\#16372C\],
        html.dark .text-gray-800 {
            color: #F8FAFC !important;
        }

        html.dark .text-\[\#60756B\],
        html.dark .text-\[\#6B7C73\],
        html.dark .text-\[\#6D8077\],
        html.dark .text-\[\#6F8177\],
        html.dark .text-\[\#70847A\],
        html.dark .text-\[\#75867D\],
        html.dark .text-\[\#8A9B92\],
        html.dark .text-gray-500,
        html.dark .text-gray-600,
        html.dark .text-gray-700 {
            color: #AAB7C4 !important;
        }

        html.dark .border-\[\#E3EBE4\],
        html.dark .border-\[\#DCE7DE\],
        html.dark .border-\[\#D8E3DD\],
        html.dark .border-\[\#D7E6DE\],
        html.dark .border-\[\#D9E2DD\],
        html.dark .border-gray-100,
        html.dark .border-gray-200,
        html.dark .border-gray-300,
        html.dark .border-gray-700 {
            border-color: #243044 !important;
        }

        html.dark input,
        html.dark select,
        html.dark textarea {
            background-color: #0B1220 !important;
            color: #F8FAFC !important;
            border-color: #243044 !important;
        }

        html.dark input::placeholder,
        html.dark textarea::placeholder {
            color: #718096 !important;
        }

        html.dark table thead tr {
            background-color: #0B1220 !important;
        }

        html.dark table tbody tr:hover {
            background-color: #111C2E !important;
        }

        html.dark .shadow-sm,
        html.dark .shadow-md,
        html.dark .shadow-lg,
        html.dark .shadow-xl,
        html.dark .shadow-2xl {
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.38) !important;
        }

        html.dark .ring-green-100 {
            --tw-ring-color: rgba(34, 197, 94, 0.25) !important;
        }

        html.dark ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        html.dark ::-webkit-scrollbar-track {
            background: #070B14;
        }

        html.dark ::-webkit-scrollbar-thumb {
            background: #243044;
            border-radius: 999px;
        }

        html.dark ::-webkit-scrollbar-thumb:hover {
            background: #334155;
        }

        /*
        |--------------------------------------------------------------------------
        | CORREÇÕES DO MODO ESCURO
        |--------------------------------------------------------------------------
        | Evita texto branco em fundo branco/claro ao passar o mouse.
        */

        html.sem-modo-escuro,
        html.sem-modo-escuro body,
        html.sem-modo-escuro #app,
        html.sem-modo-escuro main,
        html.sem-modo-escuro section {
            background: #F3F7F3 !important;
            color: #1F2937 !important;
        }

        html.sem-modo-escuro .dark {
            color-scheme: light !important;
        }

        html.dark a:hover,
        html.dark button:hover {
            color: inherit;
        }

        html.dark .hover\:bg-white:hover,
        html.dark .hover\:bg-gray-50:hover,
        html.dark .hover\:bg-gray-100:hover,
        html.dark .hover\:bg-\[\#F8FBF8\]:hover,
        html.dark .hover\:bg-\[\#F1F6F2\]:hover,
        html.dark .hover\:bg-\[\#EAF5EF\]:hover,
        html.dark .hover\:bg-\[\#DCE7DE\]:hover {
            background-color: #111C2E !important;
            color: #F8FAFC !important;
        }

        html.dark .hover\:text-\[\#003C2F\]:hover,
        html.dark .hover\:text-\[\#004D3A\]:hover,
        html.dark .hover\:text-gray-700:hover,
        html.dark .hover\:text-gray-800:hover,
        html.dark .hover\:text-gray-900:hover {
            color: #F8FAFC !important;
        }

        html.dark .group:hover .group-hover\:text-\[\#003C2F\],
        html.dark .group:hover .group-hover\:text-\[\#004D3A\],
        html.dark .group:hover .group-hover\:text-gray-800,
        html.dark .group:hover .group-hover\:text-gray-900 {
            color: #F8FAFC !important;
        }

        html.dark .hover\:border-\[\#00A63E\]\/40:hover,
        html.dark .hover\:border-\[\#005543\]\/50:hover,
        html.dark .hover\:border-blue-500\/40:hover,
        html.dark .hover\:border-blue-500\/50:hover {
            border-color: #00A63E !important;
        }

        html.dark .bg-white:hover,
        html.dark .bg-\[\#F8FBF8\]:hover,
        html.dark .bg-\[\#F1F6F2\]:hover,
        html.dark .bg-\[\#EAF5EF\]:hover {
            color: #F8FAFC !important;
        }

        html.dark .bg-white:hover *,
        html.dark .bg-\[\#F8FBF8\]:hover *,
        html.dark .bg-\[\#F1F6F2\]:hover *,
        html.dark .bg-\[\#EAF5EF\]:hover * {
            color: inherit;
        }

        html.dark input[type="radio"],
        html.dark input[type="checkbox"] {
            accent-color: #00A63E !important;
        }

        /*
        |--------------------------------------------------------------------------
        | TOAST GLOBAL
        |--------------------------------------------------------------------------
        */
        .toast-global {
            animation: toastEntrar 0.35s ease forwards;
        }

        .toast-global.saindo {
            animation: toastSair 0.25s ease forwards;
        }

        @keyframes toastEntrar {
            from {
                opacity: 0;
                transform: translateY(18px) scale(0.96);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes toastSair {
            from {
                opacity: 1;
                transform: translateY(0) scale(1);
            }

            to {
                opacity: 0;
                transform: translateY(18px) scale(0.96);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | BOTÃO VOLTAR AO TOPO
        |--------------------------------------------------------------------------
        */
        #btnVoltarTopo {
            opacity: 0;
            transform: translateY(18px) scale(0.92);
            pointer-events: none;
        }

        #btnVoltarTopo.btn-topo-visivel {
            opacity: 1;
            transform: translateY(0) scale(1);
            pointer-events: auto;
        }

        html.dark #btnVoltarTopo {
            background: #00A63E !important;
            color: #FFFFFF !important;
            border-color: rgba(255, 255, 255, 0.18) !important;
        }

        html.dark #btnVoltarTopo:hover {
            background: #008A35 !important;
        }

    </style>
</head>

<body class="text-gray-800 bg-[#F3F7F3] min-h-screen font-['Outfit'] antialiased">

    <div id="app" class="w-full min-h-screen">

        @yield('content')

    </div>

    @if(!isset($noLayout) && View::exists('components.footer'))
        @include('components.footer')
    @endif

    {{-- CONTAINER DOS TOASTS --}}
    <div id="toastContainer"
         class="fixed bottom-5 right-5 z-[9999] flex flex-col gap-3 w-[calc(100%-40px)] sm:w-[390px] pointer-events-none">
    </div>

    {{-- BOTÃO VOLTAR AO TOPO --}}
    <button
        type="button"
        id="btnVoltarTopo"
        onclick="voltarAoTopo()"
        class="
            fixed right-5 bottom-24 sm:bottom-5 z-[9998]
            w-12 h-12 rounded-full
            bg-[#004D3A] text-white
            shadow-2xl border border-white/30
            flex items-center justify-center
            hover:bg-[#003C2F] hover:scale-105
            transition-all duration-300
        "
        aria-label="Voltar ao topo"
        title="Voltar ao topo"
    >
        <svg xmlns="http://www.w3.org/2000/svg"
             class="w-6 h-6"
             fill="none"
             viewBox="0 0 24 24"
             stroke="currentColor">
            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M5 15l7-7 7 7"/>
        </svg>
    </button>


    @php
        $toasts = [];

        if (session('success')) {
            $toasts[] = [
                'tipo' => 'success',
                'titulo' => 'Tudo certo!',
                'mensagem' => session('success'),
            ];
        }

        if (session('error')) {
            $toasts[] = [
                'tipo' => 'error',
                'titulo' => 'Atenção!',
                'mensagem' => session('error'),
            ];
        }

        if (session('erro')) {
            $toasts[] = [
                'tipo' => 'error',
                'titulo' => 'Atenção!',
                'mensagem' => session('erro'),
            ];
        }

        if (session('warning')) {
            $toasts[] = [
                'tipo' => 'warning',
                'titulo' => 'Aviso!',
                'mensagem' => session('warning'),
            ];
        }

        if (session('info')) {
            $toasts[] = [
                'tipo' => 'info',
                'titulo' => 'Informação',
                'mensagem' => session('info'),
            ];
        }

        if ($errors->any()) {
            foreach ($errors->all() as $erro) {
                $toasts[] = [
                    'tipo' => 'error',
                    'titulo' => 'Corrija este campo',
                    'mensagem' => $erro,
                ];
            }
        }
    @endphp

    <script>
        const paginaSemModoEscuroSistema = @json($paginaSemModoEscuro);

        function aplicarTemaSistema(tema) {
            /*
            |--------------------------------------------------------------------------
            | LOGIN / CADASTRO
            |--------------------------------------------------------------------------
            | Nestas telas, o modo escuro não é aplicado visualmente, mas a preferência
            | do usuário continua salva para quando ele entrar no sistema.
            */
            if (paginaSemModoEscuroSistema) {
                localStorage.setItem('tema_sistema', tema === 'dark' ? 'dark' : 'light');

                document.documentElement.classList.remove('dark');
                document.documentElement.classList.add('theme-light', 'sem-modo-escuro');

                atualizarIconeTemaSistema();
                atualizarBotaoVoltarTopo();

                return;
            }

            if (tema === 'dark') {
                document.documentElement.classList.add('dark');
                document.documentElement.classList.remove('theme-light', 'sem-modo-escuro');
                localStorage.setItem('tema_sistema', 'dark');
            } else {
                document.documentElement.classList.remove('dark', 'sem-modo-escuro');
                document.documentElement.classList.add('theme-light');
                localStorage.setItem('tema_sistema', 'light');
            }

            atualizarIconeTemaSistema();
            atualizarBotaoVoltarTopo();
        }

        function alternarTemaSistema() {
            const estaEscuro = document.documentElement.classList.contains('dark');
            aplicarTemaSistema(estaEscuro ? 'light' : 'dark');
        }

        function atualizarIconeTemaSistema() {
            const estaEscuro = document.documentElement.classList.contains('dark');

            document.querySelectorAll('[data-tema-texto]').forEach((el) => {
                el.innerText = estaEscuro ? 'Modo claro' : 'Modo escuro';
            });

            document.querySelectorAll('[data-tema-icone-sol]').forEach((el) => {
                el.classList.toggle('hidden', !estaEscuro);
            });

            document.querySelectorAll('[data-tema-icone-lua]').forEach((el) => {
                el.classList.toggle('hidden', estaEscuro);
            });
        }

        function criarToast(tipo, titulo, mensagem) {
            const container = document.getElementById('toastContainer');

            if (!container) return;

            const toast = document.createElement('div');

            let estilos = {
                borda: 'border-green-200',
                fundoIcone: 'bg-green-100',
                textoIcone: 'text-green-700',
                barra: 'bg-green-600',
                icone: '✓'
            };

            if (tipo === 'error') {
                estilos = {
                    borda: 'border-red-200',
                    fundoIcone: 'bg-red-100',
                    textoIcone: 'text-red-700',
                    barra: 'bg-red-600',
                    icone: '!'
                };
            }

            if (tipo === 'warning') {
                estilos = {
                    borda: 'border-yellow-200',
                    fundoIcone: 'bg-yellow-100',
                    textoIcone: 'text-yellow-700',
                    barra: 'bg-yellow-500',
                    icone: '!'
                };
            }

            if (tipo === 'info') {
                estilos = {
                    borda: 'border-blue-200',
                    fundoIcone: 'bg-blue-100',
                    textoIcone: 'text-blue-700',
                    barra: 'bg-blue-600',
                    icone: 'i'
                };
            }

            toast.className = `
                toast-global pointer-events-auto relative overflow-hidden
                bg-white border ${estilos.borda}
                rounded-3xl shadow-2xl p-4
            `;

            toast.innerHTML = `
                <div class="flex items-start gap-3 pr-8">
                    <div class="w-11 h-11 rounded-2xl ${estilos.fundoIcone} ${estilos.textoIcone} flex items-center justify-center font-extrabold shrink-0">
                        ${estilos.icone}
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-extrabold text-[#003C2F] leading-tight">
                            ${titulo}
                        </p>

                        <p class="text-sm text-[#60756B] mt-1 leading-relaxed break-words">
                            ${mensagem}
                        </p>
                    </div>

                    <button type="button"
                            class="absolute top-3 right-3 w-8 h-8 rounded-xl flex items-center justify-center text-[#8A9B92] hover:bg-[#F1F6F2] hover:text-[#003C2F] transition"
                            onclick="fecharToast(this)">
                        ×
                    </button>
                </div>

                <div class="absolute bottom-0 left-0 h-1 ${estilos.barra} toast-barra"
                     style="width: 100%; animation: toastBarra 4s linear forwards;">
                </div>
            `;

            container.appendChild(toast);

            setTimeout(() => {
                removerToast(toast);
            }, 4200);
        }

        function removerToast(toast) {
            if (!toast) return;

            toast.classList.add('saindo');

            setTimeout(() => {
                toast.remove();
            }, 280);
        }

        function fecharToast(botao) {
            const toast = botao.closest('.toast-global');
            removerToast(toast);
        }

        const styleToastBarra = document.createElement('style');
        styleToastBarra.innerHTML = `
            @keyframes toastBarra {
                from { width: 100%; }
                to { width: 0%; }
            }

            html.dark .toast-global {
                background: #101827 !important;
                border-color: #243044 !important;
            }

            html.dark .toast-global p:first-child {
                color: #F8FAFC !important;
            }

            html.dark .toast-global p {
                color: #AAB7C4 !important;
            }
        `;
        document.head.appendChild(styleToastBarra);


        /*
        |--------------------------------------------------------------------------
        | BOTÃO VOLTAR AO TOPO
        |--------------------------------------------------------------------------
        */
        function atualizarBotaoVoltarTopo() {
            const botao = document.getElementById('btnVoltarTopo');

            if (!botao) return;

            const scrollAtual = window.scrollY || document.documentElement.scrollTop || 0;

            if (scrollAtual > 350) {
                botao.classList.add('btn-topo-visivel');
            } else {
                botao.classList.remove('btn-topo-visivel');
            }
        }

        function voltarAoTopo() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        window.addEventListener('scroll', atualizarBotaoVoltarTopo, { passive: true });

        document.addEventListener('DOMContentLoaded', function () {
            atualizarIconeTemaSistema();

            const toasts = @json($toasts);

            toasts.forEach((toast, index) => {
                setTimeout(() => {
                    criarToast(toast.tipo, toast.titulo, toast.mensagem);
                }, index * 250);
            });
        });
    </script>

</body>
</html>