@extends('layout.app')

@section('title', 'Pós-teste')

@section('content')

<div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-white">

    <div class="max-w-4xl mx-auto px-4 py-8">

        <!-- Cabeçalho -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 mb-6 shadow">
            <h1 class="text-2xl font-bold mb-2">
                📝 {{ $avaliacao->titulo ?? 'Pós-teste' }}
            </h1>

            <p class="text-slate-400 text-sm">
                Responda as perguntas abaixo e clique em finalizar.
            </p>

            @if(isset($avaliacao->tempo_limite) && $avaliacao->tempo_limite)
                <p class="text-sm text-blue-400 mt-2">
                    Tempo limite: {{ $avaliacao->tempo_limite }} minutos
                </p>
            @endif
        </div>

        <!-- Alertas -->
        @if(session('success'))
            <div class="mb-4 bg-green-500/20 text-green-400 border border-green-500 p-4 rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-500/20 text-red-400 border border-red-500 p-4 rounded-xl">
                {{ session('error') }}
            </div>
        @endif

        @if($perguntas->count() > 0)

            <form method="POST" action="{{ route('avaliacoes.submit', $avaliacao->id) }}">
                @csrf

                <div class="space-y-5">

                    @foreach($perguntas as $index => $pergunta)

                        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow">

                            <h2 class="font-semibold mb-4">
                                {{ $index + 1 }}. {{ $pergunta->pergunta }}
                            </h2>

                            <div class="space-y-3">

                                @forelse($pergunta->respostas as $resposta)

                                    <label class="flex items-center gap-3 bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 cursor-pointer hover:bg-slate-700 transition">

                                        <input
                                            type="radio"
                                            name="respostas[{{ $pergunta->id }}]"
                                            value="{{ $resposta->id }}"
                                            required
                                            class="accent-blue-600"
                                        >

                                        <span class="text-sm">
                                            {{ $resposta->resposta }}
                                        </span>

                                    </label>

                                @empty

                                    <p class="text-red-400 text-sm">
                                        Nenhuma alternativa cadastrada para esta pergunta.
                                    </p>

                                @endforelse

                            </div>

                        </div>

                    @endforeach

                </div>

                <div class="flex justify-between items-center mt-8">

                    <a href="{{ route('dashboard.aluno') }}"
                       class="bg-slate-700 hover:bg-slate-600 px-5 py-3 rounded-lg transition">
                        Voltar
                    </a>

                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded-lg font-semibold transition">
                        Finalizar pós-teste
                    </button>

                </div>

            </form>

        @else

            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 text-center">
                <h2 class="text-xl font-bold mb-2">Nenhuma pergunta encontrada</h2>

                <p class="text-slate-400 mb-6">
                    Este pós-teste ainda não possui perguntas cadastradas.
                </p>

                <a href="{{ route('dashboard.aluno') }}"
                   class="bg-blue-600 hover:bg-blue-700 px-5 py-3 rounded-lg transition">
                    Voltar para o dashboard
                </a>
            </div>

        @endif

    </div>

</div>

@endsection