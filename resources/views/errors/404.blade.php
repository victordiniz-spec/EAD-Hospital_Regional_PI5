<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 | Integrar ReSaúde</title>
    @vite('resources/css/app.css')

    <style>
        :root{
            --bg-1:#071711;
            --bg-2:#0b2218;
            --bg-3:#103225;
            --panel:rgba(9, 28, 21, 0.82);
            --panel-soft:rgba(255,255,255,0.04);
            --line:rgba(115, 214, 176, 0.16);
            --line-strong:rgba(115, 214, 176, 0.30);
            --text:#f3fff9;
            --muted:#a6cfc0;
            --brand:#36d69d;
            --brand-2:#86f0c7;
            --brand-dark:#0f6d50;
            --danger:#ff6b7d;
            --shadow:0 26px 80px rgba(0,0,0,.38);

            --rx:0deg;
            --ry:0deg;
            --mx:0px;
            --my:0px;
        }

        *{
            box-sizing:border-box;
        }

        html, body{
            margin:0;
            min-height:100%;
            font-family: Inter, Arial, Helvetica, sans-serif;
            color:var(--text);
            background:
                radial-gradient(circle at 10% 15%, rgba(54,214,157,.14), transparent 22%),
                radial-gradient(circle at 90% 10%, rgba(134,240,199,.08), transparent 18%),
                radial-gradient(circle at 50% 90%, rgba(54,214,157,.07), transparent 28%),
                linear-gradient(135deg, var(--bg-1) 0%, var(--bg-2) 45%, #06130e 100%);
            overflow-x:hidden;
        }

        body::before{
            content:"";
            position:fixed;
            inset:0;
            background-image:
                linear-gradient(rgba(106, 187, 154, .08) 1px, transparent 1px),
                linear-gradient(90deg, rgba(106, 187, 154, .08) 1px, transparent 1px);
            background-size:34px 34px;
            mask-image: radial-gradient(circle at center, black 40%, transparent 100%);
            opacity:.45;
            pointer-events:none;
        }

        body::after{
            content:"";
            position:fixed;
            inset:0;
            background:
                linear-gradient(180deg, rgba(255,255,255,.03), transparent 18%, transparent 82%, rgba(255,255,255,.02));
            pointer-events:none;
        }

        .noise{
            position:fixed;
            inset:0;
            pointer-events:none;
            opacity:.10;
            background-image:
                radial-gradient(circle at 20% 30%, rgba(255,255,255,.8) 0 1px, transparent 1px),
                radial-gradient(circle at 70% 60%, rgba(255,255,255,.8) 0 1px, transparent 1px),
                radial-gradient(circle at 40% 80%, rgba(255,255,255,.8) 0 1px, transparent 1px);
            background-size:180px 180px;
        }

        .page{
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:26px;
        }

        .shell{
            width:100%;
            max-width:1440px;
            display:grid;
            grid-template-columns: minmax(340px, 530px) minmax(360px, 1fr);
            gap:24px;
            align-items:stretch;
        }

        .panel{
            position:relative;
            border:1px solid var(--line);
            background:var(--panel);
            border-radius:32px;
            box-shadow:var(--shadow);
            backdrop-filter: blur(14px);
            overflow:hidden;
        }

        .panel::before{
            content:"";
            position:absolute;
            inset:0;
            background:linear-gradient(180deg, rgba(255,255,255,.04), transparent 18%);
            pointer-events:none;
        }

        .content{
            padding:42px;
            display:flex;
            flex-direction:column;
            justify-content:center;
        }

        .badge{
            display:inline-flex;
            align-items:center;
            gap:10px;
            width:max-content;
            padding:12px 18px;
            border-radius:999px;
            background:rgba(54,214,157,.10);
            border:1px solid rgba(54,214,157,.18);
            color:#effff8;
            font-weight:800;
            font-size:13px;
            letter-spacing:.04em;
        }

        .badge .dot{
            width:12px;
            height:12px;
            border-radius:50%;
            background:var(--brand);
            box-shadow:0 0 0 0 rgba(54,214,157,.6);
            animation:pulse 1.8s infinite;
        }

        @keyframes pulse{
            0%{ box-shadow:0 0 0 0 rgba(54,214,157,.55); }
            70%{ box-shadow:0 0 0 12px rgba(54,214,157,0); }
            100%{ box-shadow:0 0 0 0 rgba(54,214,157,0); }
        }

        .eyebrow{
            margin-top:28px;
            font-size:12px;
            text-transform:uppercase;
            letter-spacing:.38em;
            color:#8ecbb4;
            font-weight:900;
        }

        .big-404{
            margin-top:18px;
            font-size:clamp(92px, 13vw, 168px);
            line-height:.88;
            font-weight:900;
            letter-spacing:-.07em;
            color:#f0fff9;
            text-shadow:
                0 0 22px rgba(54,214,157,.10),
                0 12px 40px rgba(0,0,0,.30);
        }

        .title{
            margin:10px 0 0;
            font-size:clamp(28px, 3vw, 46px);
            line-height:1.05;
            font-weight:900;
        }

        .desc{
            margin-top:18px;
            font-size:17px;
            line-height:1.85;
            color:var(--muted);
            max-width:700px;
        }

        .desc strong{
            color:#effff8;
        }

        .info{
            margin-top:28px;
            display:grid;
            grid-template-columns: 58px 1fr;
            gap:16px;
            align-items:flex-start;
            padding:22px;
            background:rgba(255,255,255,.03);
            border:1px solid rgba(136, 234, 198, .10);
            border-radius:24px;
        }

        .info-icon{
            width:58px;
            height:58px;
            border-radius:18px;
            background:rgba(54,214,157,.10);
            border:1px solid rgba(54,214,157,.20);
            display:flex;
            align-items:center;
            justify-content:center;
            color:#dffff4;
            box-shadow:inset 0 1px 0 rgba(255,255,255,.05);
        }

        .info-title{
            font-size:16px;
            font-weight:900;
        }

        .info-text{
            margin-top:5px;
            color:var(--muted);
            line-height:1.75;
            font-size:14px;
        }

        .mini-grid{
            margin-top:18px;
            display:grid;
            grid-template-columns:repeat(2, minmax(0,1fr));
            gap:12px;
        }

        .mini-card{
            padding:16px 18px;
            border-radius:20px;
            border:1px solid rgba(136,234,198,.10);
            background:rgba(255,255,255,.025);
        }

        .mini-card span{
            display:block;
            color:#89cbb1;
            font-size:11px;
            text-transform:uppercase;
            letter-spacing:.14em;
            font-weight:800;
        }

        .mini-card strong{
            display:block;
            margin-top:8px;
            font-size:15px;
            line-height:1.45;
            color:#f3fff9;
        }

        .actions{
            margin-top:28px;
            display:flex;
            flex-wrap:wrap;
            gap:14px;
        }

        .btn{
            appearance:none;
            border:none;
            cursor:pointer;
            text-decoration:none;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:10px;
            padding:15px 22px;
            border-radius:18px;
            font-size:14px;
            font-weight:900;
            transition:.25s ease;
        }

        .btn-primary{
            color:#073323;
            background:linear-gradient(180deg, #6df0bc, #31d49a);
            box-shadow:0 15px 34px rgba(54,214,157,.22);
        }

        .btn-primary:hover{
            transform:translateY(-2px) scale(1.01);
            box-shadow:0 18px 36px rgba(54,214,157,.28);
        }

        .btn-secondary{
            color:#effff8;
            background:rgba(255,255,255,.05);
            border:1px solid rgba(255,255,255,.08);
        }

        .btn-secondary:hover{
            transform:translateY(-2px);
            background:rgba(255,255,255,.08);
        }

        .footer-note{
            margin-top:24px;
            color:#7cb79e;
            font-size:13px;
            line-height:1.7;
        }

        .visual{
            min-height:760px;
            position:relative;
            overflow:hidden;
        }

        .visual-top{
            position:absolute;
            top:18px;
            left:18px;
            right:18px;
            z-index:12;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:12px;
            flex-wrap:wrap;
        }

        .chip{
            padding:12px 18px;
            border-radius:999px;
            background:rgba(255,255,255,.05);
            border:1px solid rgba(255,255,255,.08);
            font-size:12px;
            font-weight:800;
            color:#f1fff9;
            backdrop-filter: blur(8px);
        }

        .interactive-stage{
            position:relative;
            width:100%;
            height:100%;
            min-height:760px;
            perspective:1600px;
            overflow:hidden;
            cursor:default;
        }

        .ghost-code{
            position:absolute;
            inset:auto 0 140px 0;
            text-align:center;
            font-size:clamp(160px, 25vw, 320px);
            font-weight:900;
            letter-spacing:-.08em;
            line-height:.8;
            color:rgba(255,255,255,.04);
            text-shadow:0 0 50px rgba(54,214,157,.06);
            transform:translate3d(calc(var(--mx) * .08), calc(var(--my) * .05), -120px);
            pointer-events:none;
            user-select:none;
        }

        .scene-3d{
            position:absolute;
            inset:0;
            transform-style:preserve-3d;
            transition:transform .18s ease-out;
            transform:
                rotateX(var(--rx))
                rotateY(var(--ry));
        }

        .orb{
            position:absolute;
            border-radius:50%;
            filter:blur(2px);
            opacity:.95;
        }

        .orb-a{
            width:180px;
            height:180px;
            background:radial-gradient(circle, rgba(122,255,210,.60) 0%, rgba(54,214,157,.18) 45%, transparent 70%);
            top:90px;
            left:70px;
            transform:translateZ(-120px) translateX(calc(var(--mx) * -.08)) translateY(calc(var(--my) * -.08));
        }

        .orb-b{
            width:240px;
            height:240px;
            background:radial-gradient(circle, rgba(96,175,255,.30) 0%, rgba(54,214,157,.12) 44%, transparent 72%);
            right:50px;
            bottom:120px;
            transform:translateZ(-100px) translateX(calc(var(--mx) * .10)) translateY(calc(var(--my) * .08));
        }

        .orb-c{
            width:120px;
            height:120px;
            background:radial-gradient(circle, rgba(255,255,255,.18) 0%, rgba(255,255,255,.04) 46%, transparent 72%);
            right:180px;
            top:130px;
            transform:translateZ(-50px);
        }

        .ring{
            position:absolute;
            left:50%;
            top:50%;
            width:420px;
            height:420px;
            transform:translate(-50%, -42%) translateZ(-40px);
            border-radius:50%;
            border:1px solid rgba(101,228,183,.20);
            box-shadow:
                0 0 0 28px rgba(54,214,157,.03),
                0 0 0 58px rgba(54,214,157,.02),
                inset 0 0 50px rgba(54,214,157,.04);
            animation:ringPulse 3s ease-in-out infinite;
        }

        @keyframes ringPulse{
            0%,100%{ transform:translate(-50%, -42%) translateZ(-40px) scale(1); opacity:.9; }
            50%{ transform:translate(-50%, -42%) translateZ(-40px) scale(1.04); opacity:.55; }
        }

        .floating-card{
            position:absolute;
            width:220px;
            padding:18px;
            border-radius:24px;
            background:rgba(10,28,21,.88);
            border:1px solid rgba(115,214,176,.14);
            box-shadow:0 20px 44px rgba(0,0,0,.24);
            backdrop-filter: blur(10px);
            transform-style:preserve-3d;
            transition:transform .18s ease-out;
        }

        .floating-card .kicker{
            font-size:11px;
            text-transform:uppercase;
            letter-spacing:.18em;
            color:#8ecbb4;
            font-weight:900;
        }

        .floating-card .fc-title{
            margin-top:10px;
            font-size:16px;
            line-height:1.4;
            font-weight:900;
            color:#f3fff9;
        }

        .floating-card .fc-text{
            margin-top:6px;
            color:var(--muted);
            font-size:13px;
            line-height:1.6;
        }

        .fc-1{
            left:38px;
            top:118px;
            transform:
                translateZ(110px)
                translateX(calc(var(--mx) * -.20))
                translateY(calc(var(--my) * -.18));
            animation:floatCardA 4.2s ease-in-out infinite;
        }

        .fc-2{
            right:34px;
            top:160px;
            transform:
                translateZ(120px)
                translateX(calc(var(--mx) * .18))
                translateY(calc(var(--my) * -.16));
            animation:floatCardB 4.6s ease-in-out infinite;
        }

        .fc-3{
            left:80px;
            bottom:118px;
            width:200px;
            transform:
                translateZ(80px)
                translateX(calc(var(--mx) * -.12))
                translateY(calc(var(--my) * .14));
            animation:floatCardC 5s ease-in-out infinite;
        }

        @keyframes floatCardA{
            0%,100%{ transform:translateZ(110px) translateY(0); }
            50%{ transform:translateZ(110px) translateY(-10px); }
        }

        @keyframes floatCardB{
            0%,100%{ transform:translateZ(120px) translateY(0); }
            50%{ transform:translateZ(120px) translateY(-12px); }
        }

        @keyframes floatCardC{
            0%,100%{ transform:translateZ(80px) translateY(0); }
            50%{ transform:translateZ(80px) translateY(-8px); }
        }

        .helmet-zone{
            position:absolute;
            left:50%;
            top:52%;
            transform:translate(-50%, -50%);
            width:min(88%, 620px);
            height:min(82%, 620px);
            display:flex;
            align-items:center;
            justify-content:center;
            transform-style:preserve-3d;
        }

        .halo{
            position:absolute;
            width:440px;
            height:440px;
            border-radius:50%;
            background:
                radial-gradient(circle, rgba(134,240,199,.20) 0%, rgba(54,214,157,.08) 32%, rgba(54,214,157,0) 70%);
            filter:blur(10px);
            transform:translateZ(-40px);
            pointer-events:none;
        }

        .helmet-wrapper{
            position:relative;
            width:min(100%, 500px);
            aspect-ratio:1 / 1;
            transform-style:preserve-3d;
            transition:transform .15s ease-out;
            transform:
                translateZ(120px)
                translateX(calc(var(--mx) * .18))
                translateY(calc(var(--my) * .18))
                rotateX(calc(var(--ry) * -.55))
                rotateY(calc(var(--rx) * -.55));
        }

        .helmet-shadow{
            position:absolute;
            left:50%;
            bottom:18px;
            width:70%;
            height:44px;
            border-radius:50%;
            transform:translateX(-50%) translateZ(-20px);
            background:radial-gradient(circle, rgba(0,0,0,.42) 0%, rgba(0,0,0,.10) 55%, transparent 78%);
            filter:blur(10px);
        }

        .helmet-svg{
            position:relative;
            width:100%;
            height:100%;
            filter:drop-shadow(0 36px 50px rgba(0,0,0,.34));
            transform:translateZ(40px);
        }

        .visor-glow{
            position:absolute;
            left:50%;
            top:48%;
            width:54%;
            height:23%;
            border-radius:999px 999px 42px 42px;
            transform:translate(-50%, -50%) translateZ(90px);
            background:linear-gradient(90deg, rgba(255,255,255,.06), rgba(255,255,255,.16), rgba(255,255,255,.03));
            mix-blend-mode:screen;
            opacity:.85;
            pointer-events:none;
            filter:blur(1px);
        }

        .hint{
            position:absolute;
            left:50%;
            bottom:10px;
            transform:translateX(-50%) translateZ(140px);
            padding:12px 16px;
            border-radius:999px;
            background:rgba(255,255,255,.06);
            border:1px solid rgba(255,255,255,.08);
            color:#effff9;
            font-size:12px;
            font-weight:800;
            letter-spacing:.08em;
            text-transform:uppercase;
            white-space:nowrap;
            backdrop-filter: blur(8px);
        }

        .hint strong{
            color:var(--brand-2);
        }

        .scan-floor{
            position:absolute;
            left:0;
            right:0;
            bottom:0;
            height:190px;
            background:
                linear-gradient(180deg, rgba(255,255,255,.03), rgba(255,255,255,0)),
                linear-gradient(180deg, #102219, #07130f);
            border-top:1px solid rgba(136,234,198,.10);
            transform:translateZ(-80px);
        }

        .scan-floor::before{
            content:"";
            position:absolute;
            inset:28px 0 auto 0;
            height:8px;
            background:
                repeating-linear-gradient(90deg, rgba(115,214,176,.16) 0 46px, transparent 46px 84px);
            opacity:.75;
        }

        .bottom-text{
            position:absolute;
            left:18px;
            right:18px;
            bottom:18px;
            z-index:14;
            padding:16px 18px;
            border-radius:22px;
            background:rgba(10,28,21,.90);
            border:1px solid rgba(115,214,176,.14);
            text-align:center;
            backdrop-filter: blur(10px);
        }

        .bottom-text strong{
            display:block;
            font-size:15px;
            font-weight:900;
        }

        .bottom-text span{
            display:block;
            margin-top:4px;
            color:var(--muted);
            font-size:13px;
            line-height:1.65;
        }

        .particle{
            position:absolute;
            width:8px;
            height:8px;
            border-radius:50%;
            background:rgba(134,240,199,.8);
            box-shadow:0 0 18px rgba(134,240,199,.45);
            opacity:.75;
            animation:particleFloat 5s linear infinite;
        }

        .p1{ top:120px; left:50%; animation-delay:0s; }
        .p2{ top:220px; right:140px; animation-delay:1s; }
        .p3{ bottom:220px; left:140px; animation-delay:2s; }
        .p4{ bottom:120px; right:210px; animation-delay:3s; }

        @keyframes particleFloat{
            0%{ transform:translateY(0) scale(.9); opacity:.2; }
            30%{ opacity:.75; }
            100%{ transform:translateY(-40px) scale(1.15); opacity:0; }
        }

        .interactive-stage.is-clicked .helmet-wrapper{
            animation:helmetPulse .45s ease;
        }

        @keyframes helmetPulse{
            0%{ transform:translateZ(120px) scale(1) rotateX(0deg) rotateY(0deg); }
            50%{ transform:translateZ(130px) scale(1.03) rotateX(-2deg) rotateY(2deg); }
            100%{ transform:translateZ(120px) scale(1) rotateX(0deg) rotateY(0deg); }
        }

        @media (max-width: 1180px){
            .shell{
                grid-template-columns:1fr;
            }

            .visual,
            .interactive-stage{
                min-height:700px;
            }
        }

        @media (max-width: 760px){
            .page{
                padding:16px;
            }

            .content{
                padding:28px;
            }

            .desc{
                font-size:15px;
                line-height:1.8;
            }

            .mini-grid{
                grid-template-columns:1fr;
            }

            .visual,
            .interactive-stage{
                min-height:620px;
            }

            .floating-card{
                width:170px;
                padding:14px;
            }

            .floating-card .fc-title{
                font-size:14px;
            }

            .floating-card .fc-text{
                font-size:12px;
            }

            .fc-1{
                left:12px;
                top:90px;
            }

            .fc-2{
                right:12px;
                top:132px;
            }

            .fc-3{
                left:18px;
                bottom:110px;
                width:170px;
            }

            .helmet-zone{
                width:96%;
            }

            .halo{
                width:320px;
                height:320px;
            }

            .ring{
                width:300px;
                height:300px;
            }

            .hint{
                font-size:10px;
                padding:10px 14px;
            }

            .ghost-code{
                bottom:170px;
                font-size:170px;
            }

            .bottom-text{
                left:12px;
                right:12px;
                bottom:12px;
            }
        }
    </style>
</head>
<body>
    <div class="noise"></div>

    <div class="page">
        <div class="shell">
            <section class="panel content">
                <div class="badge">
                    <span class="dot"></span>
                    INTEGRAR RESAÚDE • ERRO 404 INTERATIVO
                </div>

                <div class="eyebrow">STATUS DA NAVEGAÇÃO</div>

                <div class="big-404">404</div>

                <h1 class="title">Página não encontrada</h1>

                <p class="desc">
                    A rota que você tentou acessar não está disponível no momento.
                    Então eu refiz esta tela com um visual mais forte, inspirado em uma
                    <strong>experiência 3D moderna</strong>, mas dentro do
                    <strong>mundo da saúde</strong> do seu sistema.
                    <br><br>
                    O capacete ao lado reage ao mouse para dar aquele efeito premium e interativo.
                </p>

                <div class="info">
                    <div class="info-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v7.5M8.25 12h7.5"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>

                    <div>
                        <div class="info-title">O que o usuário pode fazer agora?</div>
                        <div class="info-text">
                            Voltar para a página inicial, retornar para a tela anterior
                            ou tentar acessar novamente a área correta do sistema.
                        </div>
                    </div>
                </div>

                <div class="mini-grid">
                    <div class="mini-card">
                        <span>ambiente</span>
                        <strong>Integrar ReSaúde</strong>
                    </div>

                    <div class="mini-card">
                        <span>situação</span>
                        <strong>Rota ausente ou removida</strong>
                    </div>
                </div>

                <div class="actions">
                    <a href="{{ url('/') }}" class="btn btn-primary">
                        Ir para o início
                    </a>

                    <button type="button" onclick="history.back()" class="btn btn-secondary">
                        Voltar
                    </button>
                </div>

                <div class="footer-note">
                    Página 404 personalizada • visual 3D interativo • tema saúde
                </div>
            </section>

            <section class="panel visual">
                <div class="visual-top">
                    <div class="chip">MODO INTERATIVO • 3D</div>
                    <div class="chip">Mexa o mouse no capacete</div>
                </div>

                <div class="interactive-stage" id="interactiveStage">
                    <div class="ghost-code">404</div>

                    <div class="scene-3d" id="scene3d">
                        <div class="orb orb-a"></div>
                        <div class="orb orb-b"></div>
                        <div class="orb orb-c"></div>

                        <div class="ring"></div>

                        <div class="floating-card fc-1">
                            <div class="kicker">alerta clínico</div>
                            <div class="fc-title">A rota sumiu do painel</div>
                            <div class="fc-text">
                                O conteúdo procurado não foi localizado nesta navegação.
                            </div>
                        </div>

                        <div class="floating-card fc-2">
                            <div class="kicker">navegação segura</div>
                            <div class="fc-title">Retorne ao fluxo principal</div>
                            <div class="fc-text">
                                Use os botões para voltar sem perder a experiência do sistema.
                            </div>
                        </div>

                        <div class="floating-card fc-3">
                            <div class="kicker">monitoramento</div>
                            <div class="fc-title">Erro 404 detectado</div>
                            <div class="fc-text">
                                Tela redesenhada para uma experiência mais bonita e viva.
                            </div>
                        </div>

                        <div class="helmet-zone">
                            <div class="halo"></div>

                            <div class="helmet-wrapper" id="helmetWrapper">
                                <div class="helmet-shadow"></div>

                                <svg class="helmet-svg" viewBox="0 0 520 520" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <defs>
                                        <linearGradient id="shellMain" x1="0" y1="0" x2="1" y2="1">
                                            <stop offset="0%" stop-color="#ffffff"/>
                                            <stop offset="55%" stop-color="#d9f9ee"/>
                                            <stop offset="100%" stop-color="#8edec0"/>
                                        </linearGradient>

                                        <linearGradient id="shellSide" x1="0" y1="0" x2="1" y2="1">
                                            <stop offset="0%" stop-color="#0b7d5a"/>
                                            <stop offset="100%" stop-color="#0e4f3a"/>
                                        </linearGradient>

                                        <linearGradient id="visorGrad" x1="0" y1="0" x2="1" y2="0">
                                            <stop offset="0%" stop-color="#09241d"/>
                                            <stop offset="50%" stop-color="#183b32"/>
                                            <stop offset="100%" stop-color="#0c221b"/>
                                        </linearGradient>

                                        <linearGradient id="tealBand" x1="0" y1="0" x2="1" y2="1">
                                            <stop offset="0%" stop-color="#7bf0c2"/>
                                            <stop offset="100%" stop-color="#31d49a"/>
                                        </linearGradient>

                                        <filter id="softShadow" x="-30%" y="-30%" width="160%" height="160%">
                                            <feDropShadow dx="0" dy="20" stdDeviation="18" flood-color="rgba(0,0,0,.35)"/>
                                        </filter>
                                    </defs>

                                    <!-- sombra base -->
                                    <ellipse cx="270" cy="430" rx="122" ry="30" fill="rgba(0,0,0,.18)"/>

                                    <!-- casco externo -->
                                    <path filter="url(#softShadow)"
                                        d="M146 260
                                           C146 150, 225 84, 326 92
                                           C412 99, 468 162, 471 246
                                           C473 301, 454 343, 420 377
                                           C388 409, 344 425, 297 425
                                           L212 425
                                           C184 425, 162 413, 150 389
                                           C140 369, 141 348, 151 330
                                           C121 315, 104 293, 104 265
                                           C104 239, 119 216, 146 204 Z"
                                        fill="url(#shellMain)"/>

                                    <!-- lateral verde -->
                                    <path
                                        d="M273 98
                                           C368 96, 437 151, 451 230
                                           C418 224, 391 220, 356 216
                                           C328 213, 301 211, 274 211
                                           Z"
                                        fill="url(#shellSide)"/>

                                    <!-- faixa superior -->
                                    <path
                                        d="M201 143
                                           C242 113, 308 103, 370 120
                                           C357 136, 348 153, 343 172
                                           C282 163, 238 165, 198 181
                                           C194 168, 194 155, 201 143 Z"
                                        fill="url(#tealBand)"/>

                                    <!-- cruz médica -->
                                    <g transform="translate(235 138)">
                                        <rect x="0" y="0" width="50" height="50" rx="14" fill="#ffffff" stroke="#0b7d5a" stroke-width="4"/>
                                        <rect x="20" y="9" width="10" height="32" rx="5" fill="#25c98f"/>
                                        <rect x="9" y="20" width="32" height="10" rx="5" fill="#25c98f"/>
                                    </g>

                                    <!-- ecg band -->
                                    <path
                                        d="M168 208
                                           C214 193, 265 188, 332 196
                                           C334 204, 334 212, 332 220
                                           C286 214, 254 214, 226 220
                                           L214 220 L205 205 L194 237 L181 204 L170 220
                                           C154 224, 142 229, 132 235
                                           C130 226, 132 216, 168 208 Z"
                                        fill="#24bf89"/>

                                    <!-- visor -->
                                    <path
                                        d="M178 224
                                           C218 208, 302 206, 381 225
                                           C397 230, 408 242, 410 256
                                           C413 271, 405 283, 387 290
                                           C331 313, 267 320, 189 308
                                           C166 304, 153 293, 149 278
                                           C145 261, 155 237, 178 224 Z"
                                        fill="url(#visorGrad)"
                                        stroke="rgba(255,255,255,.18)"
                                        stroke-width="4"/>

                                    <!-- brilho visor -->
                                    <path
                                        d="M191 233
                                           C231 220, 297 220, 360 236
                                           C337 241, 309 244, 287 245
                                           C253 247, 220 245, 191 240 Z"
                                        fill="rgba(255,255,255,.16)"/>

                                    <!-- estrutura boca -->
                                    <path
                                        d="M147 330
                                           C171 322, 209 318, 248 319
                                           C246 349, 253 382, 268 425
                                           L210 425
                                           C183 425, 162 413, 150 390
                                           C140 370, 141 346, 147 330 Z"
                                        fill="#0e7656"/>

                                    <!-- detalhe lateral -->
                                    <path
                                        d="M302 330
                                           C346 325, 384 312, 408 294
                                           C421 307, 428 323, 429 343
                                           C430 366, 421 386, 404 400
                                           C383 418, 356 425, 297 425
                                           C304 394, 307 365, 302 330 Z"
                                        fill="#e8fff4"/>

                                    <!-- grade frontal -->
                                    <g transform="translate(173 342)">
                                        <rect x="0" y="0" width="70" height="30" rx="12" fill="#0b2018"/>
                                        <rect x="10" y="8" width="50" height="4" rx="2" fill="#59d9a8"/>
                                        <rect x="10" y="18" width="50" height="4" rx="2" fill="#59d9a8"/>
                                    </g>

                                    <!-- faixas finas -->
                                    <path d="M281 108 C358 110, 410 149, 430 211" stroke="rgba(255,255,255,.24)" stroke-width="6" fill="none" stroke-linecap="round"/>
                                    <path d="M158 262 C200 248, 246 245, 290 248" stroke="rgba(255,255,255,.16)" stroke-width="6" fill="none" stroke-linecap="round"/>
                                </svg>

                                <div class="visor-glow"></div>

                                <div class="hint">
                                    <strong>Mexa o mouse</strong> para interagir com o capacete
                                </div>
                            </div>
                        </div>

                        <div class="particle p1"></div>
                        <div class="particle p2"></div>
                        <div class="particle p3"></div>
                        <div class="particle p4"></div>

                        <div class="scan-floor"></div>
                    </div>

                    <div class="bottom-text">
                        <strong>Rota não encontrada • experiência 404 redesenhada</strong>
                        <span>
                            Visual futurista, interativo e alinhado ao universo da saúde do seu sistema.
                        </span>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script>
        (function () {
            const stage = document.getElementById('interactiveStage');
            const root = document.documentElement;

            if (!stage) return;

            let frame = null;

            function applyInteraction(clientX, clientY) {
                const rect = stage.getBoundingClientRect();

                const x = clientX - rect.left;
                const y = clientY - rect.top;

                const px = (x / rect.width) - 0.5;
                const py = (y / rect.height) - 0.5;

                const rotateY = px * 16;
                const rotateX = py * -14;

                const moveX = px * 36;
                const moveY = py * 28;

                root.style.setProperty('--rx', rotateX.toFixed(2) + 'deg');
                root.style.setProperty('--ry', rotateY.toFixed(2) + 'deg');
                root.style.setProperty('--mx', moveX.toFixed(2) + 'px');
                root.style.setProperty('--my', moveY.toFixed(2) + 'px');
            }

            function resetInteraction() {
                root.style.setProperty('--rx', '0deg');
                root.style.setProperty('--ry', '0deg');
                root.style.setProperty('--mx', '0px');
                root.style.setProperty('--my', '0px');
            }

            stage.addEventListener('mousemove', (event) => {
                if (frame) cancelAnimationFrame(frame);
                frame = requestAnimationFrame(() => {
                    applyInteraction(event.clientX, event.clientY);
                });
            });

            stage.addEventListener('mouseleave', () => {
                resetInteraction();
            });

            stage.addEventListener('click', () => {
                stage.classList.add('is-clicked');
                setTimeout(() => {
                    stage.classList.remove('is-clicked');
                }, 450);
            });

            stage.addEventListener('touchmove', (event) => {
                if (!event.touches || !event.touches[0]) return;
                const touch = event.touches[0];
                applyInteraction(touch.clientX, touch.clientY);
            }, { passive: true });

            stage.addEventListener('touchend', () => {
                resetInteraction();
            });
        })();
    </script>
</body>
</html>