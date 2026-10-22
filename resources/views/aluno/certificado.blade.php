@extends('layout.app')

@section('title', 'Meu Certificado')

@section('content')

@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;

    $aluno = auth()->user();
    $alunoId = auth()->id();

    // Uma única fonte para autorização: a verificação do CertificadoController.
    // Não cadastrar emissão em uma view (GET não deve gravar certificados).
    $situacaoCertificado = \App\Http\Controllers\CertificadoController::verificarRequisitos($alunoId);
    $requisitos = $situacaoCertificado['requisitos'];
    $certificadoLiberado = (bool) $situacaoCertificado['liberado'];
    $totalCursos = (int) $situacaoCertificado['total_cursos'];
    $cursosConcluidos = (int) $situacaoCertificado['cursos_concluidos'];
    $totalAulas = (int) $situacaoCertificado['total_aulas'];
    $totalAulasAssistidas = (int) $situacaoCertificado['aulas_assistidas'];
    $totalPosTestes = (int) $situacaoCertificado['total_pos_testes'];
    $totalPosTestesFeitos = (int) $situacaoCertificado['pos_testes_feitos'];
    $mediaGeralPosTestes = (float) $situacaoCertificado['media_pos_testes'];
    $provaFinalFeita = (bool) $situacaoCertificado['prova_final_feita'];
    $notaFinal = $situacaoCertificado['nota_final'];
    $aprovadoNaFinal = (bool) $requisitos['aprovacao_final'];
    $aulasOk = (bool) $requisitos['aulas'];
    $postestesOk = (bool) $requisitos['pos_testes'];
    $cursosOk = (bool) $requisitos['cursos'];
    $formatarPercentual = fn ($valor) => number_format((float) ($valor ?? 0), 2, ',', '.') . '%';
    $notaFinalParaExibir = $notaFinal !== null ? $formatarPercentual($notaFinal) : 'Não realizada';

    $totalRequisitos = count($requisitos);
    $requisitosConcluidos = collect($requisitos)->filter()->count();
    $progresso = $totalRequisitos > 0
        ? (int) round($requisitosConcluidos / $totalRequisitos * 100) : 0;

    $modeloCertificado = Schema::hasTable('certificados')
        ? DB::table('certificados')->orderByDesc('created_at')->orderByDesc('id')->first()
        : null;
    $nomeCurso = $modeloCertificado->curso ?? 'Formação Integrar ReSaúde';
    $cargaHoraria = $modeloCertificado->carga_horaria ?? 40;
    $responsavel = $modeloCertificado->responsavel ?? 'Responsável pelo Curso';
    $cargo = $modeloCertificado->cargo ?? 'Coordenação do Curso';
    $assinatura = $modeloCertificado->assinatura ?? null;

    $certificadoEmitido = null;
    $codigoValidacaoCertificado = null;
    $dataEmissaoCertificado = now();
    if ($certificadoLiberado && $modeloCertificado && Schema::hasTable('certificados_emitidos')
        && Schema::hasColumn('certificados_emitidos', 'certificado_modelo_id')) {
        $certificadoEmitido = DB::table('certificados_emitidos')
            ->where('aluno_id', $alunoId)
            ->where('certificado_modelo_id', $modeloCertificado->id)->first();
        if ($certificadoEmitido) {
            $codigoValidacaoCertificado = $certificadoEmitido->codigo_validacao ?? $certificadoEmitido->codigo ?? null;
            $dataEmissaoCertificado = !empty($certificadoEmitido->data_emissao)
                ? \Carbon\Carbon::parse($certificadoEmitido->data_emissao) : now();
        }
    }
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

    /*
    |--------------------------------------------------------------------------
    | CERTIFICADO NA TELA
    |--------------------------------------------------------------------------
    */
    #areaCertificado {
        width: 850px;
        min-width: 850px;
        min-height: 590px;
        margin: 0 auto;
        background: #ffffff;
        border: 7px solid #EAF5EF;
        border-radius: 16px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
        position: relative;
        overflow: hidden;
    }

    /*
    |--------------------------------------------------------------------------
    | IMPRESSÃO DO CERTIFICADO
    |--------------------------------------------------------------------------
    | Corrigido para:
    | - imprimir apenas o certificado;
    | - ocupar uma única folha;
    | - não deixar a página em branco;
    | - remover sidebar, navbar, footer, botões e fundos da tela.
    */
    @page {
        size: A4 landscape;
        margin: 0;
    }

    @media print {
        html,
        body {
            width: 297mm !important;
            height: 210mm !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #ffffff !important;
            overflow: hidden !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        #app {
            width: 297mm !important;
            height: 210mm !important;
            min-height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #ffffff !important;
            overflow: hidden !important;
        }

        body * {
            visibility: hidden !important;
        }

        #areaCertificado,
        #areaCertificado * {
            visibility: visible !important;
        }

        #areaCertificado {
            display: block !important;
            position: fixed !important;
            left: 0 !important;
            top: 0 !important;
            width: 297mm !important;
            height: 210mm !important;
            min-width: 0 !important;
            min-height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #ffffff !important;
            border: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            overflow: hidden !important;
            page-break-before: avoid !important;
            page-break-after: avoid !important;
            page-break-inside: avoid !important;
            break-before: avoid !important;
            break-after: avoid !important;
            break-inside: avoid !important;
        }

        .nao-imprimir,
        .nao-imprimir *,
        aside,
        nav,
        header,
        footer,
        #toastContainer,
        #btnVoltarTopo {
            display: none !important;
            visibility: hidden !important;
        }

        .certificado-folha {
            display: block !important;
            position: relative !important;
            width: 287mm !important;
            height: 200mm !important;
            margin: 5mm !important;
            border: 2.5mm solid #EAF5EF !important;
            border-radius: 6mm !important;
            background: #ffffff !important;
            overflow: hidden !important;
            box-shadow: none !important;
        }

        .certificado-decoracao-superior {
            position: absolute !important;
            top: -34mm !important;
            right: -28mm !important;
            width: 64mm !important;
            height: 64mm !important;
            border-radius: 9999px !important;
            border: 9mm solid #EAF5EF !important;
            opacity: 0.9 !important;
        }

        .certificado-decoracao-inferior {
            position: absolute !important;
            bottom: -38mm !important;
            left: -32mm !important;
            width: 76mm !important;
            height: 76mm !important;
            border-radius: 9999px !important;
            border: 10mm solid #EAF5EF !important;
            opacity: 0.9 !important;
        }

        .certificado-conteudo {
            position: relative !important;
            z-index: 10 !important;
            width: 100% !important;
            height: 100% !important;
            padding: 13mm 22mm 10mm !important;
            text-align: center !important;
        }

        .certificado-marca {
            font-size: 10px !important;
            line-height: 1.2 !important;
            letter-spacing: 6px !important;
            margin: 0 0 8mm 0 !important;
        }

        .certificado-titulo {
            font-size: 34px !important;
            line-height: 1.15 !important;
            letter-spacing: 10px !important;
            margin: 0 !important;
        }

        .certificado-subtitulo {
            font-size: 12px !important;
            margin-top: 11mm !important;
        }

        .certificado-nome-box {
            width: 150mm !important;
            max-width: none !important;
            margin: 6mm auto 0 !important;
            padding-bottom: 3mm !important;
            border-bottom: 1.4px solid #BFD8C5 !important;
        }

        .certificado-nome {
            font-size: 21px !important;
            line-height: 1.2 !important;
        }

        .certificado-texto {
            max-width: 235mm !important;
            margin: 8mm auto 0 !important;
            font-size: 12px !important;
            line-height: 1.75 !important;
        }

        .certificado-texto-menor {
            max-width: 235mm !important;
            margin: 4mm auto 0 !important;
            font-size: 12px !important;
            line-height: 1.75 !important;
        }

        .certificado-rodape {
            position: absolute !important;
            left: 22mm !important;
            right: 22mm !important;
            bottom: 14mm !important;
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 36mm !important;
            align-items: end !important;
            margin: 0 !important;
        }

        .certificado-assinatura-espaco {
            height: 19mm !important;
        }

        .certificado-responsavel {
            font-size: 10px !important;
            line-height: 1.3 !important;
        }

        .certificado-cargo {
            font-size: 8px !important;
            margin-top: 1mm !important;
            line-height: 1.3 !important;
        }

        .certificado-dados p {
            font-size: 9px !important;
            line-height: 1.7 !important;
        }

        .certificado-data-label {
            font-size: 7px !important;
        }

        .certificado-data {
            font-size: 9px !important;
            margin-top: 1mm !important;
        }
    }
    /* Interface escura; o papel do certificado permanece branco inclusive no dark mode. */
    html.dark body, html.dark #app { background: #0B1220 !important; }
    html.dark #areaCertificado, html.dark #areaCertificado * { color-scheme: light; }
    html.dark #areaCertificado { background: #fff !important; }
</style>

<div class="flex min-h-screen w-full bg-[#F3F7F3] text-[#003C2F] overflow-x-hidden">

    @include('partials.sidebar-aluno')

    <main class="flex-1 min-w-0 w-full bg-[#F3F7F3] overflow-x-hidden">

        @include('partials.navbar')

        <section class="p-4 sm:p-6 lg:p-8">

            <!-- CABEÇALHO -->
            <div class="mb-7 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-5 nao-imprimir">

                <div>

                    <h1 class="text-3xl sm:text-4xl font-extrabold text-[#003C2F] tracking-tight">
                        Meu Certificado
                    </h1>

                    <p class="text-sm text-[#60756B] mt-2 max-w-2xl">
                        O certificado exige a conclusão de todos os cursos obrigatórios, incluindo aulas e pós-testes, média geral mínima de 70% nos pós-testes e aprovação com pelo menos 70% na prova final.
                    </p>
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
                                Para receber o certificado, conclua todos os cursos obrigatórios, assista a todas as aulas, realize todos os pós-testes e seja aprovado na prova final. A média geral dos pós-testes também deve alcançar 70%.
                            </p>

                            <div class="mt-6 bg-[#F8FBF8] border border-[#E3EBE4] rounded-3xl p-5">

                                <div class="flex items-center justify-between mb-3">
                                    <p class="text-sm font-extrabold text-[#003C2F]">
                                        Progresso dos cinco requisitos do certificado
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
                                            Aulas assistidas (cursos obrigatórios)
                                        </p>

                                        <p class="text-2xl font-extrabold mt-1 text-[#004D3A]">
                                            {{ $totalAulasAssistidas }} / {{ $totalAulas }}
                                        </p>
                                    </div>

                                    <div class="bg-white rounded-2xl border border-[#E3EBE4] p-4">
                                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                            Pós-testes feitos (cursos obrigatórios)
                                        </p>

                                        <p class="text-2xl font-extrabold mt-1 text-[#004D3A]">
                                            {{ $totalPosTestesFeitos }} / {{ $totalPosTestes }}
                                        </p>
                                    </div>

                                    <div class="bg-white rounded-2xl border border-[#E3EBE4] p-4">
                                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                            Cursos obrigatórios concluídos
                                        </p>
                                        <p class="text-2xl font-extrabold mt-1 {{ $cursosOk ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $cursosConcluidos }} / {{ $totalCursos }}
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
                                            {{ $notaFinalParaExibir }}
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
                                    <div class="w-9 h-9 rounded-xl {{ $requisitos['aulas'] ? 'bg-green-100 text-green-700' : 'bg-red-50 text-red-600' }} flex items-center justify-center shrink-0 font-bold">
                                        {{ $requisitos['aulas'] ? '✓' : '!' }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-[#003C2F]">Assistir todas as aulas</p>
                                        <p class="text-sm text-[#60756B]">{{ $totalAulasAssistidas }} de {{ $totalAulas }} aulas assistidas.</p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-xl {{ $requisitos['pos_testes'] ? 'bg-green-100 text-green-700' : 'bg-red-50 text-red-600' }} flex items-center justify-center shrink-0 font-bold">
                                        {{ $requisitos['pos_testes'] ? '✓' : '!' }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-[#003C2F]">Concluir todos os pós-testes</p>
                                        <p class="text-sm text-[#60756B]">{{ $totalPosTestesFeitos }} de {{ $totalPosTestes }} pós-testes realizados.</p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-xl {{ $requisitos['cursos'] ? 'bg-green-100 text-green-700' : 'bg-red-50 text-red-600' }} flex items-center justify-center shrink-0 font-bold">
                                        {{ $requisitos['cursos'] ? '✓' : '!' }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-[#003C2F]">Concluir todos os cursos obrigatórios</p>
                                        <p class="text-sm text-[#60756B]">{{ $cursosConcluidos }} de {{ $totalCursos }} cursos concluídos.</p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-xl {{ $requisitos['prova_final'] ? 'bg-green-100 text-green-700' : 'bg-red-50 text-red-600' }} flex items-center justify-center shrink-0 font-bold">
                                        {{ $requisitos['prova_final'] ? '✓' : '!' }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-[#003C2F]">Fazer prova final</p>
                                        <p class="text-sm text-[#60756B]">{{ $provaFinalFeita ? 'Prova realizada.' : 'Ainda não realizada.' }}</p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-xl {{ $requisitos['aprovacao_final'] ? 'bg-green-100 text-green-700' : 'bg-red-50 text-red-600' }} flex items-center justify-center shrink-0 font-bold">
                                        {{ $requisitos['aprovacao_final'] ? '✓' : '!' }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-[#003C2F]">Atingir 70% na prova final</p>
                                        <p class="text-sm text-[#60756B]">Nota atual: {{ $notaFinalParaExibir }}.</p>
                                    </div>
                                </div>

                                <p class="text-xs text-[#60756B]">Média geral dos pós-testes obrigatórios: <strong>{{ $formatarPercentual($mediaGeralPosTestes) }}</strong> (mínimo de 70% para acessar a prova final).</p>
                            </div>

                        </div>
                    </aside>

                </div>

            @else

                <!-- CERTIFICADO LIBERADO -->
                <div class="bg-white border border-[#E3EBE4] rounded-3xl shadow-sm p-5 sm:p-6 overflow-x-auto">

                    <div id="areaCertificado">

                        <div class="certificado-folha w-full h-full relative overflow-hidden">

                            <div class="certificado-decoracao-superior absolute -top-16 -right-16 w-52 h-52 rounded-full border-[30px] border-[#EAF5EF] opacity-80"></div>
                            <div class="certificado-decoracao-inferior absolute -bottom-20 -left-20 w-60 h-60 rounded-full border-[34px] border-[#EAF5EF] opacity-80"></div>

                            <div class="certificado-conteudo relative z-10 px-20 py-14 text-center">

                                <p class="certificado-marca text-sm font-extrabold text-[#60756B] tracking-[0.25em] uppercase">
                                    Integrar ReSaúde
                                </p>

                                <h1 class="certificado-titulo mt-7 text-4xl font-extrabold tracking-[0.22em] text-[#004D3A] leading-tight">
                                    CERTIFICADO DE<br>
                                    CONCLUSÃO
                                </h1>

                                <p class="certificado-subtitulo mt-8 text-sm text-[#374151]">
                                    Certificamos que
                                </p>

                                <div class="certificado-nome-box mt-5 mx-auto max-w-lg border-b-2 border-[#BFD8C5] pb-2">
                                    <p class="certificado-nome text-2xl font-extrabold text-[#1F2937] tracking-wide">
                                        {{ $aluno->name }}
                                    </p>
                                </div>

                                <p class="certificado-texto mt-8 text-sm text-[#4B5563] leading-relaxed max-w-2xl mx-auto">
                                    concluiu com aproveitamento a formação
                                    <strong>{{ $nomeCurso }}</strong>,
                                    com carga horária total de
                                    <strong>{{ $cargaHoraria }} horas</strong>.
                                </p>

                                <p class="certificado-texto-menor mt-3 text-sm text-[#4B5563] leading-relaxed max-w-2xl mx-auto">
                                    O aluno concluiu todos os cursos obrigatórios e obteve aproveitamento mínimo de 70% na prova final geral desta formação.
                                </p>

                                <div class="certificado-rodape grid grid-cols-2 gap-14 mt-16 items-end">

                                    <!-- ASSINATURA MANUAL -->
                                    <div>
                                        <div class="certificado-assinatura-espaco h-16 flex items-end justify-center">
                                            @if(!empty($assinatura))
                                                <img src="{{ asset('storage/' . $assinatura) }}"
                                                     alt="Assinatura"
                                                     class="max-h-16 max-w-[190px] object-contain">
                                            @else
                                                <span class="text-xs italic text-[#A5B7AB]">
                                                    Espaço para assinatura manual
                                                </span>
                                            @endif
                                        </div>

                                        <div class="border-t border-[#8A9B92] pt-2">
                                            <p class="certificado-responsavel text-xs font-bold text-[#374151] uppercase">
                                                {{ $responsavel }}
                                            </p>

                                            <p class="certificado-cargo text-[10px] text-[#60756B] mt-1 uppercase">
                                                {{ $cargo }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- DADOS -->
                                    <div>
                                        <div class="certificado-dados text-left inline-block">
                                            <p class="text-xs text-[#60756B]">
                                                CPF:
                                                <strong class="text-[#374151]">{{ $aluno->cpf }}</strong>
                                            </p>

                                            <p class="text-xs text-[#60756B] mt-2">
                                                Aproveitamento:
                                                <strong class="text-[#374151]">{{ $notaFinalParaExibir }}</strong>
                                            </p>

                                            <div class="border-t border-[#8A9B92] mt-6 pt-2">
                                                <p class="certificado-data-label text-[10px] text-[#60756B] uppercase">
                                                    Data de emissão
                                                </p>

                                                <p class="certificado-data text-xs font-bold text-[#374151] mt-1">
                                                    {{ $certificadoEmitido ? \Carbon\Carbon::parse($dataEmissaoCertificado)->format('d/m/Y') : 'Ainda não registrado' }}
                                                </p>
                                            </div>

                                            @if($codigoValidacaoCertificado)
                                                <p class="text-[10px] text-[#8A9B92] mt-4 break-all">
                                                    Código:
                                                    <strong class="text-[#60756B]">{{ $codigoValidacaoCertificado }}</strong>
                                                </p>
                                            @endif
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

@endsection