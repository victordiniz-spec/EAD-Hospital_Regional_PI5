@extends('layout.app')

@section('title', 'Avisos')

@section('content')

<div class="flex min-h-screen">

    @include('partials.sidebar-professor')

    <!-- CONTEÚDO -->
    <main class="flex-1 p-8 bg-[#0B1120] text-white">

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Avisos</h2>

            <span class="text-sm text-gray-400">
                Gerencie todos os avisos da plataforma
            </span>
        </div>

        <!-- FORM DE CRIAÇÃO -->
        <div class="bg-[#1E293B] p-6 rounded-xl mb-8 shadow-md">

            <h3 class="font-semibold mb-4">Criar Novo Aviso</h3>

            <form method="POST" action="{{ route('avisos.store') }}">
                @csrf

                <input type="text" name="titulo" placeholder="Título"
                    class="w-full mb-3 p-3 rounded-lg bg-[#0F172A] border border-gray-700">

                <select name="categoria"
                    class="w-full mb-3 p-3 rounded-lg bg-[#0F172A] border border-gray-700">
                    <option value="urgente">Urgente</option>
                    <option value="informativo">Informativo</option>
                </select>

                <textarea name="mensagem" placeholder="Mensagem"
                    class="w-full mb-4 p-3 rounded-lg bg-[#0F172A] border border-gray-700"></textarea>

                <button class="bg-green-600 px-5 py-2 rounded hover:bg-green-700">
                    Salvar Aviso
                </button>

            </form>
        </div>

        <!-- LISTA DE AVISOS (igual HOME 🔥) -->
        <div class="bg-[#1E293B] p-6 rounded-xl shadow-md">

            <h3 class="font-bold mb-4">Avisos Recentes</h3>

            <div class="space-y-4">

                @forelse($avisos as $aviso)

                    <div class="bg-[#0F172A] p-4 rounded-lg border-l-4 border-green-500 flex justify-between items-center">

                        <div>
                            <p class="font-semibold">{{ $aviso->titulo }}</p>
                            <p class="text-sm text-gray-400">{{ $aviso->mensagem }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $aviso->created_at }}
                            </p>
                        </div>

                        <div class="flex gap-3">

                            <!-- EDITAR -->
                            <button onclick="editarAviso(
                                {{ $aviso->id }},
                                '{{ $aviso->titulo }}',
                                '{{ $aviso->mensagem }}',
                                '{{ $aviso->categoria }}'
                            )">
                                ✏️
                            </button>

                            <!-- EXCLUIR -->
                            <form method="POST" action="{{ route('avisos.destroy', $aviso->id) }}">
                                @csrf
                                @method('DELETE')

                                <button>🗑️</button>
                            </form>

                        </div>

                    </div>

                @empty
                    <p class="text-gray-400">Nenhum aviso encontrado</p>
                @endforelse

            </div>

        </div>

    </main>

</div>

@endsection