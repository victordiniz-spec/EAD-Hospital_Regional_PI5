<!DOCTYPE html>
<html lang="pt-br" class="bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>

    @vite('resources/css/app.css')
</head>

<body class="bg-slate-950 text-white min-h-screen flex flex-col">

@include('components.navbar')

<!-- CONTEÚDO -->
<main class="flex-1 flex items-center justify-center px-4">
    @yield('content')
</main>

@include('components.footer')

</body>
</html>