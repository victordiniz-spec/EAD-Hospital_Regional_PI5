@extends('layout.app')

@section('title', 'Pós-teste')

@section('content')

@php
    $tempoLimite = isset($avaliacao->tempo_limite) && $avaliacao->tempo_limite
        ? (int) $avaliacao->tempo_limite
        : 0;
@endphp

<div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-white">

    <div class="max-w-5xl mx-auto px-4 py-8">

        <!-- TOPO -->
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            <div>
                <button type="button"
                        onclick="confirmarSaidaBonito()"
                        class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white transition mb-4">
                    ← Voltar para o dashboard
                </button>

                <h1 class="text-3xl font-bold">
                    📝 {{ $avaliacao->titulo ?? 'Pós-teste' }}
                </h1>

                <p class="text-slate-400 text-sm mt-2">
                    Responda todas as questões e clique em finalizar.
                </p>
            </div>

            <!-- CONTADOR -->
            @if($tempoLimite > 0)
                <div class="bg-slate-900 border border-blue-500/40 rounded-2xl px-6 py-4 shadow-lg min-w-[220px]">
                    <p class="text-xs uppercase tracking-widest text-slate-400 mb-1">
                        Tempo restante
                    </p>

                    <div id="contador"
                         class="text-3xl font-bold text-blue-400">
                        --:--
                    </div>

                    <p class="text-xs text-slate-500 mt-1">
                        Limite: {{ $tempoLimite }} minuto(s)
                    </p>
                </div>
            @else
                <div class="bg-slate-900 border border-slate-700 rounded-2xl px-6 py-4 shadow-lg min-w-[220px]">
                    <p class="text-xs uppercase tracking-widest text-slate-400 mb-1">
                        Tempo
                    </p>

                    <div class="text-xl font-bold text-emerald-400">
                        Sem limite
                    </div>
                </div>
            @endif

        </div>

        <!-- CARD INFORMATIVO -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 mb-6 shadow">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <div class="bg-slate-800/70 rounded-xl p-4 border border-slate-700">
                    <p class="text-xs text-slate-400 uppercase tracking-widest">Questões</p>
                    <h3 class="text-2xl font-bold mt-1">{{ $perguntas->count() }}</h3>
                </div>

                <div class="bg-slate-800/70 rounded-xl p-4 border border-slate-700">
                    <p class="text-xs text-slate-400 uppercase tracking-widest">Tipo</p>
                    <h3 class="text-2xl font-bold mt-1">Pós-teste</h3>
                </div>

                <div class="bg-slate-800/70 rounded-xl p-4 border border-slate-700">
                    <p class="text-xs text-slate-400 uppercase tracking-widest">Status</p>
                    <h3 class="text-2xl font-bold mt-1 text-yellow-400">Em andamento</h3>
                </div>

            </div>
        </div>

        <!-- ALERTAS -->
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

            <form method="POST"
                  action="{{ route('avaliacoes.submit', $avaliacao->id) }}"
                  id="formPosTeste">
                @csrf

                <div class="space-y-5">

                    @foreach($perguntas as $index => $pergunta)

                        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-lg hover:border-blue-500/40 transition">

                            <div class="flex items-start gap-4 mb-5">

                                <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center font-bold shrink-0">
                                    {{ $index + 1 }}
                                </div>

                                <div>
                                    <p class="text-xs uppercase tracking-widest text-slate-500 mb-1">
                                        Questão {{ $index + 1 }}
                                    </p>

                                    <h2 class="font-semibold text-lg leading-relaxed">
                                        {{ $pergunta->pergunta }}
                                    </h2>
                                </div>

                            </div>

                            <div class="space-y-3">

                                @forelse($pergunta->respostas as $resposta)

                                    <label class="grupo-resposta flex items-center gap-3 bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 cursor-pointer hover:bg-slate-700 hover:border-blue-500/50 transition">

                                        <input
                                            type="radio"
                                            name="respostas[{{ $pergunta->id }}]"
                                            value="{{ $resposta->id }}"
                                            required
                                            class="accent-blue-600"
                                        >

                                        <span class="text-sm text-slate-200">
                                            {{ $resposta->resposta }}
                                        </span>

                                    </label>

                                @empty

                                    <p class="text-red-400 text-sm bg-red-500/10 border border-red-500/30 rounded-xl p-3">
                                        Nenhuma alternativa cadastrada para esta pergunta.
                                    </p>

                                @endforelse

                            </div>

                        </div>

                    @endforeach

                </div>

                <!-- BOTÕES -->
                <div class="sticky bottom-0 mt-8 bg-slate-950/90 backdrop-blur border border-slate-800 rounded-2xl p-4 flex flex-col sm:flex-row justify-between items-center gap-3 shadow-2xl">

                    <button type="button"
                            onclick="confirmarSaidaBonito()"
                            class="w-full sm:w-auto text-center bg-slate-700 hover:bg-slate-600 px-5 py-3 rounded-lg transition">
                        Voltar
                    </button>

                    <button type="submit"
                            onclick="finalizandoFormulario = true"
                            class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded-lg font-semibold transition shadow-lg shadow-blue-600/20">
                        Finalizar pós-teste
                    </button>

                </div>

            </form>

        @else

            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 text-center shadow-lg">

                <div class="w-16 h-16 rounded-full bg-yellow-500/10 border border-yellow-500/30 flex items-center justify-center mx-auto mb-4">
                    <span class="text-3xl">⚠️</span>
                </div>

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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let finalizandoFormulario = false;
    const tempoLimiteMinutos = {{ $tempoLimite }};
    const form = document.getElementById('formPosTeste');

    function confirmarSaidaBonito() {
        Swal.fire({
            icon: 'warning',
            title: 'Deseja sair do pós-teste?',
            html: `
                <div style="text-align: center;">
                    <p style="color:#475569; font-size:15px; line-height:1.6; margin-bottom: 10px;">
                        Se você sair agora, suas respostas não serão enviadas.
                    </p>
                    <p style="color:#64748b; font-size:14px; line-height:1.5;">
                        Ao voltar para o pós-teste, o tempo será reiniciado.
                    </p>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Sim, sair',
            cancelButtonText: 'Continuar respondendo',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#2563eb',
            reverseButtons: true,
            background: '#ffffff',
            color: '#0f172a',
            customClass: {
                popup: 'rounded-2xl',
                confirmButton: 'rounded-lg',
                cancelButton: 'rounded-lg'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                finalizandoFormulario = true;
                window.location.href = "{{ route('dashboard.aluno') }}";
            }
        });
    }

    // Contador
    if (tempoLimiteMinutos > 0 && form) {
        let tempoRestante = tempoLimiteMinutos * 60;
        const contador = document.getElementById('contador');

        function atualizarContador() {
            const minutos = Math.floor(tempoRestante / 60);
            const segundos = tempoRestante % 60;

            contador.textContent =
                String(minutos).padStart(2, '0') + ':' + String(segundos).padStart(2, '0');

            if (tempoRestante <= 60) {
                contador.classList.remove('text-blue-400');
                contador.classList.add('text-red-400');
            }

            if (tempoRestante <= 0) {
                finalizandoFormulario = true;

                Swal.fire({
                    icon: 'info',
                    title: 'Tempo esgotado!',
                    text: 'Seu pós-teste será enviado automaticamente.',
                    confirmButtonColor: '#2563eb',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then(() => {
                    form.submit();
                });

                return;
            }

            tempoRestante--;
        }

        atualizarContador();
        setInterval(atualizarContador, 1000);
    }

    // Destaque visual na alternativa selecionada
    document.querySelectorAll('input[type="radio"]').forEach((radio) => {
        radio.addEventListener('change', function () {
            const name = this.name;

            document.querySelectorAll(`input[name="${name}"]`).forEach((input) => {
                input.closest('label').classList.remove('border-blue-500', 'bg-blue-500/10');
            });

            this.closest('label').classList.add('border-blue-500', 'bg-blue-500/10');
        });
    });
</script>

@endsection