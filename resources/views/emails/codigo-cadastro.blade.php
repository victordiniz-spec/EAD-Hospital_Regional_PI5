<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Código de Verificação</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f3f7f3; padding:30px; color:#1f2937;">

    <div style="max-width:520px; margin:0 auto; background:white; border-radius:18px; padding:30px; border:1px solid #e5e7eb;">

        <h2 style="color:#047857; margin-top:0;">
            Integrar ReSaúde
        </h2>

        <p>Olá, {{ $nome }}!</p>

        <p>
            Recebemos uma solicitação de cadastro usando este e-mail.
            Para confirmar que o e-mail é seu, digite o código abaixo na tela de cadastro:
        </p>

        <div style="background:#ecfdf5; border:1px solid #bbf7d0; color:#047857; font-size:34px; font-weight:bold; letter-spacing:8px; text-align:center; padding:20px; border-radius:14px; margin:25px 0;">
            {{ $codigo }}
        </div>

        <p style="font-size:14px; color:#6b7280;">
            Este código expira em 15 minutos.
        </p>

        <p style="font-size:14px; color:#6b7280;">
            Se você não solicitou este cadastro, ignore este e-mail.
        </p>

    </div>

</body>
</html>