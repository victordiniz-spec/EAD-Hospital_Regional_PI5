<!DOCTYPE html>
<html lang="pt-br" class="theme-light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Integrar ReSaúde')</title>

    {{-- Aplica o tema antes da tela carregar para não piscar --}}
    <script>
        (function () {
            const temaSalvo = localStorage.getItem('tema_sistema');

            if (temaSalvo === 'dark') {
                document.documentElement.classList.add('dark');
                document.documentElement.classList.remove('theme-light');
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.add('theme-light');
            }
        })();
    </script>

    @vite('resources/css/app.css')

    <style>
        html,
        body {
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

        /*
        |--------------------------------------------------------------------------
        | FUNDOS CLAROS DO SISTEMA
        |--------------------------------------------------------------------------
        */
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

        /*
        |--------------------------------------------------------------------------
        | SIDEBAR PROFESSOR / ALUNO
        |--------------------------------------------------------------------------
        | Esse bloco força a sidebar a escurecer mesmo se ela estiver com bg-gray-100,
        | bg-white ou classes fixas.
        */
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

        html.dark aside .hover\:bg-gray-200:hover {
            background-color: #111C2E !important;
        }

        html.dark aside .text-gray-700,
        html.dark aside .text-gray-800,
        html.dark aside .text-gray-600,
        html.dark aside .text-gray-500 {
            color: #CBD5E1 !important;
        }

        /*
        |--------------------------------------------------------------------------
        | CARDS, ALERTAS E CORES DE STATUS
        |--------------------------------------------------------------------------
        */
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

        /*
        |--------------------------------------------------------------------------
        | TEXTOS
        |--------------------------------------------------------------------------
        */
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

        /*
        |--------------------------------------------------------------------------
        | BORDAS
        |--------------------------------------------------------------------------
        */
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

        /*
        |--------------------------------------------------------------------------
        | FORMULÁRIOS
        |--------------------------------------------------------------------------
        */
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

        /*
        |--------------------------------------------------------------------------
        | TABELAS
        |--------------------------------------------------------------------------
        */
        html.dark table thead tr {
            background-color: #0B1220 !important;
        }

        html.dark table tbody tr:hover {
            background-color: #111C2E !important;
        }

        /*
        |--------------------------------------------------------------------------
        | SOMBRAS E EFEITOS
        |--------------------------------------------------------------------------
        */
        html.dark .shadow-sm,
        html.dark .shadow-md,
        html.dark .shadow-lg,
        html.dark .shadow-xl,
        html.dark .shadow-2xl {
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.38) !important;
        }

        html.dark .backdrop-blur {
            backdrop-filter: blur(14px);
        }

        html.dark .ring-green-100 {
            --tw-ring-color: rgba(34, 197, 94, 0.25) !important;
        }

        html.dark .dark-card {
            background: #101827 !important;
            border-color: #243044 !important;
        }

        /*
        |--------------------------------------------------------------------------
        | SCROLLBAR NO MODO ESCURO
        |--------------------------------------------------------------------------
        */
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
    </style>
</head>

<body class="text-gray-800 bg-[#F3F7F3] min-h-screen">

    <div id="app" class="w-full min-h-screen">

        @yield('content')

    </div>

    @if(!isset($noLayout) && View::exists('components.footer'))
        @include('components.footer')
    @endif

    <script>
        function aplicarTemaSistema(tema) {
            if (tema === 'dark') {
                document.documentElement.classList.add('dark');
                document.documentElement.classList.remove('theme-light');
                localStorage.setItem('tema_sistema', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.add('theme-light');
                localStorage.setItem('tema_sistema', 'light');
            }

            atualizarIconeTemaSistema();
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

        document.addEventListener('DOMContentLoaded', function () {
            atualizarIconeTemaSistema();
        });
    </script>

</body>
</html>