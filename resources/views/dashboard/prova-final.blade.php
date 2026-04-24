@extends('layout.app')

@section('title', 'Prova Final')

@section('content')

<div class="flex min-h-screen">

    @include('partials.sidebar-aluno')

    <main class="flex-1 p-8 bg-[#0B1120] text-white">

        <h2 class="text-2xl font-bold mb-6">
            📝 Prova Final
        </h2>

        @if(session('success'))
            <div class="bg-green-500/20 text-green-400 p-3 mb-4 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(isset($avaliacao))

        <form action="{{ route('prova.final.responder') }}" method="POST">
            @csrf

            <div class="bg-[#1E293B] p-6 rounded-xl mb-6">
                <h3 class="text-lg font-semibold mb-2">
                    {{ $avaliacao->titulo }}
                </h3>

                <p class="text-sm text-gray-400">
                    Tempo: {{ $avaliacao->tempo_limite }} minutos
                </p>
            </div>

            @foreach($avaliacao->perguntas as $index => $pergunta)

                <div class="bg-[#1E293B] p-6 rounded-xl mb-4">

                    <p class="mb-4 font-semibold">
                        {{ $index + 1 }}. {{ $pergunta->pergunta }}
                    </p>

                    @foreach($pergunta->respostas as $resposta)

                        <label class="block mb-2">
                            <input type="radio"
                                   name="respostas[{{ $pergunta->id }}]"
                                   value="{{ $resposta->id }}"
                                   class="mr-2">

                            {{ $resposta->resposta }}
                        </label>

                    @endforeach

                </div>

            @endforeach

            <button class="bg-green-600 px-6 py-3 rounded hover:bg-green-700 transition">
                ✅ Finalizar Prova
            </button>

        </form>

        @else

            <div class="bg-yellow-500/20 text-yellow-400 p-4 rounded">
                Nenhuma prova disponível no momento.
            </div>

        @endif

    </main>

</div>

@endsection