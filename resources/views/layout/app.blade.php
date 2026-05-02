<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Integrar ReSaúde')</title>

    @vite('resources/css/app.css')
</head>

<body class="text-gray-800 bg-[#F3F7F3] min-h-screen">

    {{-- 
        NAVBAR GLOBAL DESATIVADA
        Motivo: algumas telas, como dashboard do professor/aluno, já possuem navbar/sidebar própria.
        Se quiser usar a navbar em uma tela específica, chame diretamente:
        @include('partials.navbar')
    --}}

    <div class="w-full min-h-screen">

        @yield('content')

    </div>

    {{-- 
        FOOTER GLOBAL OPCIONAL
        Mantido apenas se o arquivo existir e se a tela não desativar layout.
    --}}
    @if(!isset($noLayout) && View::exists('components.footer'))
        @include('components.footer')
    @endif

</body>
</html>