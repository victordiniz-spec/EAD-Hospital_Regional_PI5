<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>

    @vite('resources/css/app.css')
</head>

<body class="text-gray-800">

{{-- NAVBAR --}}
@if(!isset($noLayout))
    @include('components.navbar')
@endif

<div class="w-full min-h-screen flex items-center justify-center">

    @yield('content')

</div>

{{-- FOOTER --}}
@if(!isset($noLayout))
    @include('components.footer')
@endif

</body>
</html>