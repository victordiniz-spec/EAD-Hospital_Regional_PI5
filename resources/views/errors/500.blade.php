<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro 500 - Erro interno do servidor</title>
    @vite('resources/css/app.css')

    <style>
        body{margin:0;font-family:Inter,Arial,sans-serif;background:#071712;color:#ecfff7}
        .page{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;background:
            radial-gradient(circle at 20% 20%, rgba(255,200,87,.1), transparent 25%),
            radial-gradient(circle at 80% 10%, rgba(22,208,135,.08), transparent 20%),
            linear-gradient(135deg,#04110d,#071712)}
        .box{max-width:1100px;width:100%;display:grid;grid-template-columns:1fr 1fr;gap:24px}
        .card{background:rgba(9,30,23,.84);border:1px solid rgba(80,120,100,.22);border-radius:28px;box-shadow:0 20px 50px rgba(0,0,0,.3);overflow:hidden}
        .left{padding:42px}
        .code{font-size:140px;font-weight:900;line-height:.9;color:#ffc857}
        .title{font-size:40px;font-weight:900;margin-top:8px}
        .desc{margin-top:18px;color:#a9c7bc;line-height:1.9;font-size:17px}
        .actions{display:flex;gap:14px;flex-wrap:wrap;margin-top:28px}
        .btn{padding:15px 22px;border-radius:18px;text-decoration:none;font-weight:800;border:none;cursor:pointer}
        .btn-primary{background:#ffc857;color:#132015}
        .btn-secondary{background:rgba(255,255,255,.05);color:#fff;border:1px solid rgba(255,255,255,.08)}
        .scene{min-height:620px;position:relative;background:radial-gradient(circle at center, rgba(255,200,87,.08), transparent 35%)}
        .monitor{position:absolute;left:50%;top:120px;transform:translateX(-50%);width:280px;height:220px;border-radius:28px;background:#10231c;border:1px solid rgba(255,200,87,.25);display:flex;align-items:center;justify-content:center;flex-direction:column}
        .monitor .n{font-size:74px;font-weight:900;color:#ffc857}
        .monitor .t{font-weight:800}
        .cat,.dog{position:absolute;bottom:90px;width:220px}
        .cat{left:40px}
        .dog{right:40px}
        .bubble{position:absolute;background:rgba(8,25,20,.95);border:1px solid rgba(255,255,255,.08);border-radius:24px;padding:14px 16px;max-width:220px}
        .bubble strong{display:block;line-height:1.5}
        .bubble small{display:block;margin-top:4px;color:#a3c8bb}
        .b1{left:30px;top:90px}
        .b2{right:30px;top:140px}
        .floor{position:absolute;left:0;right:0;bottom:0;height:170px;background:linear-gradient(180deg,#11241d,#081510)}
        .subtitle{position:absolute;left:20px;right:20px;bottom:20px;background:rgba(8,25,20,.92);padding:16px 18px;border-radius:20px;text-align:center;border:1px solid rgba(255,255,255,.08)}
        .subtitle span{display:block;color:#a5c9bd;margin-top:4px}
        @media(max-width:1000px){.box{grid-template-columns:1fr}}
    </style>
</head>
<body>
<div class="page">
    <div class="box">
        <section class="card left">
            <div class="code">500</div>
            <div class="title">Erro interno do servidor</div>
            <div class="desc">
                O gato enfermeiro puxou o cabo de rede na hora errada,
                o cachorro enfermeiro saiu correndo atrás da internet,
                e o servidor entrou em estado crítico.
                Tente novamente em alguns instantes.
            </div>

            <div class="actions">
                <a href="{{ url('/') }}" class="btn btn-primary">Voltar ao início</a>
                <button onclick="history.back()" class="btn btn-secondary" type="button">Voltar</button>
            </div>
        </section>

        <section class="card scene">
            <div class="bubble b1">
                <strong>Ops... acho que puxei cedo demais!</strong>
                <small>Gato enfermeiro em pânico.</small>
            </div>

            <div class="bubble b2">
                <strong>Agora caiu tudo?!</strong>
                <small>Cachorro enfermeiro sem internet.</small>
            </div>

            <div class="monitor">
                <div class="n">500</div>
                <div class="t">Falha crítica do sistema</div>
            </div>

            <div class="cat">
                <svg viewBox="0 0 220 240" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="110" cy="62" r="42" fill="#F59E0B"/>
                    <rect x="60" y="82" width="100" height="90" rx="28" fill="#fff" stroke="#1e4f40" stroke-width="3"/>
                    <ellipse cx="110" cy="76" rx="24" ry="16" fill="#fff2df"/>
                    <circle cx="98" cy="62" r="4" fill="#172A24"/>
                    <circle cx="122" cy="62" r="4" fill="#172A24"/>
                    <path d="M102 84 Q110 92 118 84" stroke="#7C2D12" stroke-width="3" fill="none"/>
                </svg>
            </div>

            <div class="dog">
                <svg viewBox="0 0 220 240" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="110" cy="62" r="42" fill="#C78A57"/>
                    <rect x="60" y="82" width="100" height="90" rx="28" fill="#fff" stroke="#1e4f40" stroke-width="3"/>
                    <ellipse cx="110" cy="78" rx="26" ry="18" fill="#FCE7CF"/>
                    <circle cx="98" cy="64" r="4" fill="#172A24"/>
                    <circle cx="122" cy="64" r="4" fill="#172A24"/>
                    <path d="M102 88 Q110 96 118 88" stroke="#7C2D12" stroke-width="3" fill="none"/>
                </svg>
            </div>

            <div class="floor"></div>

            <div class="subtitle">
                <strong>Episódio 500 — pane no servidor</strong>
                <span>O sistema entrou em emergência. Tente novamente ou avise o time de desenvolvimento.</span>
            </div>
        </section>
    </div>
</div>
</body>
</html>