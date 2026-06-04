<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 297mm;
            height: 210mm;
            margin: 0;
            padding: 0;
            background: #ffffff;
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #173F36;
            overflow: hidden;
        }

        .pagina-certificado {
            width: 297mm;
            height: 210mm;
            margin: 0;
            padding: 0;
            background: #ffffff;
            position: relative;
            overflow: hidden;
            page-break-after: avoid;
            page-break-before: avoid;
            page-break-inside: avoid;
        }

        .certificado {
            width: 287mm;
            height: 200mm;
            margin: 5mm;
            border: 2.5mm solid #EAF5EF;
            border-radius: 6mm;
            position: relative;
            overflow: hidden;
            text-align: center;
            padding: 13mm 22mm 10mm;
        }

        .decoracao-superior {
            position: absolute;
            top: -34mm;
            right: -28mm;
            width: 64mm;
            height: 64mm;
            border: 9mm solid #EAF5EF;
            border-radius: 50%;
            opacity: 0.95;
        }

        .decoracao-inferior {
            position: absolute;
            bottom: -38mm;
            left: -32mm;
            width: 76mm;
            height: 76mm;
            border: 10mm solid #EAF5EF;
            border-radius: 50%;
            opacity: 0.95;
        }

        .conteudo {
            position: relative;
            z-index: 2;
            height: 100%;
        }

        .marca {
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 6px;
            text-transform: uppercase;
            color: #60756B;
            margin-bottom: 8mm;
        }

        .titulo {
            font-size: 34px;
            line-height: 1.15;
            font-weight: 900;
            letter-spacing: 10px;
            color: #004D3A;
            text-transform: uppercase;
            margin: 0;
        }

        .subtitulo {
            font-size: 12px;
            color: #374151;
            margin-top: 11mm;
        }

        .nome {
            width: 150mm;
            margin: 6mm auto 0;
            padding-bottom: 3mm;
            border-bottom: 1.4px solid #BFD8C5;
            font-size: 21px;
            font-weight: 900;
            color: #1F2937;
            line-height: 1.2;
        }

        .texto {
            max-width: 235mm;
            margin: 8mm auto 0;
            font-size: 12px;
            line-height: 1.75;
            color: #4B5563;
        }

        .texto strong {
            color: #173F36;
        }

        .area-rodape {
            position: absolute;
            left: 22mm;
            right: 22mm;
            bottom: 14mm;
            display: table;
            width: calc(100% - 44mm);
        }

        .assinatura,
        .dados {
            display: table-cell;
            width: 50%;
            vertical-align: bottom;
        }

        .assinatura {
            text-align: center;
            padding-right: 18mm;
        }

        .dados {
            text-align: left;
            padding-left: 18mm;
        }

        .espaco-assinatura {
            height: 19mm;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            color: #A5B7AB;
            font-size: 10px;
            font-style: italic;
        }

        .assinatura-img {
            max-height: 18mm;
            max-width: 48mm;
            object-fit: contain;
        }

        .linha {
            border-top: 1px solid #8A9B92;
            padding-top: 2mm;
            margin-top: 1mm;
        }

        .responsavel {
            font-size: 10px;
            font-weight: bold;
            color: #374151;
            text-transform: uppercase;
            line-height: 1.3;
        }

        .cargo {
            font-size: 8px;
            color: #60756B;
            text-transform: uppercase;
            margin-top: 1mm;
            line-height: 1.3;
        }

        .info {
            font-size: 9px;
            color: #60756B;
            margin-bottom: 4mm;
            line-height: 1.7;
        }

        .info strong {
            color: #374151;
        }

        .data-box {
            border-top: 1px solid #8A9B92;
            padding-top: 2mm;
            margin-top: 3mm;
            width: 42mm;
        }

        .data-label {
            font-size: 7px;
            text-transform: uppercase;
            color: #60756B;
        }

        .data {
            font-size: 9px;
            font-weight: bold;
            color: #374151;
            margin-top: 1mm;
        }

        .codigo {
            position: absolute;
            bottom: 5mm;
            left: 0;
            right: 0;
            font-size: 7px;
            color: #9CA3AF;
            text-align: center;
        }

        @media print {
            html,
            body {
                width: 297mm !important;
                height: 210mm !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .pagina-certificado {
                width: 297mm !important;
                height: 210mm !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden !important;
                page-break-after: avoid !important;
                page-break-before: avoid !important;
                page-break-inside: avoid !important;
                break-after: avoid !important;
                break-before: avoid !important;
                break-inside: avoid !important;
            }
        }
    </style>
</head>

@php
    /*
    |--------------------------------------------------------------------------
    | CORREÇÃO DO APROVEITAMENTO NO PDF
    |--------------------------------------------------------------------------
    | Se chegar 10, entende como nota 10/10 e mostra 100,00%.
    | Se chegar 70, 80, 100 ou "70%", mantém como porcentagem.
    */
    $aproveitamentoBruto = $aproveitamento ?? '70%';

    if (is_numeric($aproveitamentoBruto)) {
        $aproveitamentoNumero = (float) $aproveitamentoBruto;
    } else {
        $aproveitamentoLimpo = preg_replace('/[^0-9,\.]/', '', (string) $aproveitamentoBruto);
        $aproveitamentoNumero = (float) str_replace(',', '.', $aproveitamentoLimpo);
    }

    if ($aproveitamentoNumero <= 10) {
        $aproveitamentoNumero = $aproveitamentoNumero * 10;
    }

    $aproveitamentoFormatado = number_format($aproveitamentoNumero, 2, ',', '.') . '%';
@endphp

<body>

    <div class="pagina-certificado">

        <div class="certificado">

            <div class="decoracao-superior"></div>
            <div class="decoracao-inferior"></div>

            <div class="conteudo">

                <div class="marca">
                    Integrar ReSaúde
                </div>

                <h1 class="titulo">
                    Certificado de<br>
                    Conclusão
                </h1>

                <div class="subtitulo">
                    Certificamos que
                </div>

                <div class="nome">
                    {{ $nome_aluno }}
                </div>

                <div class="texto">
                    concluiu com aproveitamento o curso
                    <strong>{{ $curso }}</strong>,
                    com carga horária total de
                    <strong>{{ $carga_horaria }} horas</strong>.
                </div>

                <div class="texto" style="margin-top: 4mm;">
                    O aluno cumpriu todos os requisitos obrigatórios e obteve aproveitamento mínimo de 70%.
                </div>

                <div class="area-rodape">

                    <div class="assinatura">
                        <div class="espaco-assinatura">
                            @if(!empty($assinatura))
                                <img src="{{ public_path('storage/' . $assinatura) }}" class="assinatura-img">
                            @else
                                Espaço para assinatura manual
                            @endif
                        </div>

                        <div class="linha">
                            <div class="responsavel">
                                {{ $responsavel }}
                            </div>

                            <div class="cargo">
                                {{ $cargo }}
                            </div>
                        </div>
                    </div>

                    <div class="dados">
                        <div class="info">
                            CPF:
                            <strong>{{ $cpf ?? '---' }}</strong>
                            <br>
                            Aproveitamento:
                            <strong>{{ $aproveitamentoFormatado }}</strong>
                        </div>

                        <div class="data-box">
                            <div class="data-label">
                                Data de emissão
                            </div>

                            <div class="data">
                                {{ $data_conclusao }}
                            </div>
                        </div>
                    </div>

                </div>

                <div class="codigo">
                    Código de validação: {{ $codigo }}
                </div>

            </div>

        </div>

    </div>

</body>
</html>
