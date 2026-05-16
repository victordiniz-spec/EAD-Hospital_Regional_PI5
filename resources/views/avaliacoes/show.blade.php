@extends('layout.app')

@section('title', 'Pós-teste')

@section('content')

@php
    $tempoLimite = isset($avaliacao->tempo_limite) && $avaliacao->tempo_limite
        ? (int) $avaliacao->tempo_limite
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

    body.pos-teste-bloqueado {
        overflow: hidden;
    }

    .alternativa-card:has(input:checked) {
        background: #EAF5EF !important;
        border-color: #00A63E !important;
        box-shadow: 0 10px 24px rgba(0, 85, 67, 0.10);
    }

    .alternativa-card:has(input:checked) .bolinha-alternativa {
        background: #005543;
        color: #ffffff;
        border-color: #005543;
    }

    .barra-respostas {
        transition: width .3s ease;
    }

    @media (max-width: 768px) {
        .area-pos-teste {
            padding-top: 5rem !important;
        }

        .titulo-pos-teste-mobile {
            font-size: 1.75rem !important;
            line-height: 2.15rem !important;
        }

        .rodape-pos-teste {
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            border-radius: 1.5rem 1.5rem 0 0 !important;
        }
    }
</style>

<div class="flex min-h-screen w-full bg-[#F3F7F3] text-[#003C2F] overflow-x-hidden">

    @include('partials.sidebar-aluno')

    <main class="flex-1 min-w-0 w-full bg-[#F3F7F3] overflow-x-hidden">

        @include('partials.navbar')

        <section class="area-pos-teste p-4 sm:p-6 lg:p-8 pb-36">

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

            <!-- CABEÇALHO -->
            <div class="mb-7 flex flex-col xl:flex-row xl:items-end xl:justify-between gap-5">

                <div class="min-w-0">
                    <div class="inline-flex items-center gap-2 text-[11px] font-extrabold uppercase tracking-widest text-[#00A63E] mb-2">
                        <span class="w-2 h-2 rounded-full bg-[#00A63E]"></span>
                        Avaliação do aluno
                    </div>

                    <h1 class="titulo-pos-teste-mobile text-3xl sm:text-4xl font-extrabold text-[#003C2F] tracking-tight break-words">
                        {{ $avaliacao->titulo ?? 'Pós-teste' }}
                    </h1>

                    <p class="text-sm text-[#60756B] mt-2 max-w-3xl">
                        Leia cada questão com atenção, marque uma alternativa por pergunta e finalize o pós-teste quando terminar.
                    </p>
                </div>

                @if($tempoLimite > 0)
                    <div class="bg-white border border-[#E3EBE4] rounded-3xl px-5 py-4 shadow-sm min-w-[230px]">
                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                            Tempo restante
                        </p>

                        <div id="contador" class="text-4xl font-extrabold text-[#004D3A] mt-1">
                            --:--
                        </div>

                        <p class="text-xs text-[#60756B] mt-1">
                            Limite: {{ $tempoLimite }} minuto(s)
                        </p>
                    </div>
                @else
                    <div class="bg-white border border-[#E3EBE4] rounded-3xl px-5 py-4 shadow-sm min-w-[230px]">
                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                            Tempo
                        </p>

                        <div class="text-2xl font-extrabold text-[#004D3A] mt-1">
                            Sem limite
                        </div>

                        <p class="text-xs text-[#60756B] mt-1">
                            Responda com tranquilidade.
                        </p>
                    </div>
                @endif

            </div>

            <!-- RESUMO -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-7">

                <div class="bg-white border border-[#E3EBE4] rounded-3xl p-5 shadow-sm">
                    <p class="text-xs text-[#60756B] font-semibold uppercase tracking-wider">Questões</p>
                    <h3 class="text-3xl font-extrabold text-[#004D3A] mt-1">{{ $totalPerguntas }}</h3>
                </div>

                <div class="bg-white border border-[#E3EBE4] rounded-3xl p-5 shadow-sm">
                    <p class="text-xs text-[#60756B] font-semibold uppercase tracking-wider">Tipo</p>
                    <h3 class="text-2xl font-extrabold text-[#004D3A] mt-2">Pós-teste</h3>
                </div>

                <div class="bg-white border border-[#E3EBE4] rounded-3xl p-5 shadow-sm">
                    <p class="text-xs text-[#60756B] font-semibold uppercase tracking-wider">Status</p>
                    <h3 class="text-2xl font-extrabold text-yellow-700 mt-2">Em andamento</h3>
                </div>

            </div>

            @if($perguntas->count() > 0)

                <form method="POST"
                      action="{{ route('avaliacoes.submit', $avaliacao->id) }}"
                      id="formPosTeste">
                    @csrf

                    <div class="grid grid-cols-1 xl:grid-cols-12 gap-7">

                        <!-- QUESTÕES -->
                        <div class="xl:col-span-8 space-y-5">

                            @foreach($perguntas as $index => $pergunta)

                                <article class="questao-card bg-white border border-[#E3EBE4] rounded-3xl p-5 sm:p-6 shadow-sm transition hover:shadow-md"
                                         data-pergunta-id="{{ $pergunta->id }}">

                                    <div class="flex items-start gap-4 mb-5">

                                        <div class="w-12 h-12 rounded-2xl bg-[#004D3A] text-white flex items-center justify-center font-extrabold shrink-0">
                                            {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                        </div>

                                        <div class="min-w-0">
                                            <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold mb-1">
                                                Questão {{ $index + 1 }}
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

                                            <label class="alternativa-card flex items-center gap-3 bg-[#F8FBF8] border border-[#DCE7DE] rounded-2xl px-4 py-3 cursor-pointer hover:bg-[#EAF5EF] hover:border-[#00A63E]/60 transition">

                                                <input
                                                    type="radio"
                                                    name="respostas[{{ $pergunta->id }}]"
                                                    value="{{ $resposta->id }}"
                                                    required
                                                    class="sr-only radio-resposta"
                                                    data-pergunta="{{ $pergunta->id }}"
                                                >

                                                <span class="bolinha-alternativa w-9 h-9 rounded-xl border border-[#C9D8CE] bg-white text-[#004D3A] flex items-center justify-center text-xs font-extrabold shrink-0 transition">
                                                    {{ $letra }}
                                                </span>

                                                <span class="text-sm sm:text-base text-[#173F36] font-semibold leading-relaxed break-words">
                                                    {{ $resposta->resposta }}
                                                </span>

                                            </label>

                                        @empty

                                            <p class="text-red-600 text-sm bg-red-50 border border-red-100 rounded-2xl p-4 font-semibold">
                                                Nenhuma alternativa cadastrada para esta pergunta.
                                            </p>

                                        @endforelse

                                    </div>

                                </article>

                            @endforeach

                        </div>

                        <!-- PAINEL LATERAL -->
                        <aside class="xl:col-span-4 space-y-5">

                            <div class="bg-white border border-[#E3EBE4] rounded-3xl p-5 shadow-sm sticky top-6">
                                <div class="flex items-start gap-3 mb-4">
                                    <div class="w-12 h-12 rounded-2xl bg-[#EAF5EF] text-[#004D3A] flex items-center justify-center shrink-0 text-xl">
                                        📝
                                    </div>

                                    <div>
                                        <h2 class="font-extrabold text-lg text-[#003C2F]">
                                            Progresso das respostas
                                        </h2>

                                        <p class="text-xs text-[#60756B] mt-1">
                                            Acompanhe quantas questões já foram respondidas.
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between text-xs font-bold text-[#004D3A] mb-2">
                                    <span><span id="respondidasTexto">0</span> de {{ $totalPerguntas }} respondidas</span>
                                    <span id="percentualTexto">0%</span>
                                </div>

                                <div class="h-3 bg-[#E7EEE9] rounded-full overflow-hidden">
                                    <div id="barraRespostas" class="barra-respostas h-full bg-[#005543] rounded-full" style="width: 0%;"></div>
                                </div>

                                <div class="mt-5 bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-4">
                                    <p class="text-xs text-[#60756B] leading-relaxed">
                                        Antes de finalizar, confira se todas as perguntas foram respondidas. Ao enviar, sua nota será registrada no sistema.
                                    </p>
                                </div>

                                <button type="button"
                                        onclick="confirmarSaidaBonito()"
                                        class="mt-5 w-full bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-3 rounded-2xl font-bold transition">
                                    Voltar para as aulas
                                </button>
                            </div>

                        </aside>

                    </div>

                    <!-- RODAPÉ FIXO -->
                    <div class="rodape-pos-teste fixed lg:left-[16rem] left-0 right-0 bottom-0 z-[80] bg-white/95 backdrop-blur border-t border-[#DCE7DE] shadow-2xl px-4 sm:px-6 py-4">
                        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

                            <div class="text-sm text-[#60756B] font-semibold text-center sm:text-left">
                                <span class="text-[#004D3A] font-extrabold" id="respondidasRodape">0</span>
                                de
                                <span class="text-[#004D3A] font-extrabold">{{ $totalPerguntas }}</span>
                                questões respondidas
                            </div>

                            <div class="flex flex-col sm:flex-row gap-3">
                                <button type="button"
                                        onclick="confirmarSaidaBonito()"
                                        class="w-full sm:w-auto bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-3 rounded-2xl font-bold transition">
                                    Voltar
                                </button>

                                <button type="submit"
                                        onclick="finalizandoFormulario = true"
                                        class="w-full sm:w-auto bg-[#005543] hover:bg-[#004636] text-white px-6 py-3 rounded-2xl font-extrabold transition shadow-sm">
                                    Finalizar pós-teste
                                </button>
                            </div>

                        </div>
                    </div>

                </form>

            @else

                <div class="max-w-2xl mx-auto bg-white rounded-3xl border border-[#E3EBE4] shadow-sm p-8 text-center">

                    <div class="w-20 h-20 rounded-full bg-yellow-100 text-yellow-700 flex items-center justify-center mx-auto mb-5 text-3xl">
                        ⚠️
                    </div>

                    <h2 class="text-2xl font-extrabold text-[#004D3A] mb-2">
                        Nenhuma pergunta encontrada
                    </h2>

                    <p class="text-sm text-[#60756B] mb-6">
                        Este pós-teste ainda não possui perguntas cadastradas.
                    </p>

                    <a href="{{ route('dashboard.aluno') }}"
                       class="inline-flex items-center justify-center bg-[#005543] hover:bg-[#004636] text-white px-5 py-3 rounded-2xl font-extrabold transition">
                        Voltar para o dashboard
                    </a>
                </div>

            @endif

        </section>

    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let finalizandoFormulario = false;
    const tempoLimiteMinutos = {{ $tempoLimite }};
    const totalPerguntas = {{ $totalPerguntas }};
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
            cancelButtonColor: '#005543',
            reverseButtons: true,
            background: '#ffffff',
            color: '#0f172a',
            customClass: {
                popup: 'rounded-2xl',
                confirmButton: 'rounded-xl',
                cancelButton: 'rounded-xl'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                finalizandoFormulario = true;
                window.location.href = "{{ route('aluno.aulas') }}";
            }
        });
    }

    function atualizarProgressoRespostas() {
        const respondidas = new Set();

        document.querySelectorAll('.radio-resposta:checked').forEach((radio) => {
            respondidas.add(radio.dataset.pergunta);
        });

        const totalRespondidas = respondidas.size;
        const percentual = totalPerguntas > 0
            ? Math.round((totalRespondidas / totalPerguntas) * 100)
            : 0;

        const respondidasTexto = document.getElementById('respondidasTexto');
        const respondidasRodape = document.getElementById('respondidasRodape');
        const percentualTexto = document.getElementById('percentualTexto');
        const barraRespostas = document.getElementById('barraRespostas');

        if (respondidasTexto) respondidasTexto.innerText = totalRespondidas;
        if (respondidasRodape) respondidasRodape.innerText = totalRespondidas;
        if (percentualTexto) percentualTexto.innerText = percentual + '%';
        if (barraRespostas) barraRespostas.style.width = percentual + '%';
    }

    // Contador
    if (tempoLimiteMinutos > 0 && form) {
        let tempoRestante = tempoLimiteMinutos * 60;
        const contador = document.getElementById('contador');

        function atualizarContador() {
            const minutos = Math.floor(tempoRestante / 60);
            const segundos = tempoRestante % 60;

            if (contador) {
                contador.textContent =
                    String(minutos).padStart(2, '0') + ':' + String(segundos).padStart(2, '0');
            }

            if (contador && tempoRestante <= 60) {
                contador.classList.remove('text-[#004D3A]');
                contador.classList.add('text-red-600');
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

    // Destaque visual na alternativa selecionada + progresso
    document.querySelectorAll('.radio-resposta').forEach((radio) => {
        radio.addEventListener('change', function () {
            atualizarProgressoRespostas();
        });
    });

    if (form) {
        form.addEventListener('submit', function(e) {
            const respondidas = new Set();

            document.querySelectorAll('.radio-resposta:checked').forEach((radio) => {
                respondidas.add(radio.dataset.pergunta);
            });

            if (respondidas.size < totalPerguntas) {
                e.preventDefault();
                finalizandoFormulario = false;

                Swal.fire({
                    icon: 'warning',
                    title: 'Ainda faltam respostas',
                    text: 'Responda todas as questões antes de finalizar o pós-teste.',
                    confirmButtonColor: '#005543'
                });
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            confirmarSaidaBonito();
        }
    });

    atualizarProgressoRespostas();
</script>

@endsection
