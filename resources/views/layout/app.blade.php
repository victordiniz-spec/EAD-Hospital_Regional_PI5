<!DOCTYPE html>
<html lang="pt-br" class="bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>

    @vite('resources/css/app.css')
</head>

<body class="bg-slate-950 text-white">

@include('components.navbar')

<div class="w-full min-h-screen flex items-center justify-center">

    @yield('content')

</div>

@include('components.footer')

</body>
</html>