<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>

    @vite('resources/css/app.css')

</head>

<body class="bg-gray-100">

@include('components.navbar')

<div class="container mx-auto mt-10">

    @yield('content')

</div>

@include('components.footer')

</body>
</html>