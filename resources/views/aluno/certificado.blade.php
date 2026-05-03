@extends('layout.app')

@section('title', 'Meu Certificado')

@section('content')

@php
    use Illuminate\Support\Facades\DB;

    $aluno = auth()->user();
    $alunoId = auth()->id();

    // ACESSO TEMPORÁRIO DE TESTE
    // Senha: 123
    $acessoTeste = request('teste') === '123';

    // =========================
    // AULAS
    // =========================
    $totalAulas = DB::table('aulas')->count();

    $totalAulasAssistidas = DB::table('aulas_assistidas')
        ->where('aluno_id', $alunoId)
        ->where('assistido', true)
        ->count();

    $aulasOk = $totalAulas > 0 && $totalAulasAssistidas >= $totalAulas;

    // =========================
    // PÓS-TESTES
    // =========================
    $posTestesIds = DB::table('avaliacoes')
        ->whereNotNull('aula_id')
        ->pluck('id');

    $totalPosTestes = $posTestesIds->count();

    $totalPosTestesFeitos = $totalPosTestes > 0
        ? DB::table('notas')
            ->where('aluno_id', $alunoId)
            ->whereIn('avaliacao_id', $posTestesIds)
            ->distinct('avaliacao_id')
            ->count('avaliacao_id')
        : 0;

    $postestesOk = $totalPosTestesFeitos >= $totalPosTestes;

    // =========================
    // PROVA FINAL
    // =========================
    $provaFinal = DB::table('avaliacoes')
        ->where('tipo', 'final')
        ->first();

    $notaFinal = null;
    $provaFinalFeita = false;
    $aprovadoNaFinal = false;

    if ($provaFinal) {
        $resultadoFinal = DB::table('notas')
            ->where('aluno_id', $alunoId)
            ->where('avaliacao_id', $provaFinal->id)
            ->orderByDesc('created_at')
            ->first();

        if ($resultadoFinal) {
            $provaFinalFeita = true;

            if (isset($resultadoFinal->porcentagem)) {
                $notaFinal = $resultadoFinal->porcentagem;
            } elseif (isset($resultadoFinal->nota)) {
                $notaFinal = $resultadoFinal->nota;
            } elseif (isset($resultadoFinal->pontuacao)) {
                $notaFinal = $resultadoFinal->pontuacao;
            } else {
                $notaFinal = 0;
            }

            $aprovadoNaFinal = $notaFinal >= 70;
        }
    }

    // =========================
    // LIBERAÇÃO DO CERTIFICADO
    // =========================
    $certificadoLiberado = ($aulasOk && $postestesOk && $provaFinalFeita && $aprovadoNaFinal) || $acessoTeste;

    $totalRequisitos = 4;
    $requisitosConcluidos = 0;

    if ($aulasOk) $requisitosConcluidos++;
    if ($postestesOk) $requisitosConcluidos++;
    if ($provaFinalFeita) $requisitosConcluidos++;
    if ($aprovadoNaFinal) $requisitosConcluidos++;

    $progresso = round(($requisitosConcluidos / $totalRequisitos) * 100);

    // =========================
    // MODELO DO CERTIFICADO
    // =========================
    $modeloCertificado = DB::table('certificados')
        ->orderByDesc('created_at')
        ->first();

    $nomeCurso = $modeloCertificado->curso ?? 'Integrar ReSaúde';
    $cargaHoraria = $modeloCertificado->carga_horaria ?? 40;
    $responsavel = $modeloCertificado->responsavel ?? 'Responsável pelo Curso';
    $cargo = $modeloCertificado->cargo ?? 'Coordenação do Curso';
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

    @media print {
        body * {
            visibility: hidden;
        }

        #areaCertificado, #areaCertificado * {
            visibility: visible;
        }

        #areaCertificado {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            box-shadow: none !important;
        }

        .nao-imprimir {
            display: none !important;
        }
    }
</style>

<div class="flex min-h-screen w-full bg-[#F3F7F3] text-[#003C2F] overflow-x-hidden">

    @include('partials.sidebar-aluno')

    <main class="flex-1 min-w-0 w-full bg-[#F3F7F3] overflow-x-hidden">

        @include('partials.navbar')

        <section class="p-4 sm:p-6 lg:p-8">

            <!-- CABEÇALHO -->
            <div class="mb-7 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-5 nao-imprimir">

                <div>
                    <div class="flex items-center gap-2 text-[11px] font-extrabold uppercase tracking-widest text-[#60756B] mb-2">
                        <span>Aluno</span>
                        <span>›</span>
                        <span class="text-[#004D3A]">Certificado</span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl font-extrabold text-[#003C2F] tracking-tight">
                        Meu Certificado
                    </h1>

                    <p class="text-sm text-[#60756B] mt-2 max-w-2xl">
                        O certificado será liberado após concluir todas as aulas, pós-testes e obter pelo menos 70% na prova final.
                    </p>

                    @if($acessoTeste)
                        <div class="mt-4 inline-flex items-center gap-2 bg-yellow-100 text-yellow-800 border border-yellow-200 px-4 py-2 rounded-2xl text-sm font-bold">
                            ⚠️ Acesso de teste ativado
                        </div>
                    @endif
                </div>

                @if($certificadoLiberado)
                    <button type="button"
                            onclick="window.print()"
                            class="bg-[#004D3A] hover:bg-[#003C2F] text-white px-6 py-3 rounded-2xl font-extrabold transition shadow-sm">
                        Imprimir certificado
                    </button>
                @endif

            </div>

            @if(!$certificadoLiberado)

                <!-- CERTIFICADO BLOQUEADO -->
                <div class="grid grid-cols-1 xl:grid-cols-12 gap-7 nao-imprimir">

                    <div class="xl:col-span-8">

                        <div class="bg-white border border-[#E3EBE4] rounded-3xl p-6 sm:p-8 shadow-sm">

                            <div class="w-20 h-20 rounded-full bg-red-50 text-red-600 flex items-center justify-center mb-5">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-10 h-10"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="1.8"
                                          d="M16.5 10.5V6.75a4.5 4.5 0 0 0-9 0v3.75m-.75 11.25h10.5A2.25 2.25 0 0 0 19.5 19.5v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 12.75v6.75a2.25 2.25 0 0 0 2.25 2.25z"/>
                                </svg>
                            </div>

                            <h2 class="text-2xl sm:text-3xl font-extrabold text-[#003C2F] mb-3">
                                Certificado bloqueado
                            </h2>

                            <p class="text-[#60756B] text-sm leading-relaxed max-w-2xl">
                                Você ainda não concluiu todos os requisitos necessários para liberar seu certificado.
                            </p>

                            <div class="mt-6 bg-[#F8FBF8] border border-[#E3EBE4] rounded-3xl p-5">

                                <div class="flex items-center justify-between mb-3">
                                    <p class="text-sm font-extrabold text-[#003C2F]">
                                        Progresso do certificado
                                    </p>

                                    <p class="text-sm font-extrabold text-[#004D3A]">
                                        {{ $progresso }}%
                                    </p>
                                </div>

                                <div class="w-full h-3 bg-[#E8EFE9] rounded-full overflow-hidden">
                                    <div class="h-full bg-[#004D3A] rounded-full transition-all duration-700"
                                         style="width: {{ $progresso }}%;">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-5">

                                    <div class="bg-white rounded-2xl border border-[#E3EBE4] p-4">
                                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                            Aulas assistidas
                                        </p>

                                        <p class="text-2xl font-extrabold mt-1 {{ $aulasOk ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $totalAulasAssistidas }} / {{ $totalAulas }}
                                        </p>
                                    </div>

                                    <div class="bg-white rounded-2xl border border-[#E3EBE4] p-4">
                                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                            Pós-testes feitos
                                        </p>

                                        <p class="text-2xl font-extrabold mt-1 {{ $postestesOk ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $totalPosTestesFeitos }} / {{ $totalPosTestes }}
                                        </p>
                                    </div>

                                    <div class="bg-white rounded-2xl border border-[#E3EBE4] p-4">
                                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                            Prova final
                                        </p>

                                        <p class="text-2xl font-extrabold mt-1 {{ $provaFinalFeita ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $provaFinalFeita ? 'Feita' : 'Pendente' }}
                                        </p>
                                    </div>

                                    <div class="bg-white rounded-2xl border border-[#E3EBE4] p-4">
                                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                            Aproveitamento
                                        </p>

                                        <p class="text-2xl font-extrabold mt-1 {{ $aprovadoNaFinal ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $notaFinal !== null ? $notaFinal . '%' : '0%' }}
                                        </p>

                                        <p class="text-xs text-[#60756B] mt-1">
                                            Mínimo necessário: 70%
                                        </p>
                                    </div>

                                </div>

                            </div>

                            <div class="flex flex-col sm:flex-row gap-3 mt-6">
                                <a href="{{ route('aluno.aulas') }}"
                                   class="inline-flex items-center justify-center bg-[#004D3A] text-white px-6 py-3 rounded-2xl font-bold hover:bg-[#003C2F] transition">
                                    Continuar minhas aulas
                                </a>

                                <button type="button"
                                        onclick="abrirAcessoTesteCertificado()"
                                        class="inline-flex items-center justify-center bg-yellow-100 text-yellow-800 border border-yellow-200 px-6 py-3 rounded-2xl font-bold hover:bg-yellow-200 transition">
                                    Acesso de teste
                                </button>
                            </div>

                        </div>

                    </div>

                    <aside class="xl:col-span-4">
                        <div class="bg-white border border-[#E3EBE4] rounded-3xl p-6 shadow-sm">

                            <h3 class="text-xl font-extrabold text-[#003C2F] mb-4">
                                Requisitos
                            </h3>

                            <div class="space-y-4">

                                <div class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-xl {{ $aulasOk ? 'bg-green-100 text-green-700' : 'bg-red-50 text-red-600' }} flex items-center justify-center shrink-0 font-bold">
                                        {{ $aulasOk ? '✓' : '!' }}
                                    </div>

                                    <div>
                                        <p class="font-bold text-[#003C2F]">Assistir todas as aulas</p>
                                        <p class="text-sm text-[#60756B]">{{ $totalAulasAssistidas }} de {{ $totalAulas }} concluídas.</p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-xl {{ $postestesOk ? 'bg-green-100 text-green-700' : 'bg-red-50 text-red-600' }} flex items-center justify-center shrink-0 font-bold">
                                        {{ $postestesOk ? '✓' : '!' }}
                                    </div>

                                    <div>
                                        <p class="font-bold text-[#003C2F]">Concluir pós-testes</p>
                                        <p class="text-sm text-[#60756B]">{{ $totalPosTestesFeitos }} de {{ $totalPosTestes }} concluídos.</p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-xl {{ $provaFinalFeita ? 'bg-green-100 text-green-700' : 'bg-red-50 text-red-600' }} flex items-center justify-center shrink-0 font-bold">
                                        {{ $provaFinalFeita ? '✓' : '!' }}
                                    </div>

                                    <div>
                                        <p class="font-bold text-[#003C2F]">Fazer prova final</p>
                                        <p class="text-sm text-[#60756B]">{{ $provaFinalFeita ? 'Prova realizada.' : 'Ainda não realizada.' }}</p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-xl {{ $aprovadoNaFinal ? 'bg-green-100 text-green-700' : 'bg-red-50 text-red-600' }} flex items-center justify-center shrink-0 font-bold">
                                        {{ $aprovadoNaFinal ? '✓' : '!' }}
                                    </div>

                                    <div>
                                        <p class="font-bold text-[#003C2F]">Atingir 70% na prova final</p>
                                        <p class="text-sm text-[#60756B]">Nota atual: {{ $notaFinal !== null ? $notaFinal . '%' : '0%' }}.</p>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </aside>

                </div>

            @else

                <!-- CERTIFICADO LIBERADO -->
                <div class="bg-white border border-[#E3EBE4] rounded-3xl shadow-sm p-5 sm:p-6 overflow-x-auto">

                    <div id="areaCertificado"
                         class="min-w-[780px] bg-white mx-auto rounded-2xl border-[7px] border-[#EAF5EF] shadow-sm relative overflow-hidden"
                         style="width: 850px; min-height: 590px;">

                        <div class="absolute -top-16 -right-16 w-52 h-52 rounded-full border-[30px] border-[#EAF5EF] opacity-80"></div>
                        <div class="absolute -bottom-20 -left-20 w-60 h-60 rounded-full border-[34px] border-[#EAF5EF] opacity-80"></div>

                        <div class="relative z-10 px-20 py-14 text-center">

                            <p class="text-sm font-extrabold text-[#60756B] tracking-[0.25em] uppercase">
                                Integrar ReSaúde
                            </p>

                            <h1 class="mt-7 text-4xl font-extrabold tracking-[0.22em] text-[#004D3A] leading-tight">
                                CERTIFICADO DE<br>
                                CONCLUSÃO
                            </h1>

                            <p class="mt-8 text-sm text-[#374151]">
                                Certificamos que
                            </p>

                            <div class="mt-5 mx-auto max-w-lg border-b-2 border-[#BFD8C5] pb-2">
                                <p class="text-2xl font-extrabold text-[#1F2937] tracking-wide">
                                    {{ $aluno->name }}
                                </p>
                            </div>

                            <p class="mt-8 text-sm text-[#4B5563] leading-relaxed max-w-2xl mx-auto">
                                concluiu com aproveitamento o curso
                                <strong>{{ $nomeCurso }}</strong>,
                                com carga horária total de
                                <strong>{{ $cargaHoraria }} horas</strong>.
                            </p>

                            <p class="mt-3 text-sm text-[#4B5563] leading-relaxed max-w-2xl mx-auto">
                                O aluno cumpriu todos os requisitos obrigatórios e obteve aproveitamento mínimo de 70% na prova final.
                            </p>

                            <div class="grid grid-cols-2 gap-14 mt-16 items-end">

                                <!-- ASSINATURA MANUAL -->
                                <div>
                                    <div class="h-16 flex items-end justify-center">
                                        <span class="text-xs italic text-[#A5B7AB]">
                                            Espaço para assinatura manual
                                        </span>
                                    </div>

                                    <div class="border-t border-[#8A9B92] pt-2">
                                        <p class="text-xs font-bold text-[#374151] uppercase">
                                            {{ $responsavel }}
                                        </p>

                                        <p class="text-[10px] text-[#60756B] mt-1 uppercase">
                                            {{ $cargo }}
                                        </p>
                                    </div>
                                </div>

                                <!-- DADOS -->
                                <div>
                                    <div class="text-left inline-block">
                                        <p class="text-xs text-[#60756B]">
                                            CPF:
                                            <strong class="text-[#374151]">{{ $aluno->cpf }}</strong>
                                        </p>

                                        <p class="text-xs text-[#60756B] mt-2">
                                            Aproveitamento:
                                            <strong class="text-[#374151]">{{ $notaFinal ?? 70 }}%</strong>
                                        </p>

                                        <div class="border-t border-[#8A9B92] mt-6 pt-2">
                                            <p class="text-[10px] text-[#60756B] uppercase">
                                                Data de emissão
                                            </p>

                                            <p class="text-xs font-bold text-[#374151] mt-1">
                                                {{ now()->format('d/m/Y') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            @endif

        </section>

    </main>

</div>

<!-- MODAL ACESSO TESTE -->
<div id="modalAcessoTesteCertificado"
     class="fixed inset-0 hidden items-center justify-center z-[90] bg-black/50 backdrop-blur-sm px-4">

    <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl border border-[#E3EBE4] p-6 text-center">

        <div class="w-16 h-16 mx-auto rounded-full bg-yellow-100 text-yellow-700 flex items-center justify-center mb-4">
            <span class="text-2xl">🔐</span>
        </div>

        <h2 class="text-xl font-extrabold text-[#003C2F] mb-2">
            Acesso de teste
        </h2>

        <p class="text-sm text-[#60756B] mb-5">
            Digite a senha de teste para liberar temporariamente a visualização do certificado.
        </p>

        <input
            type="password"
            id="senhaTesteCertificado"
            placeholder="Digite a senha"
            class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-[#F8FBF8] text-[#003C2F] text-center font-bold focus:outline-none focus:ring-2 focus:ring-[#00A63E] mb-4"
        >

        <p id="erroSenhaTesteCertificado" class="hidden text-sm text-red-600 font-bold mb-4">
            Senha incorreta. Tente novamente.
        </p>

        <div class="flex gap-3">
            <button type="button"
                    onclick="fecharAcessoTesteCertificado()"
                    class="w-1/2 px-4 py-3 rounded-2xl bg-gray-100 text-gray-700 font-bold hover:bg-gray-200 transition">
                Cancelar
            </button>

            <button type="button"
                    onclick="validarAcessoTesteCertificado()"
                    class="w-1/2 px-4 py-3 rounded-2xl bg-[#004D3A] text-white font-bold hover:bg-[#003C2F] transition">
                Entrar
            </button>
        </div>

    </div>
</div>

<script>
    function abrirAcessoTesteCertificado() {
        const modal = document.getElementById('modalAcessoTesteCertificado');
        const input = document.getElementById('senhaTesteCertificado');
        const erro = document.getElementById('erroSenhaTesteCertificado');

        if (!modal) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        if (input) {
            input.value = '';
            setTimeout(() => input.focus(), 150);
        }

        if (erro) {
            erro.classList.add('hidden');
        }
    }

    function fecharAcessoTesteCertificado() {
        const modal = document.getElementById('modalAcessoTesteCertificado');

        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function validarAcessoTesteCertificado() {
        const input = document.getElementById('senhaTesteCertificado');
        const erro = document.getElementById('erroSenhaTesteCertificado');

        const senha = input ? input.value.trim() : '';

        if (senha === '123') {
            window.location.href = "{{ route('certificado.aluno') }}?teste=123";
            return;
        }

        if (erro) {
            erro.classList.remove('hidden');
        }

        if (input) {
            input.value = '';
            input.focus();
        }
    }

    const modalAcessoTesteCertificado = document.getElementById('modalAcessoTesteCertificado');

    if (modalAcessoTesteCertificado) {
        modalAcessoTesteCertificado.addEventListener('click', function(e) {
            if (e.target === this) {
                fecharAcessoTesteCertificado();
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            fecharAcessoTesteCertificado();
        }

        if (e.key === 'Enter') {
            const modal = document.getElementById('modalAcessoTesteCertificado');

            if (modal && !modal.classList.contains('hidden')) {
                validarAcessoTesteCertificado();
            }
        }
    });
</script>

@endsection