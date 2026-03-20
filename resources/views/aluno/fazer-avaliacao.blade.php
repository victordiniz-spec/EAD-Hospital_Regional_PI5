@extends('layout.app')

@section('title', 'Fazer Avaliação')

@section('content')

<div class="flex min-h-screen bg-[#0B1120] text-white justify-center items-start p-8">

    <div class="bg-[#1E293B] p-6 rounded-xl w-full max-w-3xl">
        <h2 class="text-2xl font-bold mb-6">{{ $avaliacao->titulo }}</h2>

        <form action="{{ route('avaliacoes.submit', $avaliacao->id) }}" method="POST">
            @csrf

            @foreach($avaliacao->perguntas as $index => $pergunta)
                <div class="mb-6">
                    <p class="font-semibold mb-2">{{ $index + 1 }}. {{ $pergunta->pergunta }}</p>

                    @foreach($pergunta->respostas as $resposta)
                        <label class="block mb-1">
                            <input type="radio" name="resposta[{{ $pergunta->id }}]" value="{{ $resposta->id }}" class="mr-2">
                            {{ $resposta->resposta }}
                        </label>
                    @endforeach
                </div>
            @endforeach

            <button type="submit" class="bg-blue-600 px-4 py-2 rounded">Enviar Respostas</button>
        </form>
    </div>

</div>

@endsection