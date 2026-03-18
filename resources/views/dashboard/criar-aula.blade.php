@extends('layout.app')

@section('title', 'Criar Aula')

@section('content')

<div class="flex min-h-screen bg-[#0B1120] text-white">

    <main class="flex-1 p-8">

        <h2 class="text-2xl font-bold mb-6">Criar Nova Aula</h2>

        <form action="{{ route('aulas.store') }}" method="POST" class="space-y-4">
            @csrf

            <input type="text" name="titulo" placeholder="Título da aula"
                class="w-full p-3 rounded bg-[#1E293B]">

            <textarea name="descricao" placeholder="Descrição"
                class="w-full p-3 rounded bg-[#1E293B]"></textarea>

            <input type="text" name="video_url" placeholder="Link do vídeo"
                class="w-full p-3 rounded bg-[#1E293B]">

            <button class="bg-green-600 px-4 py-2 rounded hover:bg-green-700">
                Salvar Aula
            </button>

        </form>

    </main>

</div>

@endsection