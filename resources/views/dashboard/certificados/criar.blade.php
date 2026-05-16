@extends('layout.app')

@section('title', 'Criar Certificado')

@section('content')

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
    | IMPRESSÃO DO PREVIEW DO CERTIFICADO
    |--------------------------------------------------------------------------
    | Quando clicar em imprimir nesta tela, imprime apenas o certificado em
    | uma folha A4 paisagem.
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

        body * {
            visibility: hidden !important;
        }

        .certificado-preview-print,
        .certificado-preview-print * {
            visibility: visible !important;
        }

        .certificado-preview-print {
            position: fixed !important;
            inset: 0 !important;
            width: 297mm !important;
            height: 210mm !important;
            margin: 0 !important;
            padding: 5mm !important;
            background: #ffffff !important;
            overflow: hidden !important;
            page-break-after: avoid !important;
            page-break-before: avoid !important;
            page-break-inside: avoid !important;
            break-after: avoid !important;
            break-before: avoid !important;
            break-inside: avoid !important;
        }

        .certificado-preview-print > div {
            width: 287mm !important;
            height: 200mm !important;
            min-width: 0 !important;
            min-height: 0 !important;
            margin: 0 !important;
            transform: none !important;
            box-shadow: none !important;
        }
    }

</style>

<div class="flex min-h-screen w-full bg-[#F3F7F3] text-[#003C2F] overflow-x-hidden">

    @include('partials.sidebar-professor')

    <main class="flex-1 min-w-0 w-full bg-[#F3F7F3] overflow-x-hidden">

        @include('partials.navbar')

        <section class="p-4 sm:p-6 lg:p-8">

            <!-- CABEÇALHO -->
            <div class="mb-7">

                <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-5">

                    <div>

                        <h1 class="text-3xl sm:text-4xl font-extrabold text-[#003C2F] tracking-tight">
                            Certificados
                        </h1>

                        <p class="text-sm text-[#60756B] mt-2 max-w-2xl">
                            Configure o modelo de certificado do curso e acompanhe os certificados emitidos.
                        </p>
                    </div>

                </div>

            </div>

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

            @if($errors->any())
                <div class="mb-5 bg-red-100 text-red-700 px-4 py-3 rounded-2xl border border-red-200 shadow-sm">
                    <p class="font-bold mb-2">Corrija os campos abaixo:</p>

                    <ul class="list-disc pl-5 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- ÁREA SUPERIOR -->
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-7 mb-8">

                <!-- CONFIGURAÇÕES -->
                <div class="xl:col-span-4">

                    <div class="bg-white border border-[#E3EBE4] rounded-3xl shadow-sm p-5 sm:p-6 min-h-[420px]">

                        <div class="flex items-center gap-3 mb-8">

                            <div class="w-12 h-12 rounded-2xl bg-[#EAF5EF] text-[#004D3A] flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-6 h-6"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="1.8"
                                          d="M10.5 6h9.75M10.5 12h9.75M10.5 18h9.75M3.75 6h.008v.008H3.75V6zm0 6h.008v.008H3.75V12zm0 6h.008v.008H3.75V18z"/>
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-xl font-extrabold text-[#003C2F] leading-tight">
                                    Configurações de Emissão
                                </h2>

                                <p class="text-xs text-[#60756B] mt-1">
                                    Dados usados no certificado.
                                </p>
                            </div>

                        </div>

                        <form action="{{ route('certificados.store') }}" method="POST">
                            @csrf

                            <!-- NOME DO CURSO -->
                            <div class="mb-5">
                                <label class="block text-[11px] uppercase tracking-widest font-extrabold text-[#60756B] mb-2">
                                    Nome do curso
                                </label>

                                <input type="text"
                                       name="curso"
                                       id="cursoInput"
                                       value="{{ old('curso') }}"
                                       oninput="atualizarPreviewCertificado()"
                                       placeholder="Ex: Integrar ReSaúde"
                                       class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-bold placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition">
                            </div>

                            <!-- CARGA HORÁRIA -->
                            <div class="mb-5">
                                <label class="block text-[11px] uppercase tracking-widest font-extrabold text-[#60756B] mb-2">
                                    Carga horária padrão
                                </label>

                                <div class="relative">
                                    <input type="number"
                                           name="carga_horaria"
                                           id="cargaInput"
                                           value="{{ old('carga_horaria', 40) }}"
                                           oninput="atualizarPreviewCertificado()"
                                           placeholder="Ex: 40"
                                           class="w-full px-4 py-3 pr-12 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-bold placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition">

                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[#8A9B92] text-sm font-bold">
                                        h
                                    </span>
                                </div>
                            </div>

                            <!-- NOME DO RESPONSÁVEL -->
                            <div class="mb-5">
                                <label class="block text-[11px] uppercase tracking-widest font-extrabold text-[#60756B] mb-2">
                                    Nome do responsável
                                </label>

                                <input type="text"
                                       name="responsavel"
                                       id="responsavelInput"
                                       value="{{ old('responsavel') }}"
                                       oninput="atualizarPreviewCertificado()"
                                       placeholder="Ex: Diretora Acadêmica"
                                       class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-bold placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition">
                            </div>

                            <!-- CARGO -->
                            <div class="mb-8">
                                <label class="block text-[11px] uppercase tracking-widest font-extrabold text-[#60756B] mb-2">
                                    Cargo
                                </label>

                                <input type="text"
                                       name="cargo"
                                       id="cargoInput"
                                       value="{{ old('cargo') }}"
                                       oninput="atualizarPreviewCertificado()"
                                       placeholder="Ex: Coordenação do Curso"
                                       class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-bold placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition">
                            </div>

                            <!-- AVISO ASSINATURA -->
                            <div class="mb-6 bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-4">
                                <div class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-[#EAF5EF] text-[#004D3A] flex items-center justify-center shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="w-5 h-5"
                                             fill="none"
                                             viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="1.8"
                                                  d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                                        </svg>
                                    </div>

                                    <div>
                                        <p class="text-sm font-extrabold text-[#003C2F]">
                                            Assinatura manual
                                        </p>

                                        <p class="text-xs text-[#60756B] mt-1 leading-relaxed">
                                            O certificado terá um espaço em branco para a responsável assinar manualmente após a impressão.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- BOTÃO -->
                            <button type="submit"
                                    class="w-full bg-[#004D3A] hover:bg-[#003C2F] text-white px-6 py-4 rounded-2xl font-extrabold transition shadow-lg flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-5 h-5"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="1.8"
                                          d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                                </svg>

                                Atualizar Certificado
                            </button>

                        </form>

                    </div>

                </div>

                <!-- TEMPLATE / PREVIEW -->
                <div class="xl:col-span-8">

                    <div class="bg-white border border-[#E3EBE4] rounded-3xl shadow-sm p-5 sm:p-6">

                        <div class="flex items-center justify-between gap-4 mb-6">

                            <div class="flex items-center gap-3">

                                <div class="w-12 h-12 rounded-2xl bg-[#EAF5EF] text-[#004D3A] flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="w-6 h-6"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="1.8"
                                              d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 15.75A2.25 2.25 0 0 1 15.75 13.5H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/>
                                    </svg>
                                </div>

                                <div>
                                    <h2 class="text-xl font-extrabold text-[#003C2F]">
                                        Template de Certificado
                                    </h2>

                                    <p class="text-xs text-[#60756B] mt-1">
                                        Prévia visual do certificado impresso.
                                    </p>
                                </div>

                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button"
                                        class="w-10 h-10 rounded-xl hover:bg-[#F1F6F2] text-[#004D3A] transition flex items-center justify-center"
                                        title="Editar modelo">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="w-5 h-5"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="1.8"
                                              d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931z"/>
                                    </svg>
                                </button>

                                <button type="button"
                                        onclick="window.print()"
                                        class="w-10 h-10 rounded-xl hover:bg-[#F1F6F2] text-[#004D3A] transition flex items-center justify-center"
                                        title="Imprimir">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="w-5 h-5"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="1.8"
                                              d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231a1.125 1.125 0 0 1-1.12-1.227L6.34 18m11.318 0H6.34M6 13.5V6a2.25 2.25 0 0 1 2.25-2.25h7.5A2.25 2.25 0 0 1 18 6v7.5"/>
                                    </svg>
                                </button>
                            </div>

                        </div>

                        <!-- CERTIFICADO PREVIEW -->
                        <div class="certificado-preview-print bg-[#F8FBF8] rounded-3xl p-4 sm:p-6 shadow-inner overflow-x-auto">

                            <div class="min-w-[720px] bg-white mx-auto rounded-2xl border-[6px] border-[#EAF5EF] shadow-sm relative overflow-hidden"
                                 style="width: 780px; min-height: 520px;">

                                <!-- DECORAÇÕES -->
                                <div class="absolute -top-16 -right-16 w-48 h-48 rounded-full border-[28px] border-[#EAF5EF] opacity-80"></div>
                                <div class="absolute -bottom-20 -left-20 w-56 h-56 rounded-full border-[34px] border-[#EAF5EF] opacity-80"></div>

                                <div class="relative z-10 px-16 py-12 text-center">

                                    <!-- LOGO / NOME -->
                                    <div class="mb-6">
                                        <p class="text-sm font-extrabold text-[#60756B] tracking-[0.25em] uppercase">
                                            Integrar ReSaúde
                                        </p>
                                    </div>

                                    <!-- TÍTULO -->
                                    <h1 class="text-4xl font-extrabold tracking-[0.22em] text-[#004D3A] leading-tight">
                                        CERTIFICADO DE<br>
                                        CONCLUSÃO
                                    </h1>

                                    <p class="mt-8 text-sm text-[#374151]">
                                        Certificamos que
                                    </p>

                                    <!-- NOME ALUNO -->
                                    <div class="mt-5 mx-auto max-w-lg border-b-2 border-[#BFD8C5] pb-2">
                                        <p class="text-2xl font-extrabold text-[#1F2937] tracking-wide">
                                            [NOME DO ALUNO]
                                        </p>
                                    </div>

                                    <!-- TEXTO -->
                                    <p class="mt-8 text-sm text-[#4B5563] leading-relaxed max-w-2xl mx-auto">
                                        concluiu com aproveitamento o curso
                                        <strong id="previewCurso">[NOME DO CURSO]</strong>,
                                        com carga horária total de
                                        <strong id="previewCarga">40 horas</strong>.
                                    </p>

                                    <p class="mt-3 text-sm text-[#4B5563] leading-relaxed max-w-2xl mx-auto">
                                        Certificação emitida oficialmente pelo programa Integrar ReSaúde.
                                    </p>

                                    <!-- ASSINATURAS -->
                                    <div class="grid grid-cols-2 gap-14 mt-16 items-end">

                                        <!-- ASSINATURA MANUAL -->
                                        <div>
                                            <div class="h-16 flex items-end justify-center">
                                                <span class="text-xs italic text-[#A5B7AB]">
                                                    Espaço para assinatura manual
                                                </span>
                                            </div>

                                            <div class="border-t border-[#8A9B92] pt-2">
                                                <p id="previewResponsavel" class="text-xs font-bold text-[#374151] uppercase">
                                                    RESPONSÁVEL PELO CURSO
                                                </p>

                                                <p id="previewCargo" class="text-[10px] text-[#60756B] mt-1 uppercase">
                                                    CARGO / FUNÇÃO
                                                </p>
                                            </div>
                                        </div>

                                        <!-- DADOS -->
                                        <div>
                                            <div class="text-left inline-block">
                                                <p class="text-xs text-[#60756B]">
                                                    ID:
                                                    <strong class="text-[#374151]">000-AD-2026</strong>
                                                </p>

                                                <div class="border-t border-[#8A9B92] mt-6 pt-2">
                                                    <p class="text-[10px] text-[#60756B] uppercase">
                                                        Data de emissão
                                                    </p>

                                                    <p class="text-xs font-bold text-[#374151] mt-1">
                                                        ____ / ____ / ______
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <!-- CERTIFICADOS EMITIDOS -->
            <div class="bg-white border border-[#E3EBE4] rounded-3xl shadow-sm overflow-hidden">

                <div class="p-5 sm:p-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

                    <div class="flex items-center gap-3">

                        <div class="w-12 h-12 rounded-2xl bg-[#EAF5EF] text-[#004D3A] flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-6 h-6"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-xl font-extrabold text-[#003C2F]">
                                Certificados Emitidos
                            </h2>

                            <p class="text-xs text-[#60756B] mt-1">
                                Histórico de certificados gerados pelo sistema.
                            </p>
                        </div>

                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <button type="button"
                                class="bg-[#F1F6F2] border border-[#DCE7DE] text-[#004D3A] px-5 py-3 rounded-2xl hover:bg-[#E6EFE8] transition text-sm font-bold flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-4 h-4"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M10.5 6h9.75M10.5 12h9.75M10.5 18h9.75M3.75 6h.008v.008H3.75V6zm0 6h.008v.008H3.75V12zm0 6h.008v.008H3.75V18z"/>
                            </svg>

                            Filtrar
                        </button>

                        <button type="button"
                                class="bg-[#F1F6F2] border border-[#DCE7DE] text-[#004D3A] px-5 py-3 rounded-2xl hover:bg-[#E6EFE8] transition text-sm font-bold flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-4 h-4"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M7.5 12 12 16.5m0 0L16.5 12M12 16.5V3"/>
                            </svg>

                            Exportar CSV
                        </button>
                    </div>

                </div>

                <!-- DESKTOP TABLE -->
                <div class="hidden xl:block overflow-x-auto">

                    <table class="w-full text-sm">

                        <thead>
                            <tr class="bg-[#F8FBF8] text-left text-[11px] uppercase tracking-widest text-[#60756B] border-y border-[#E3EBE4]">
                                <th class="py-5 px-6">Nome do aluno</th>
                                <th class="py-5 px-6">CPF</th>
                                <th class="py-5 px-6">Data de emissão</th>
                                <th class="py-5 px-6">Curso/Aula</th>
                                <th class="py-5 px-6 text-right">Ações</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-[#EEF3EF]">

                            @forelse(($certificados ?? []) as $certificado)

                                <tr class="hover:bg-[#F8FBF8] transition">

                                    <td class="py-5 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-[#EAF5EF] text-[#004D3A] flex items-center justify-center font-extrabold">
                                                {{ strtoupper(substr($certificado->aluno_nome ?? 'A', 0, 1)) }}
                                            </div>

                                            <div>
                                                <p class="font-extrabold text-[#003C2F]">
                                                    {{ $certificado->aluno_nome ?? 'Aluno' }}
                                                </p>

                                                <p class="text-xs text-[#60756B]">
                                                    {{ $certificado->email ?? '' }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="py-5 px-6 text-[#4B5C52]">
                                        {{ $certificado->cpf ?? '---' }}
                                    </td>

                                    <td class="py-5 px-6 text-[#4B5C52]">
                                        {{ isset($certificado->created_at) ? \Carbon\Carbon::parse($certificado->created_at)->format('d/m/Y') : '---' }}
                                    </td>

                                    <td class="py-5 px-6">
                                        <span class="inline-flex bg-green-100 text-green-700 px-3 py-1 rounded-full text-[11px] font-extrabold">
                                            {{ $certificado->curso ?? 'Curso' }}
                                        </span>
                                    </td>

                                    <td class="py-5 px-6">
                                        <div class="flex justify-end gap-2">

                                            <button class="w-9 h-9 rounded-xl hover:bg-[#EAF5EF] text-[#004D3A] transition flex items-center justify-center"
                                                    title="Visualizar">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                     class="w-5 h-5"
                                                     fill="none"
                                                     viewBox="0 0 24 24"
                                                     stroke="currentColor">
                                                    <path stroke-linecap="round"
                                                          stroke-linejoin="round"
                                                          stroke-width="1.8"
                                                          d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                                    <path stroke-linecap="round"
                                                          stroke-linejoin="round"
                                                          stroke-width="1.8"
                                                          d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                                </svg>
                                            </button>

                                            <button class="w-9 h-9 rounded-xl hover:bg-[#EAF5EF] text-[#004D3A] transition flex items-center justify-center"
                                                    title="Reemitir">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                     class="w-5 h-5"
                                                     fill="none"
                                                     viewBox="0 0 24 24"
                                                     stroke="currentColor">
                                                    <path stroke-linecap="round"
                                                          stroke-linejoin="round"
                                                          stroke-width="1.8"
                                                          d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.992 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M7.977 14.652H2.985m18.03-9.296v4.992m0 0h-4.992m4.992 0-3.181-3.183a8.25 8.25 0 0 0-13.803 3.7"/>
                                                </svg>
                                            </button>

                                        </div>
                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="5" class="py-10 px-6 text-center text-[#60756B]">
                                        Nenhum certificado emitido ainda.
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

                <!-- MOBILE CARDS -->
                <div class="xl:hidden p-4 space-y-4">

                    @forelse(($certificados ?? []) as $certificado)

                        <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-3xl p-5">

                            <div class="flex items-start gap-3 mb-4">
                                <div class="w-11 h-11 rounded-full bg-[#EAF5EF] text-[#004D3A] flex items-center justify-center font-extrabold">
                                    {{ strtoupper(substr($certificado->aluno_nome ?? 'A', 0, 1)) }}
                                </div>

                                <div>
                                    <p class="font-extrabold text-[#003C2F]">
                                        {{ $certificado->aluno_nome ?? 'Aluno' }}
                                    </p>

                                    <p class="text-xs text-[#60756B]">
                                        {{ $certificado->email ?? '' }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-2 text-sm text-[#60756B]">
                                <p><strong>CPF:</strong> {{ $certificado->cpf ?? '---' }}</p>
                                <p><strong>Data:</strong> {{ isset($certificado->created_at) ? \Carbon\Carbon::parse($certificado->created_at)->format('d/m/Y') : '---' }}</p>
                                <p><strong>Curso:</strong> {{ $certificado->curso ?? 'Curso' }}</p>
                            </div>

                        </div>

                    @empty

                        <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-3xl p-8 text-center text-[#60756B]">
                            Nenhum certificado emitido ainda.
                        </div>

                    @endforelse

                </div>

                <div class="bg-[#F8FBF8] border-t border-[#E3EBE4] px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 text-xs text-[#60756B]">

                    <p>
                        Mostrando
                        <strong>1-{{ ($certificados ?? collect())->count() }}</strong>
                        de
                        <strong>{{ ($certificados ?? collect())->count() }}</strong>
                        certificados emitidos
                    </p>

                    <div class="flex items-center gap-2">
                        <button class="w-9 h-9 rounded-xl bg-white border border-[#E3EBE4] text-[#60756B] flex items-center justify-center">
                            ‹
                        </button>

                        <button class="w-9 h-9 rounded-xl bg-[#004D3A] text-white flex items-center justify-center">
                            1
                        </button>

                        <button class="w-9 h-9 rounded-xl bg-white border border-[#E3EBE4] text-[#60756B] flex items-center justify-center">
                            2
                        </button>

                        <button class="w-9 h-9 rounded-xl bg-white border border-[#E3EBE4] text-[#60756B] flex items-center justify-center">
                            ›
                        </button>
                    </div>

                </div>

            </div>

        </section>

    </main>

</div>

<script>
    function atualizarPreviewCertificado() {
        const curso = document.getElementById('cursoInput')?.value || '[NOME DO CURSO]';
        const carga = document.getElementById('cargaInput')?.value || '40';
        const responsavel = document.getElementById('responsavelInput')?.value || 'RESPONSÁVEL PELO CURSO';
        const cargo = document.getElementById('cargoInput')?.value || 'CARGO / FUNÇÃO';

        const previewCurso = document.getElementById('previewCurso');
        const previewCarga = document.getElementById('previewCarga');
        const previewResponsavel = document.getElementById('previewResponsavel');
        const previewCargo = document.getElementById('previewCargo');

        if (previewCurso) previewCurso.innerText = curso;
        if (previewCarga) previewCarga.innerText = carga + ' horas';
        if (previewResponsavel) previewResponsavel.innerText = responsavel;
        if (previewCargo) previewCargo.innerText = cargo;
    }

    document.addEventListener('DOMContentLoaded', atualizarPreviewCertificado);
</script>

@endsection