@extends('layout.app')

@section('title', 'Dashboard Professor')

@section('content')

<div class="flex min-h-screen bg-[#0B1120] text-white">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-[#0F172A] p-6">
        <h1 class="text-xl font-bold mb-8">ResidentEAD</h1>

        <nav class="space-y-4">
            <a href="#" class="block bg-blue-600 p-2 rounded">Dashboard</a>
            <a href="#" class="block hover:text-blue-400">Videoaulas</a>
            <a href="#" class="block hover:text-blue-400">Pós-testes</a>
            <a href="#" class="block hover:text-blue-400">Alunos</a>
        </nav>
    </aside>

    <!-- CONTEÚDO -->
    <main class="flex-1 p-8">

        <h2 class="text-2xl font-bold mb-6">Dashboard</h2>

        <!-- CARDS -->
        <div class="grid grid-cols-4 gap-6 mb-8">

            <div class="bg-[#1E293B] p-6 rounded-xl">
                <p>Total de Aulas</p>
                <h3 class="text-3xl font-bold">12</h3>
            </div>

            <div class="bg-[#1E293B] p-6 rounded-xl">
                <p>Alunos Ativos</p>
                <h3 class="text-3xl font-bold">48</h3>
            </div>

            <div class="bg-[#1E293B] p-6 rounded-xl">
                <p>Pós-testes</p>
                <h3 class="text-3xl font-bold">134</h3>
            </div>

            <div class="bg-[#1E293B] p-6 rounded-xl">
                <p>Média Geral</p>
                <h3 class="text-3xl font-bold">7.8</h3>
            </div>

        </div>

        <!-- LISTA -->
        <div class="bg-[#1E293B] p-6 rounded-xl">
            <h3 class="mb-4">Videoaulas Recentes</h3>

            <ul class="space-y-3">
                <li class="flex justify-between">
                    <span>Introdução à Semiologia</span>
                    <span class="text-green-400">Publicada</span>
                </li>

                <li class="flex justify-between">
                    <span>Ausculta Cardíaca</span>
                    <span class="text-green-400">Publicada</span>
                </li>

                <li class="flex justify-between">
                    <span>Neurologia Básica</span>
                    <span class="text-yellow-400">Rascunho</span>
                </li>
            </ul>
        </div>

    </main>

</div>

@endsection