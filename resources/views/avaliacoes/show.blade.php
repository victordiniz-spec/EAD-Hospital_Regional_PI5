@extends('layout.app')

@section('title', 'Pós-teste')

@section('content')

@php
    $tempoLimite = isset($avaliacao->tempo_limite) && $avaliacao->tempo_limite
        ? (int) $avaliacao->tempo_limite
        : 0;

    $tempoMinimo = isset($avaliacao->tempo_minimo) && $avaliacao->tempo_minimo
        ? (int) $avaliacao->tempo_minimo
        : 0;

    $totalPerguntas = $perguntas->count();
@endphp

<style>
    html, body {
        background: #F3F7F3 !important;
        margin: 0;
        padding: 0;
        width: 100%;
        min-height: 100%;
    }

    #app {
        background: #F3F7F3 !important;
        min-height: 100vh;
        width: 100%;
    }

    body.prova-em-andamento {
        overflow-x: hidden;
    }

    .opcao-resposta input[type="radio"] {
        accent-color: #005543;
    }

    .opcao-resposta.selecionada {
        background: #EAF5EF;
        border-color: #005543;
        box-shadow: 0 8px 24px rgba(0, 85, 67, 0.10);
    }

    .opcao-resposta.selecionada .bolinha-opcao {
        background: #005543;
        color: #ffffff;
        border-color: #005543;
    }

    @media (max-width: 640px) {
        .rodape-prova-fixo {
            border-radius: 1.5rem 1.5rem 0 0 !important;
        }
    }
</style>

<div class="min-h-screen bg-[#F3F7F3] text-[#003C2F]">

    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 pb-36">

        <!-- TOPO DA PROVA -->
        <header class="mb-6 bg-white border border-[#E3EBE4] rounded-3xl shadow-sm overflow-hidden">

            <div class="bg-[#004D3A] text-white px-5 sm:px-7 py-5 sm:py-6">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-5">

                    <div class="min-w-0">
                        <div class="inline-flex items-center gap-2 text-[11px] font-extrabold uppercase tracking-widest text-white/75 mb-2">
                            <span class="w-2 h-2 rounded-full bg-[#90D8C6]"></span>
                            Pós-teste da aula
                        </div>

                        <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight break-words">
                            {{ $avaliacao->titulo ?? 'Pós-teste' }}
                        </h1>

                        <p class="text-sm text-white/75 mt-2 max-w-3xl leading-relaxed">
                            Leia cada questão com atenção, marque apenas uma alternativa e finalize quando terminar.
                        </p>
                    </div>

                    <div class="bg-white/10 border border-white/20 rounded-3xl px-5 py-4 min-w-[220px]">
                        @if($tempoLimite > 0)
                            <p class="text-[11px] uppercase tracking-widest text-white/70 font-extrabold">
                                Tempo restante
                            </p>

                            <div id="contador"
                                 class="text-4xl font-extrabold text-white mt-1">
                                --:--
                            </div>

                            <p class="text-xs text-white/70 mt-1">
                                Mínimo: {{ $tempoMinimo }} minuto(s) • Máximo: {{ $tempoLimite }} minuto(s)
                            </p>
                        @else
                            <p class="text-[11px] uppercase tracking-widest text-white/70 font-extrabold">
                                Tempo
                            </p>

                            <div class="text-2xl font-extrabold text-white mt-1">
                                Sem limite
                            </div>

                            <p class="text-xs text-white/70 mt-1">
                                Responda com calma.
                            </p>
                        @endif
                    </div>

                </div>
            </div>

            <div class="p-5 sm:p-6">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                    <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-3xl p-4">
                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                            Questões
                        </p>

                        <h3 class="text-3xl font-extrabold text-[#004D3A] mt-1">
                            {{ $totalPerguntas }}
                        </h3>
                    </div>

                    <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-3xl p-4">
                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                            Tipo
                        </p>

                        <h3 class="text-2xl font-extrabold text-[#003C2F] mt-1">
                            Pós-teste
                        </h3>
                    </div>

                    <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-3xl p-4">
                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                            Respondidas
                        </p>

                        <h3 class="text-3xl font-extrabold text-[#004D3A] mt-1">
                            <span id="contadorRespondidas">0</span>/<span>{{ $totalPerguntas }}</span>
                        </h3>
                    </div>

                </div>

                <div class="mt-5">
                    <div class="flex items-center justify-between text-xs font-bold text-[#004D3A] mb-2">
                        <span>Progresso das respostas</span>
                        <span id="percentualRespondido">0%</span>
                    </div>

                    <div class="h-3 bg-[#E7EEE9] rounded-full overflow-hidden">
                        <div id="barraProgressoRespostas"
                             class="h-full bg-[#005543] rounded-full transition-all duration-500"
                             style="width: 0%;">
                        </div>
                    </div>
                </div>
            </div>

        </header>

        <!-- ALERTAS -->
        @if(session('success'))
            <div class="mb-5 bg-green-100 text-green-700 px-4 py-3 rounded-2xl border border-green-200 shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 bg-red-100 text-red-700 px-4 py-3 rounded-2xl border border-red-200 shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        @if($perguntas->count() > 0)

            <form method="POST"
                  action="{{ route('avaliacoes.submit', $avaliacao->id) }}"
                  id="formPosTeste">
                @csrf

                <section class="space-y-5">

                    @foreach($perguntas as $index => $pergunta)

                        <article class="questao-card bg-white border border-[#E3EBE4] rounded-3xl p-5 sm:p-6 shadow-sm"
                                 data-questao="{{ $pergunta->id }}">

                            <div class="flex items-start gap-4 mb-5">

                                <div class="w-12 h-12 rounded-2xl bg-[#004D3A] text-white flex items-center justify-center font-extrabold shrink-0">
                                    {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                </div>

                                <div class="min-w-0">
                                    <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold mb-1">
                                        Questão {{ $index + 1 }} de {{ $totalPerguntas }}
                                    </p>

                                    <h2 class="font-extrabold text-lg sm:text-xl leading-relaxed text-[#003C2F] break-words">
                                        {{ $pergunta->pergunta }}
                                    </h2>
                                </div>

                            </div>

                            <div class="space-y-3">

                                @forelse($pergunta->respostas as $respostaIndex => $resposta)

                                    @php
                                        $letra = chr(65 + $respostaIndex);
                                    @endphp

                                    <label class="opcao-resposta flex items-start gap-3 bg-[#F8FBF8] border border-[#DCE7DE] rounded-2xl px-4 py-4 cursor-pointer hover:bg-[#EAF5EF] hover:border-[#005543]/50 transition">

                                        <input
                                            type="radio"
                                            name="respostas[{{ $pergunta->id }}]"
                                            value="{{ $resposta->id }}"
                                            required
                                            class="mt-1 w-4 h-4 shrink-0"
                                            data-pergunta="{{ $pergunta->id }}"
                                        >

                                        <span class="bolinha-opcao w-8 h-8 rounded-xl border border-[#DCE7DE] bg-white text-[#004D3A] flex items-center justify-center text-xs font-extrabold shrink-0 transition">
                                            {{ $letra }}
                                        </span>

                                        <span class="text-sm sm:text-base text-[#173F36] leading-relaxed break-words">
                                            {{ $resposta->resposta }}
                                        </span>

                                    </label>

                                @empty

                                    <p class="text-red-600 text-sm bg-red-50 border border-red-100 rounded-2xl p-4 font-bold">
                                        Nenhuma alternativa cadastrada para esta pergunta.
                                    </p>

                                @endforelse

                            </div>

                        </article>

                    @endforeach

                </section>

                <!-- RODAPÉ FIXO DA PROVA -->
                <div class="rodape-prova-fixo fixed left-0 right-0 bottom-0 z-[9000] bg-white/95 backdrop-blur border-t border-[#DCE7DE] shadow-2xl">
                    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-4 flex flex-col sm:flex-row justify-between items-center gap-3">

                        <button type="button"
                                onclick="confirmarSaidaBonito()"
                                class="w-full sm:w-auto text-center border border-[#DCE7DE] bg-[#F8FBF8] hover:bg-[#EAF5EF] text-[#60756B] px-5 py-3 rounded-2xl font-bold transition">
                            Sair do pós-teste
                        </button>

                        <div class="w-full sm:w-auto flex flex-col sm:flex-row items-center gap-3">

                            <div class="text-xs text-[#60756B] font-bold text-center sm:text-right">
                                Respondidas:
                                <span class="text-[#004D3A]">
                                    <span id="contadorRespondidasRodape">0</span>/{{ $totalPerguntas }}
                                </span>
                            </div>

                            <button type="button"
                                    id="btnFinalizarPosTeste"
                                    onclick="confirmarEnvioPosTeste()"
                                    class="w-full sm:w-auto bg-[#005543] hover:bg-[#004636] text-white px-7 py-3 rounded-2xl font-extrabold transition shadow-sm disabled:opacity-60 disabled:cursor-not-allowed">
                                Finalizar pós-teste
                            </button>

                        </div>

                    </div>
                </div>

            </form>

        @else

            <div class="bg-white border border-[#E3EBE4] rounded-3xl p-8 text-center shadow-sm">

                <div class="w-20 h-20 rounded-full bg-yellow-100 text-yellow-700 flex items-center justify-center mx-auto mb-5 text-3xl">
                    ⚠️
                </div>

                <h2 class="text-2xl font-extrabold text-[#003C2F] mb-2">
                    Nenhuma pergunta encontrada
                </h2>

                <p class="text-[#60756B] mb-6">
                    Este pós-teste ainda não possui perguntas cadastradas.
                </p>

                <a href="{{ route('aluno.aulas') }}"
                   class="inline-flex items-center justify-center bg-[#005543] hover:bg-[#004636] text-white px-5 py-3 rounded-2xl font-extrabold transition">
                    Voltar para minhas videoaulas
                </a>
            </div>

        @endif

    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.body.classList.add('prova-em-andamento');

    let finalizandoFormulario = false;
    let saiuConfirmado = false;
    let tempoMinimoLiberado = false;
    let avisoSaidaAberto = false;

    const tempoLimiteMinutos = {{ $tempoLimite }};
    const tempoMinimoMinutos = {{ $tempoMinimo }};
    const totalPerguntas = {{ $totalPerguntas }};
    const avaliacaoId = {{ $avaliacao->id }};
    const csrfToken = '{{ csrf_token() }}';
    const rotaResetarTempo = '/avaliacoes/' + avaliacaoId + '/resetar-tempo';
    const form = document.getElementById('formPosTeste');
    const btnFinalizarPosTeste = document.getElementById('btnFinalizarPosTeste');

    @if(session('error'))
        Swal.fire({
            icon: 'warning',
            title: 'Atenção',
            text: @json(session('error')),
            confirmButtonColor: '#005543',
            background: '#ffffff',
            color: '#0f172a'
        });
    @endif

    async function resetarTempoPosTeste() {
        try {
            await fetch(rotaResetarTempo, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ motivo: 'saida_do_pos_teste' }),
                keepalive: true
            });
        } catch (e) {}
    }

    function resetarAlternativasMarcadas() {
        document.querySelectorAll('input[type="radio"]').forEach((radio) => {
            radio.checked = false;
        });

        document.querySelectorAll('.opcao-resposta').forEach((label) => {
            label.classList.remove('selecionada');
        });

        atualizarProgressoRespostas();
    }

    function mensagemSaidaReinicio() {
        return `
            <div style="text-align:center;">
                <p style="color:#475569; font-size:15px; line-height:1.6; margin-bottom:10px;">
                    Tem certeza que deseja sair do pós-teste?
                </p>
                <p style="color:#dc2626; font-size:14px; line-height:1.6; font-weight:700;">
                    Se sair agora, o tempo mínimo e o tempo máximo serão reiniciados.
                    As alternativas marcadas também serão apagadas.
                </p>
                <p style="color:#64748b; font-size:13px; line-height:1.5; margin-top:10px;">
                    Ao entrar novamente, o pós-teste começará do zero.
                </p>
            </div>
        `;
    }

    function confirmarSaidaBonito() {
        if (avisoSaidaAberto) return;

        avisoSaidaAberto = true;

        Swal.fire({
            icon: 'warning',
            title: 'Sair do pós-teste?',
            html: mensagemSaidaReinicio(),
            showCancelButton: true,
            confirmButtonText: 'Sim, sair e reiniciar',
            cancelButtonText: 'Continuar respondendo',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#005543',
            reverseButtons: true,
            background: '#ffffff',
            color: '#0f172a',
            customClass: {
                popup: 'rounded-3xl',
                confirmButton: 'rounded-2xl',
                cancelButton: 'rounded-2xl'
            }
        }).then(async (result) => {
            avisoSaidaAberto = false;

            if (result.isConfirmed) {
                finalizandoFormulario = true;
                saiuConfirmado = true;

                resetarAlternativasMarcadas();
                await resetarTempoPosTeste();

                window.location.href = "{{ route('aluno.aulas') }}";
            }
        });
    }

    function atualizarProgressoRespostas() {
        const perguntasRespondidas = new Set();

        document.querySelectorAll('input[type="radio"]:checked').forEach((radio) => {
            perguntasRespondidas.add(radio.dataset.pergunta);
        });

        const respondidas = perguntasRespondidas.size;
        const percentual = totalPerguntas > 0
            ? Math.round((respondidas / totalPerguntas) * 100)
            : 0;

        const contadorTopo = document.getElementById('contadorRespondidas');
        const contadorRodape = document.getElementById('contadorRespondidasRodape');
        const percentualTexto = document.getElementById('percentualRespondido');
        const barra = document.getElementById('barraProgressoRespostas');

        if (contadorTopo) contadorTopo.innerText = respondidas;
        if (contadorRodape) contadorRodape.innerText = respondidas;
        if (percentualTexto) percentualTexto.innerText = percentual + '%';
        if (barra) barra.style.width = percentual + '%';
    }

    function segundosRestantesTempoMinimo() {
        if (tempoMinimoMinutos <= 0) {
            return 0;
        }

        const minimoEmSegundos = tempoMinimoMinutos * 60;
        const decorrido = window.tempoMinimoDecorridoSegundos !== undefined
            ? window.tempoMinimoDecorridoSegundos
            : 0;

        return Math.max(0, minimoEmSegundos - decorrido);
    }

    function formatarTempo(segundos) {
        const minutos = Math.floor(segundos / 60);
        const restoSegundos = segundos % 60;

        return String(minutos).padStart(2, '0') + ':' + String(restoSegundos).padStart(2, '0');
    }

    function verificarLiberacaoTempoMinimo() {
        if (!btnFinalizarPosTeste) return;

        if (tempoMinimoMinutos <= 0) {
            tempoMinimoLiberado = true;
            btnFinalizarPosTeste.disabled = false;
            btnFinalizarPosTeste.innerText = 'Finalizar pós-teste';
            return;
        }

        const restante = segundosRestantesTempoMinimo();

        if (restante > 0) {
            tempoMinimoLiberado = false;
            btnFinalizarPosTeste.disabled = true;
            btnFinalizarPosTeste.innerText = 'Aguarde ' + formatarTempo(restante);
        } else {
            tempoMinimoLiberado = true;
            btnFinalizarPosTeste.disabled = false;
            btnFinalizarPosTeste.innerText = 'Finalizar pós-teste';
        }
    }

    function confirmarEnvioPosTeste() {
        if (!form) return;

        if (!tempoMinimoLiberado) {
            const restante = segundosRestantesTempoMinimo();

            Swal.fire({
                icon: 'warning',
                title: 'Tempo mínimo não atingido',
                html: `
                    <div style="text-align:center;">
                        <p style="color:#475569; font-size:15px; line-height:1.6;">
                            Você precisa permanecer pelo menos <strong>${tempoMinimoMinutos} minuto(s)</strong> no pós-teste antes de finalizar.
                        </p>
                        <p style="color:#dc2626; font-size:16px; font-weight:800; margin-top:10px;">
                            Aguarde mais ${formatarTempo(restante)}.
                        </p>
                    </div>
                `,
                confirmButtonColor: '#005543',
                background: '#ffffff',
                color: '#0f172a'
            });

            return;
        }

        const perguntasRespondidas = new Set();

        document.querySelectorAll('input[type="radio"]:checked').forEach((radio) => {
            perguntasRespondidas.add(radio.dataset.pergunta);
        });

        if (perguntasRespondidas.size < totalPerguntas) {
            Swal.fire({
                icon: 'warning',
                title: 'Ainda faltam respostas',
                text: 'Responda todas as questões antes de finalizar o pós-teste.',
                confirmButtonColor: '#005543',
                background: '#ffffff',
                color: '#0f172a'
            });
            return;
        }

        Swal.fire({
            icon: 'question',
            title: 'Finalizar pós-teste?',
            text: 'Depois de enviar, suas respostas serão salvas e sua nota será calculada.',
            showCancelButton: true,
            confirmButtonText: 'Sim, finalizar',
            cancelButtonText: 'Revisar respostas',
            confirmButtonColor: '#005543',
            cancelButtonColor: '#64748b',
            reverseButtons: true,
            background: '#ffffff',
            color: '#0f172a'
        }).then((result) => {
            if (result.isConfirmed) {
                finalizandoFormulario = true;
                form.submit();
            }
        });
    }

    // Contador do tempo máximo e liberação do tempo mínimo
    window.tempoMinimoDecorridoSegundos = 0;

    setInterval(() => {
        window.tempoMinimoDecorridoSegundos++;
        verificarLiberacaoTempoMinimo();
    }, 1000);

    verificarLiberacaoTempoMinimo();

    if (tempoLimiteMinutos > 0 && form) {
        let tempoRestante = tempoLimiteMinutos * 60;
        const contador = document.getElementById('contador');

        function atualizarContador() {
            const minutos = Math.floor(tempoRestante / 60);
            const segundos = tempoRestante % 60;

            if (contador) {
                contador.textContent =
                    String(minutos).padStart(2, '0') + ':' + String(segundos).padStart(2, '0');

                if (tempoRestante <= 60) {
                    contador.classList.add('text-red-200');
                }
            }

            if (tempoRestante <= 0) {
                finalizandoFormulario = true;

                Swal.fire({
                    icon: 'info',
                    title: 'Tempo esgotado!',
                    text: 'Seu pós-teste será enviado automaticamente.',
                    confirmButtonColor: '#005543',
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
                const label = input.closest('label');

                if (label) {
                    label.classList.remove('selecionada');
                }
            });

            const labelAtual = this.closest('label');

            if (labelAtual) {
                labelAtual.classList.add('selecionada');
            }

            atualizarProgressoRespostas();
        });
    });

    window.addEventListener('beforeunload', function (e) {
        if (!finalizandoFormulario && form) {
            e.preventDefault();
            e.returnValue = 'Tem certeza que deseja sair? Seu tempo será reiniciado e as alternativas marcadas serão apagadas.';
        }
    });

    window.addEventListener('pagehide', function () {
        if (!finalizandoFormulario && form) {
            const dados = new FormData();
            dados.append('_token', csrfToken);
            dados.append('motivo', 'pagehide_saida_da_pagina');

            if (navigator.sendBeacon) {
                navigator.sendBeacon(rotaResetarTempo, dados);
            }
        }
    });

    history.pushState({ posTesteAberto: true }, '', window.location.href);

    window.addEventListener('popstate', function () {
        if (finalizandoFormulario || saiuConfirmado) return;

        history.pushState({ posTesteAberto: true }, '', window.location.href);
        confirmarSaidaBonito();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            confirmarSaidaBonito();
        }
    });

    atualizarProgressoRespostas();
</script>

@endsection
