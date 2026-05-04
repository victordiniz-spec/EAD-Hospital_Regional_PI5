<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Erro') - Integrar ReSaúde</title>

    @vite('resources/css/app.css')

    <style>
        :root{
            --bg-1:#03130E;
            --bg-2:#071F18;
            --panel:#0C211A;
            --panel-2:#102A22;
            --line:#1E4F40;
            --line-soft:rgba(58, 129, 108, .25);
            --text:#E7FFF6;
            --muted:#9AC7B8;
            --brand:#15D087;
            --brand-soft:rgba(21,208,135,.14);
            --red:#FF5F73;
            --yellow:#FFC857;
            --shadow:0 24px 60px rgba(0,0,0,.35);
        }

        *{ box-sizing:border-box; }

        html,body{
            margin:0;
            min-height:100%;
            background:
                radial-gradient(circle at 20% 10%, rgba(21,208,135,.12), transparent 25%),
                radial-gradient(circle at 85% 15%, rgba(0,196,255,.09), transparent 18%),
                radial-gradient(circle at 80% 80%, rgba(255,200,87,.10), transparent 18%),
                linear-gradient(135deg, var(--bg-1), var(--bg-2));
            color:var(--text);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            overflow-x:hidden;
        }

        .tech-grid{
            position:fixed;
            inset:0;
            background-image:
                linear-gradient(rgba(39,120,96,.10) 1px, transparent 1px),
                linear-gradient(90deg, rgba(39,120,96,.10) 1px, transparent 1px);
            background-size:32px 32px;
            mask-image: radial-gradient(circle at center, black 45%, transparent 100%);
            pointer-events:none;
            opacity:.45;
        }

        .noise{
            position:fixed;
            inset:0;
            background-image:
                radial-gradient(circle, rgba(255,255,255,.035) 1px, transparent 1px);
            background-size:14px 14px;
            opacity:.18;
            pointer-events:none;
        }

        .page{
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:28px;
            position:relative;
        }

        .shell{
            width:100%;
            max-width:1450px;
            display:grid;
            grid-template-columns: 1.05fr .95fr;
            gap:26px;
            align-items:center;
        }

        .panel{
            position:relative;
            border:1px solid rgba(56,142,114,.26);
            background:
                linear-gradient(180deg, rgba(255,255,255,.04), rgba(255,255,255,.01)),
                rgba(10, 26, 21, .76);
            box-shadow: var(--shadow);
            border-radius:34px;
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            overflow:hidden;
        }

        .panel::before{
            content:"";
            position:absolute;
            inset:0;
            background:
                linear-gradient(120deg, transparent 0%, rgba(255,255,255,.04) 20%, transparent 42%);
            transform:translateX(-100%);
            animation:panelSweep 8s linear infinite;
            pointer-events:none;
        }

        @keyframes panelSweep{
            to{ transform:translateX(100%); }
        }

        .left{
            padding:34px;
        }

        .badge{
            display:inline-flex;
            align-items:center;
            gap:10px;
            padding:10px 16px;
            border-radius:999px;
            border:1px solid rgba(21,208,135,.25);
            background:rgba(21,208,135,.08);
            color:#CFFFF0;
            font-size:12px;
            font-weight:800;
            letter-spacing:.08em;
            text-transform:uppercase;
        }

        .badge-dot{
            width:10px;
            height:10px;
            border-radius:50%;
            background:@yield('accent', #15D087);
            box-shadow:0 0 0 0 rgba(21,208,135,.45);
            animation:pingDot 1.8s infinite;
        }

        @keyframes pingDot{
            0%{ box-shadow:0 0 0 0 rgba(21,208,135,.4); }
            70%{ box-shadow:0 0 0 12px rgba(21,208,135,0); }
            100%{ box-shadow:0 0 0 0 rgba(21,208,135,0); }
        }

        .eyebrow{
            margin-top:24px;
            color:#79B8A3;
            font-size:12px;
            text-transform:uppercase;
            letter-spacing:.35em;
            font-weight:800;
        }

        .code{
            margin:10px 0 0;
            font-size:clamp(82px, 11vw, 132px);
            line-height:.9;
            font-weight:900;
            letter-spacing:-.05em;
            color:@yield('accent', #15D087);
            text-shadow:0 0 26px rgba(21,208,135,.18);
        }

        .title{
            margin-top:14px;
            font-size:clamp(30px, 4vw, 46px);
            line-height:1.04;
            font-weight:900;
            color:#F3FFF9;
        }

        .desc{
            margin-top:18px;
            font-size:17px;
            line-height:1.85;
            color:var(--muted);
            max-width:760px;
        }

        .info-card{
            margin-top:26px;
            padding:20px;
            border-radius:24px;
            border:1px solid rgba(69,160,129,.22);
            background:
                linear-gradient(180deg, rgba(255,255,255,.03), rgba(255,255,255,.01)),
                rgba(7, 27, 21, .85);
            display:flex;
            gap:14px;
            align-items:flex-start;
        }

        .info-icon{
            width:50px;
            height:50px;
            border-radius:18px;
            display:flex;
            align-items:center;
            justify-content:center;
            background:rgba(21,208,135,.09);
            color:@yield('accent', #15D087);
            border:1px solid rgba(21,208,135,.18);
            flex-shrink:0;
        }

        .info-title{
            font-size:16px;
            font-weight:800;
            color:#F2FFF9;
        }

        .info-text{
            margin-top:4px;
            color:var(--muted);
            font-size:14px;
            line-height:1.7;
        }

        .actions{
            display:flex;
            flex-wrap:wrap;
            gap:14px;
            margin-top:28px;
        }

        .btn-primary,
        .btn-secondary{
            border:none;
            text-decoration:none;
            cursor:pointer;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:10px;
            padding:15px 24px;
            border-radius:18px;
            font-weight:800;
            transition:.25s ease;
        }

        .btn-primary{
            background:@yield('accent', #15D087);
            color:#071A14;
            box-shadow:0 16px 34px rgba(21,208,135,.22);
        }

        .btn-primary:hover{
            transform:translateY(-2px) scale(1.01);
            filter:brightness(1.05);
        }

        .btn-secondary{
            color:#DDFCF1;
            background:rgba(255,255,255,.04);
            border:1px solid rgba(86,158,132,.26);
        }

        .btn-secondary:hover{
            transform:translateY(-2px);
            background:rgba(255,255,255,.07);
        }

        .footer-note{
            margin-top:24px;
            font-size:13px;
            color:#7AA692;
            line-height:1.7;
        }

        /* CENA */
        .scene{
            min-height:700px;
            position:relative;
            overflow:hidden;
        }

        .scene-topbar{
            position:absolute;
            top:18px;
            left:18px;
            right:18px;
            z-index:10;
            display:flex;
            justify-content:space-between;
            gap:12px;
            flex-wrap:wrap;
        }

        .chip{
            padding:10px 14px;
            border-radius:999px;
            font-size:12px;
            font-weight:800;
            letter-spacing:.04em;
            color:#D9FFF1;
            background:rgba(255,255,255,.04);
            border:1px solid rgba(94,178,148,.2);
            backdrop-filter: blur(8px);
        }

        .scene-bg{
            position:absolute;
            inset:0;
            background:
                radial-gradient(circle at 50% 8%, rgba(21,208,135,.10), transparent 22%),
                linear-gradient(180deg, rgba(255,255,255,.02), rgba(255,255,255,0));
        }

        .light-beam{
            position:absolute;
            top:-20%;
            width:180px;
            height:140%;
            background:linear-gradient(180deg, rgba(255,255,255,.13), transparent);
            filter:blur(30px);
            opacity:.09;
            transform:rotate(18deg);
            animation:beamMove 9s ease-in-out infinite;
        }

        .light-1{ left:10%; animation-delay:0s; }
        .light-2{ right:10%; animation-delay:2s; }

        @keyframes beamMove{
            0%,100%{ transform:translateY(-10px) rotate(16deg); opacity:.06; }
            50%{ transform:translateY(10px) rotate(20deg); opacity:.12; }
        }

        .alarm-ring{
            position:absolute;
            inset:auto auto 180px 50%;
            transform:translateX(-50%);
            width:340px;
            height:340px;
            border-radius:50%;
            border:1px solid rgba(21,208,135,.15);
            box-shadow:
                0 0 0 24px rgba(21,208,135,.03),
                0 0 0 56px rgba(21,208,135,.02);
            animation:ringPulse 2.4s ease-in-out infinite;
        }

        @keyframes ringPulse{
            0%,100%{ transform:translateX(-50%) scale(1); opacity:.9; }
            50%{ transform:translateX(-50%) scale(1.05); opacity:.55; }
        }

        .monitor{
            position:absolute;
            left:50%;
            transform:translateX(-50%);
            top:118px;
            width:300px;
            height:242px;
            border-radius:28px;
            background:
                linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.02)),
                #0D231C;
            border:1px solid rgba(83,166,136,.28);
            box-shadow:
                0 18px 40px rgba(0,0,0,.28),
                inset 0 0 0 1px rgba(255,255,255,.02);
            overflow:hidden;
            z-index:6;
        }

        .monitor::before{
            content:"";
            position:absolute;
            inset:0;
            background:
                linear-gradient(135deg, rgba(255,255,255,.06), transparent 36%);
            pointer-events:none;
        }

        .monitor-screen{
            position:absolute;
            inset:16px 16px 52px;
            border-radius:18px;
            background:
                radial-gradient(circle at 30% 20%, rgba(255,255,255,.08), transparent 24%),
                linear-gradient(180deg, #061A14, #08251D);
            display:flex;
            flex-direction:column;
            justify-content:center;
            align-items:center;
            overflow:hidden;
        }

        .screen-scan{
            position:absolute;
            inset:0;
            background:linear-gradient(180deg, transparent 0%, rgba(21,208,135,.11) 50%, transparent 100%);
            transform:translateY(-100%);
            animation:scanMove 2.8s linear infinite;
        }

        @keyframes scanMove{
            to{ transform:translateY(100%); }
        }

        .monitor-code{
            position:relative;
            z-index:2;
            font-size:70px;
            font-weight:900;
            letter-spacing:-.04em;
            color:@yield('accent', #15D087);
            text-shadow:0 0 20px rgba(21,208,135,.20);
        }

        .monitor-label{
            position:relative;
            z-index:2;
            margin-top:10px;
            color:#D9FFF0;
            font-size:15px;
            font-weight:800;
            text-align:center;
            padding:0 16px;
        }

        .screen-lines{
            position:absolute;
            inset:auto 16px 16px 16px;
            height:52px;
        }

        .screen-lines svg{
            width:100%;
            height:100%;
        }

        .ekg{
            fill:none;
            stroke:@yield('accent', #15D087);
            stroke-width:5;
            stroke-linecap:round;
            stroke-linejoin:round;
            stroke-dasharray:260;
            animation:ekgDash 2.1s linear infinite;
        }

        @keyframes ekgDash{
            from{ stroke-dashoffset:260; }
            to{ stroke-dashoffset:0; }
        }

        .monitor-stand{
            position:absolute;
            bottom:18px;
            left:50%;
            transform:translateX(-50%);
            width:120px;
            height:16px;
            background:#46675C;
            border-radius:999px;
        }

        .speed-trails{
            position:absolute;
            inset:0;
            pointer-events:none;
            z-index:1;
        }

        .trail{
            position:absolute;
            height:8px;
            border-radius:999px;
            opacity:.22;
            filter:blur(.2px);
            background:linear-gradient(90deg, transparent, rgba(21,208,135,.15), rgba(21,208,135,.55));
            animation:trailMove 1s linear infinite;
        }

        .trail.left{ left:30px; }
        .trail.right{
            right:30px;
            background:linear-gradient(270deg, transparent, rgba(21,208,135,.15), rgba(21,208,135,.55));
        }

        .trail.t1{ top:290px; width:70px; animation-delay:0s; }
        .trail.t2{ top:320px; width:52px; animation-delay:.15s; }
        .trail.t3{ top:350px; width:84px; animation-delay:.3s; }
        .trail.t4{ top:410px; width:62px; animation-delay:.45s; }

        @keyframes trailMove{
            0%{ transform:translateX(0); opacity:.12; }
            50%{ opacity:.36; }
            100%{ transform:translateX(14px); opacity:.12; }
        }

        .floor{
            position:absolute;
            left:0;
            right:0;
            bottom:0;
            height:190px;
            background:
                linear-gradient(180deg, rgba(255,255,255,.04), rgba(255,255,255,0)),
                linear-gradient(180deg, #10251E, #0A1914);
            border-top:1px solid rgba(67,145,117,.16);
        }

        .floor::before{
            content:"";
            position:absolute;
            inset:24px 0 auto 0;
            height:8px;
            background:repeating-linear-gradient(
                90deg,
                rgba(97,180,149,.22) 0 44px,
                transparent 44px 80px
            );
            opacity:.6;
        }

        /* CABO */
        .cable-wrap{
            position:absolute;
            left:155px;
            top:335px;
            width:270px;
            height:140px;
            z-index:5;
            animation:cableFloat 2s ease-in-out infinite;
        }

        @keyframes cableFloat{
            0%,100%{ transform:rotate(0deg) translateY(0); }
            50%{ transform:rotate(-4deg) translateY(-4px); }
        }

        .plug-glow{
            filter:drop-shadow(0 0 10px rgba(255,200,87,.35));
            animation:plugBounce 1.1s ease-in-out infinite;
            transform-origin:center;
        }

        @keyframes plugBounce{
            0%,100%{ transform:rotate(6deg); }
            50%{ transform:rotate(-8deg); }
        }

        /* PERSONAGENS */
        .char{
            position:absolute;
            z-index:4;
        }

        .cat{
            left:55px;
            bottom:104px;
            width:220px;
            height:300px;
            animation:catRun 2.4s ease-in-out infinite;
        }

        .dog{
            right:55px;
            bottom:104px;
            width:230px;
            height:308px;
            animation:dogRun 2.4s ease-in-out infinite;
        }

        @keyframes catRun{
            0%,100%{ transform:translateX(0) translateY(0) skewX(-1deg); }
            25%{ transform:translateX(10px) translateY(-5px) skewX(-1deg); }
            50%{ transform:translateX(20px) translateY(0) skewX(0deg); }
            75%{ transform:translateX(10px) translateY(-6px) skewX(1deg); }
        }

        @keyframes dogRun{
            0%,100%{ transform:translateX(0) translateY(0) skewX(1deg); }
            25%{ transform:translateX(-8px) translateY(-4px) skewX(1deg); }
            50%{ transform:translateX(-16px) translateY(0) skewX(0deg); }
            75%{ transform:translateX(-8px) translateY(-5px) skewX(-1deg); }
        }

        .limb-a{ animation:limbA .45s ease-in-out infinite; transform-origin:top center; }
        .limb-b{ animation:limbB .45s ease-in-out infinite; transform-origin:top center; }
        .tail-a{ animation:tailA .8s ease-in-out infinite; transform-origin:left center; }
        .tail-b{ animation:tailB .45s ease-in-out infinite; transform-origin:left center; }

        @keyframes limbA{
            0%,100%{ transform:rotate(12deg); }
            50%{ transform:rotate(-14deg); }
        }

        @keyframes limbB{
            0%,100%{ transform:rotate(-14deg); }
            50%{ transform:rotate(12deg); }
        }

        @keyframes tailA{
            0%,100%{ transform:rotate(10deg); }
            50%{ transform:rotate(-10deg); }
        }

        @keyframes tailB{
            0%,100%{ transform:rotate(18deg); }
            50%{ transform:rotate(-18deg); }
        }

        .speech{
            position:absolute;
            z-index:8;
            max-width:220px;
            padding:14px 16px;
            border-radius:22px;
            background:
                linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.03)),
                rgba(8, 25, 20, .92);
            border:1px solid rgba(71,157,126,.24);
            box-shadow:0 16px 28px rgba(0,0,0,.18);
            animation:speechFloat 3.2s ease-in-out infinite;
        }

        .speech strong{
            display:block;
            font-size:14px;
            color:#F1FFF9;
            line-height:1.45;
        }

        .speech small{
            display:block;
            margin-top:4px;
            color:#95C5B4;
            line-height:1.5;
            font-size:12px;
        }

        .speech::after{
            content:"";
            position:absolute;
            width:14px;
            height:14px;
            background:rgba(8,25,20,.95);
            border-left:1px solid rgba(71,157,126,.24);
            border-bottom:1px solid rgba(71,157,126,.24);
            transform:rotate(-45deg);
        }

        .speech-cat{
            left:28px;
            top:88px;
        }

        .speech-cat::after{
            bottom:-8px;
            left:26px;
        }

        .speech-dog{
            right:28px;
            top:136px;
            animation-delay:.7s;
        }

        .speech-dog::after{
            bottom:-8px;
            right:26px;
        }

        @keyframes speechFloat{
            0%,100%{ transform:translateY(0); }
            50%{ transform:translateY(-7px); }
        }

        .subtitle-box{
            position:absolute;
            left:20px;
            right:20px;
            bottom:22px;
            z-index:9;
            padding:16px 18px;
            border-radius:22px;
            background:
                linear-gradient(180deg, rgba(255,255,255,.04), rgba(255,255,255,.02)),
                rgba(8, 25, 20, .92);
            border:1px solid rgba(70,151,121,.24);
            box-shadow:0 16px 28px rgba(0,0,0,.20);
            text-align:center;
        }

        .subtitle-box strong{
            display:block;
            color:#F1FFF9;
            font-size:15px;
            font-weight:900;
        }

        .subtitle-box span{
            display:block;
            margin-top:4px;
            color:#9CC9BA;
            font-size:13px;
            line-height:1.6;
        }

        .particles span{
            position:absolute;
            width:8px;
            height:8px;
            border-radius:999px;
            background:rgba(21,208,135,.18);
            filter:blur(.2px);
            animation:particleUp 2.2s ease-in-out infinite;
        }

        .particles span:nth-child(1){ left:250px; bottom:140px; animation-delay:0s; }
        .particles span:nth-child(2){ left:280px; bottom:160px; animation-delay:.4s; }
        .particles span:nth-child(3){ right:250px; bottom:146px; animation-delay:.8s; }
        .particles span:nth-child(4){ right:280px; bottom:166px; animation-delay:1.2s; }

        @keyframes particleUp{
            0%,100%{ transform:translateY(0) scale(1); opacity:.2; }
            50%{ transform:translateY(-12px) scale(1.25); opacity:.6; }
        }

        @media (max-width: 1150px){
            .shell{
                grid-template-columns:1fr;
            }
            .scene{
                min-height:650px;
            }
        }

        @media (max-width: 700px){
            .page{ padding:14px; }
            .left{ padding:24px; }
            .scene{ min-height:590px; }
            .monitor{
                width:240px;
                height:210px;
                top:115px;
            }
            .monitor-code{ font-size:56px; }
            .cat{
                left:10px;
                width:180px;
                transform-origin:bottom left;
            }
            .dog{
                right:10px;
                width:190px;
                transform-origin:bottom right;
            }
            .cable-wrap{
                left:84px;
                top:314px;
                width:220px;
            }
            .speech-cat{
                left:12px;
                top:74px;
                max-width:170px;
            }
            .speech-dog{
                right:12px;
                top:124px;
                max-width:170px;
            }
        }
    </style>
</head>
<body>

    <div class="tech-grid"></div>
    <div class="noise"></div>

    <main class="page">
        <section class="shell">

            <!-- ESQUERDA -->
            <div class="panel left">
                <div class="badge">
                    <span class="badge-dot"></span>
                    Integrar ReSaúde • Tela de Erro Futurista
                </div>

                <div class="eyebrow">Status do sistema</div>

                <div class="code">@yield('code')</div>

                <div class="title">@yield('headline')</div>

                <div class="desc">
                    @yield('message')
                </div>

                <div class="info-card">
                    <div class="info-icon">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             width="24"
                             height="24"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="1.8">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M11.25 3.75h1.5M12 7.5v4.5m0 0v3m0-3h3m-3 0H9m10.5 0a7.5 7.5 0 11-15 0 7.5 7.5 0 0115 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="info-title">O que fazer agora?</div>
                        <div class="info-text">
                            Você pode voltar à tela inicial, retornar para a página anterior
                            ou avisar o time de desenvolvimento caso o problema continue.
                        </div>
                    </div>
                </div>

                <div class="actions">
                    <a href="{{ url('/') }}" class="btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             width="20"
                             height="20"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M3 10.5 12 3l9 7.5M5.25 9.75V20.25a.75.75 0 0 0 .75.75h3.75v-5.25A.75.75 0 0 1 10.5 15h3a.75.75 0 0 1 .75.75V21H18a.75.75 0 0 0 .75-.75V9.75"/>
                        </svg>
                        Voltar ao início
                    </a>

                    <button type="button" onclick="history.back()" class="btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             width="20"
                             height="20"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                        </svg>
                        Voltar
                    </button>
                </div>

                <div class="footer-note">
                    Dica técnica: para exibir a página <strong>500</strong> personalizada localmente,
                    use temporariamente <strong>APP_DEBUG=false</strong> e rode
                    <strong>php artisan optimize:clear</strong>.
                </div>
            </div>

            <!-- DIREITA -->
            <div class="panel scene">
                <div class="scene-bg"></div>
                <div class="light-beam light-1"></div>
                <div class="light-beam light-2"></div>

                <div class="scene-topbar">
                    <div class="chip">CENA CINEMATOGRÁFICA • MODO ANIME ORIGINAL</div>
                    <div class="chip">@yield('scene_tag', 'Plantão digital')</div>
                </div>

                <div class="speech speech-cat">
                    <strong>@yield('cat_phrase', 'Preciso salvar a rede agora!')</strong>
                    <small>Gato enfermeiro em corrida máxima.</small>
                </div>

                <div class="speech speech-dog">
                    <strong>@yield('dog_phrase', 'Ei! Eu quero internet!')</strong>
                    <small>Cachorro enfermeiro em perseguição.</small>
                </div>

                <div class="alarm-ring"></div>

                <div class="speed-trails">
                    <span class="trail left t1"></span>
                    <span class="trail left t2"></span>
                    <span class="trail left t3"></span>
                    <span class="trail right t2"></span>
                    <span class="trail right t3"></span>
                    <span class="trail right t4"></span>
                </div>

                <div class="monitor">
                    <div class="monitor-screen">
                        <div class="screen-scan"></div>
                        <div class="monitor-code">@yield('code')</div>
                        <div class="monitor-label">@yield('monitor_text', 'Sistema em observação')</div>

                        <div class="screen-lines">
                            <svg viewBox="0 0 260 52" xmlns="http://www.w3.org/2000/svg">
                                <path class="ekg"
                                      d="M4 34 H28 L42 18 L58 42 L76 27 H104 L118 10 L136 44 L154 24 H190 L210 34 H256"/>
                            </svg>
                        </div>
                    </div>
                    <div class="monitor-stand"></div>
                </div>

                <!-- CABO DE REDE -->
                <div class="cable-wrap">
                    <svg viewBox="0 0 270 140" xmlns="http://www.w3.org/2000/svg">
                        <path d="M16 90 C62 24, 118 10, 212 52"
                              fill="none"
                              stroke="#27463C"
                              stroke-width="12"
                              stroke-linecap="round"/>
                        <g class="plug-glow" transform="translate(205 40)">
                            <rect width="44" height="28" rx="8" fill="#FFC857"/>
                            <rect x="6" y="3" width="32" height="10" rx="3" fill="#FFF1BE"/>
                            <path d="M10 16v8M16 16v8M22 16v8M28 16v8M34 16v8"
                                  stroke="#9E6C00"
                                  stroke-width="2"
                                  stroke-linecap="round"/>
                        </g>
                    </svg>
                </div>

                <!-- GATO -->
                <div class="char cat">
                    <svg viewBox="0 0 220 300" xmlns="http://www.w3.org/2000/svg">
                        <ellipse cx="104" cy="276" rx="62" ry="12" fill="rgba(0,0,0,.14)"/>

                        <!-- pernas -->
                        <rect class="limb-a" x="78" y="190" width="20" height="64" rx="10" fill="#F9B56A"/>
                        <rect class="limb-b" x="118" y="190" width="20" height="64" rx="10" fill="#F9B56A"/>
                        <ellipse cx="88" cy="260" rx="18" ry="9" fill="#D97706"/>
                        <ellipse cx="128" cy="260" rx="18" ry="9" fill="#D97706"/>

                        <!-- corpo -->
                        <path d="M54 92 C64 78, 154 78, 166 96 L162 190 C140 208, 86 208, 58 192 Z"
                              fill="#FFFFFF" stroke="#1F4A3E" stroke-width="3"/>
                        <path d="M75 102 C88 94, 132 94, 146 104 L146 180 C128 192, 92 192, 75 180 Z"
                              fill="#F0FFF8"/>

                        <!-- cruz -->
                        <rect x="128" y="118" width="24" height="8" rx="4" fill="#15D087"/>
                        <rect x="136" y="110" width="8" height="24" rx="4" fill="#15D087"/>

                        <!-- braço esquerdo -->
                        <rect x="40" y="120" width="22" height="70" rx="11" fill="#FFFFFF" stroke="#1F4A3E" stroke-width="3" transform="rotate(20 40 120)"/>
                        <circle cx="42" cy="178" r="12" fill="#F9B56A"/>

                        <!-- braço direito -->
                        <rect x="156" y="114" width="22" height="74" rx="11" fill="#FFFFFF" stroke="#1F4A3E" stroke-width="3" transform="rotate(-22 156 114)"/>
                        <circle cx="178" cy="166" r="12" fill="#F9B56A"/>

                        <!-- cabeça -->
                        <circle cx="110" cy="64" r="46" fill="#F59E0B"/>
                        <path d="M74 36 L88 8 L101 38 Z" fill="#F59E0B"/>
                        <path d="M119 38 L132 8 L146 36 Z" fill="#F59E0B"/>
                        <path d="M81 34 L89 18 L97 34 Z" fill="#FFEDD5"/>
                        <path d="M124 34 L132 18 L139 34 Z" fill="#FFEDD5"/>

                        <!-- rosto -->
                        <ellipse cx="110" cy="75" rx="28" ry="21" fill="#FFF2DF"/>
                        <circle cx="96" cy="62" r="4" fill="#172A24"/>
                        <circle cx="124" cy="62" r="4" fill="#172A24"/>
                        <path d="M104 70 L116 70 L110 76 Z" fill="#FB7185"/>
                        <path d="M101 82 Q110 90 119 82" stroke="#7C2D12" stroke-width="3" fill="none" stroke-linecap="round"/>
                        <path d="M82 70 H60M83 78 H54M138 70 H160M137 78 H166"
                              stroke="#8B5E34" stroke-width="2.5" stroke-linecap="round"/>

                        <!-- touca -->
                        <rect x="80" y="18" width="60" height="20" rx="10" fill="#FFFFFF" stroke="#1F4A3E" stroke-width="2"/>
                        <rect x="105" y="16" width="10" height="24" rx="4" fill="#15D087"/>
                        <rect x="98" y="23" width="24" height="10" rx="4" fill="#15D087"/>

                        <!-- cauda -->
                        <path class="tail-a" d="M165 122 C194 104, 201 80, 190 50"
                              stroke="#D97706" stroke-width="15" fill="none" stroke-linecap="round"/>

                        <!-- estetoscópio -->
                        <path d="M86 124 C86 152, 134 152, 134 124"
                              stroke="#26453E" stroke-width="4" fill="none" stroke-linecap="round"/>
                        <circle cx="110" cy="158" r="10" fill="#26453E"/>
                        <circle cx="110" cy="158" r="4" fill="#E8FFF5"/>
                    </svg>
                </div>

                <!-- CACHORRO -->
                <div class="char dog">
                    <svg viewBox="0 0 230 308" xmlns="http://www.w3.org/2000/svg">
                        <ellipse cx="114" cy="284" rx="64" ry="12" fill="rgba(0,0,0,.14)"/>

                        <!-- pernas -->
                        <rect class="limb-b" x="82" y="200" width="22" height="66" rx="11" fill="#DDB081"/>
                        <rect class="limb-a" x="126" y="200" width="22" height="66" rx="11" fill="#DDB081"/>
                        <ellipse cx="93" cy="272" rx="19" ry="9" fill="#7C4A24"/>
                        <ellipse cx="137" cy="272" rx="19" ry="9" fill="#7C4A24"/>

                        <!-- corpo -->
                        <path d="M58 98 C72 82, 162 82, 176 100 L172 198 C148 214, 92 214, 62 198 Z"
                              fill="#FFFFFF" stroke="#1F4A3E" stroke-width="3"/>
                        <path d="M76 108 C90 100, 142 100, 158 110 L158 186 C140 198, 96 198, 76 186 Z"
                              fill="#F2FFF9"/>

                        <!-- cruz -->
                        <rect x="136" y="124" width="24" height="8" rx="4" fill="#15D087"/>
                        <rect x="144" y="116" width="8" height="24" rx="4" fill="#15D087"/>

                        <!-- braço -->
                        <rect x="40" y="128" width="22" height="72" rx="11" fill="#FFFFFF" stroke="#1F4A3E" stroke-width="3" transform="rotate(18 40 128)"/>
                        <rect x="170" y="122" width="22" height="72" rx="11" fill="#FFFFFF" stroke="#1F4A3E" stroke-width="3" transform="rotate(-18 170 122)"/>
                        <circle cx="38" cy="188" r="12" fill="#DDB081"/>
                        <circle cx="191" cy="178" r="12" fill="#DDB081"/>

                        <!-- cabeça -->
                        <circle cx="116" cy="70" r="48" fill="#C78A57"/>
                        <ellipse cx="74" cy="53" rx="17" ry="27" fill="#8B5A2B" transform="rotate(-22 74 53)"/>
                        <ellipse cx="158" cy="53" rx="17" ry="27" fill="#8B5A2B" transform="rotate(22 158 53)"/>

                        <!-- rosto -->
                        <ellipse cx="116" cy="84" rx="30" ry="23" fill="#FCE7CF"/>
                        <circle cx="102" cy="69" r="4" fill="#172A24"/>
                        <circle cx="130" cy="69" r="4" fill="#172A24"/>
                        <ellipse cx="116" cy="81" rx="8" ry="5" fill="#172A24"/>
                        <path d="M107 92 Q116 102 125 92" stroke="#7C2D12" stroke-width="3" fill="none" stroke-linecap="round"/>

                        <!-- touca -->
                        <rect x="86" y="22" width="62" height="20" rx="10" fill="#FFFFFF" stroke="#1F4A3E" stroke-width="2"/>
                        <rect x="112" y="20" width="10" height="24" rx="4" fill="#15D087"/>
                        <rect x="105" y="27" width="24" height="10" rx="4" fill="#15D087"/>

                        <!-- rabo -->
                        <path class="tail-b" d="M178 132 C204 116, 215 122, 222 150"
                              stroke="#8B5A2B" stroke-width="15" fill="none" stroke-linecap="round"/>

                        <!-- wifi -->
                        <g transform="translate(184 88)">
                            <path d="M0 22 C10 12, 26 12, 36 22" fill="none" stroke="@yield('accent', #15D087)" stroke-width="4" stroke-linecap="round"/>
                            <path d="M6 30 C14 22, 22 22, 30 30" fill="none" stroke="@yield('accent', #15D087)" stroke-width="4" stroke-linecap="round"/>
                            <circle cx="18" cy="36" r="4" fill="@yield('accent', #15D087)"/>
                        </g>
                    </svg>
                </div>

                <div class="particles">
                    <span></span><span></span><span></span><span></span>
                </div>

                <div class="floor"></div>

                <div class="subtitle-box">
                    <strong>@yield('caption_title', 'Cena do plantão digital')</strong>
                    <span>@yield('caption_text', 'Quando o sistema entra em estado crítico, o gato enfermeiro corre com o cabo de rede enquanto o cachorro enfermeiro persegue a conexão.')</span>
                </div>
            </div>

        </section>
    </main>

</body>
</html>