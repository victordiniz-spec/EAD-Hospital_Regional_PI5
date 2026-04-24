<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            text-align: center;
            padding: 40px;
            border: 10px solid #16a34a;
        }

        .titulo {
            font-size: 40px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .subtitulo {
            font-size: 18px;
            margin-bottom: 30px;
        }

        .nome {
            font-size: 32px;
            font-weight: bold;
            margin: 20px 0;
        }

        .texto {
            font-size: 16px;
            margin: 20px 0;
        }

        .rodape {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }

        .assinatura {
            text-align: center;
        }

        .linha {
            width: 200px;
            border-top: 1px solid #000;
            margin: 10px auto;
        }

        .codigo {
            margin-top: 30px;
            font-size: 12px;
            color: gray;
        }
    </style>
</head>

<body>

    <div class="titulo">CERTIFICADO</div>

    <div class="subtitulo">
        Certificamos que
    </div>

    <div class="nome">
        {{ $nome_aluno }}
    </div>

    <div class="texto">
        concluiu com sucesso o curso
    </div>

    <div class="nome">
        {{ $curso }}
    </div>

    <div class="texto">
        com carga horária de <strong>{{ $carga_horaria }} horas</strong>.
    </div>

    <div class="texto">
        Concluído em {{ $data_conclusao }}
    </div>

    <div class="rodape">

        <!-- ASSINATURA -->
        <div class="assinatura">
            @if($assinatura)
                <img src="{{ public_path('storage/' . $assinatura) }}" width="120">
            @endif

            <div class="linha"></div>
            <div>{{ $responsavel }}</div>
            <div>{{ $cargo }}</div>
        </div>

    </div>

    <div class="codigo">
        Código de validação: {{ $codigo }}
    </div>

</body>
</html>