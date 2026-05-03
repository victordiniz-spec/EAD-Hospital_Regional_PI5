<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Erro') - Integrar ReSaúde</title>

    @vite('resources/css/app.css')

    <style>
        :root{
            --brand:#004D3A;
            --brand-2:#0C7B59;
            --bg:#F3F7F3;
            --soft:#EAF5EF;
            --line:#D9E7DE;
            --text:#14342B;
            --muted:#678077;
            --white:#ffffff;
            --danger:#DC2626;
            --warn:#F59E0B;
            --ok:#16A34A;
        }

        *{
            box-sizing:border-box;
        }

        body{
            margin:0;
            background:
                radial-gradient(circle at 20% 20%, rgba(22,163,74,.08), transparent 28%),
                radial-gradient(circle at 85% 15%, rgba(12,123,89,.10), transparent 25%),
                radial-gradient(circle at 80% 80%, rgba(245,158,11,.08), transparent 25%),
                linear-gradient(135deg, #f4faf6 0%, #eef6f1 45%, #f8fbf9 100%);
            color:var(--text);
            overflow-x:hidden;
        }

        .grid-bg{
            position:fixed;
            inset:0;
            background-image:
                linear-gradient(rgba(0,77,58,.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0,77,58,.04) 1px, transparent 1px);
            background-size:32px 32px;
            pointer-events:none;
        }

        .main-wrap{
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:24px;
            position:relative;
        }

        .content-grid{
            width:100%;
            max-width:1400px;
            display:grid;
            grid-template-columns: 1.02fr .98fr;
            gap:28px;
            align-items:center;
        }

        .panel{
            background:rgba(255,255,255,.82);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border:1px solid rgba(255,255,255,.7);
            border-radius:32px;
            box-shadow:
                0 20px 40px rgba(0,0,0,.08),
                inset 0 1px 0 rgba(255,255,255,.65);
        }

        .left-panel{
            padding:34px;
            position:relative;
            overflow:hidden;
        }

        .left-panel::before{
            content:"";
            position:absolute;
            right:-120px;
            top:-120px;
            width:260px;
            height:260px;
            background:radial-gradient(circle, rgba(12,123,89,.12), transparent 65%);
            border-radius:50%;
        }

        .badge{
            display:inline-flex;
            align-items:center;
            gap:10px;
            background:var(--soft);
            color:var(--brand);
            padding:10px 16px;
            border-radius:999px;
            font-size:13px;
            font-weight:800;
            letter-spacing:.04em;
        }

        .badge .dot{
            width:10px;
            height:10px;
            border-radius:50%;
            background:@yield('dot_color', #16A34A);
            box-shadow:0 0 0 0 rgba(22,163,74,.4);
            animation:dotPulse 1.8s infinite;
        }

        @keyframes dotPulse{
            0%{ box-shadow:0 0 0 0 rgba(22,163,74,.35);}
            70%{ box-shadow:0 0 0 10px rgba(22,163,74,0);}
            100%{ box-shadow:0 0 0 0 rgba(22,163,74,0);}
        }

        .overline{
            margin-top:24px;
            font-size:12px;
            letter-spacing:.35em;
            text-transform:uppercase;
            color:#7C948A;
            font-weight:800;
        }

        .error-code{
            margin:8px 0 0;
            font-size:clamp(72px, 10vw, 120px);
            line-height:.95;
            letter-spacing:-.05em;
            font-weight:900;
            color:@yield('code_color', #004D3A);
        }

        .title{
            margin:16px 0 0;
            font-size:clamp(28px, 4vw, 42px);
            line-height:1.06;
            font-weight:900;
            color:var(--text);
        }

        .desc{
            margin-top:16px;
            font-size:17px;
            line-height:1.8;
            color:var(--muted);
            max-width:700px;
        }

        .tips-box{
            margin-top:26px;
            padding:20px;
            border-radius:24px;
            background:rgba(255,255,255,.9);
            border:1px solid var(--line);
            display:flex;
            gap:14px;
            align-items:flex-start;
        }

        .tips-icon{
            width:48px;
            height:48px;
            border-radius:16px;
            background:var(--soft);
            color:var(--brand);
            display:flex;
            align-items:center;
            justify-content:center;
            flex-shrink:0;
        }

        .tips-title{
            font-size:16px;
            font-weight:800;
            color:var(--text);
        }

        .tips-text{
            font-size:14px;
            line-height:1.7;
            color:var(--muted);
            margin-top:4px;
        }

        .actions{
            display:flex;
            flex-wrap:wrap;
            gap:14px;
            margin-top:28px;
        }

        .btn-main{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:10px;
            background:var(--brand);
            color:#fff;
            padding:15px 24px;
            border-radius:18px;
            text-decoration:none;
            font-weight:800;
            box-shadow:0 10px 24px rgba(0,77,58,.18);
            transition:.25s ease;
        }

        .btn-main:hover{
            transform:translateY(-2px);
            background:#003A2C;
        }

        .btn-soft{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:10px;
            background:#fff;
            color:var(--brand);
            padding:15px 24px;
            border-radius:18px;
            border:1px solid var(--line);
            text-decoration:none;
            font-weight:800;
            transition:.25s ease;
            cursor:pointer;
        }

        .btn-soft:hover{
            transform:translateY(-2px);
            background:#F9FCFA;
        }

        .foot-note{
            margin-top:24px;
            font-size:13px;
            color:#8AA095;
        }

        .scene-wrap{
            position:relative;
            min-height:620px;
            display:flex;
            align-items:center;
            justify-content:center;
        }

        .scene-card{
            position:relative;
            width:100%;
            max-width:680px;
            min-height:620px;
            border-radius:34px;
            overflow:hidden;
            background:
                linear-gradient(180deg, #ffffff 0%, #f6fbf8 68%, #ecf6ef 100%);
            border:1px solid rgba(255,255,255,.85);
            box-shadow:
                0 25px 50px rgba(0,0,0,.08),
                inset 0 1px 0 rgba(255,255,255,.8);
        }

        .scene-header{
            position:absolute;
            top:18px;
            left:18px;
            right:18px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            z-index:10;
        }

        .scene-chip{
            background:#fff;
            border:1px solid var(--line);
            border-radius:999px;
            padding:10px 14px;
            font-size:13px;
            font-weight:800;
            color:var(--brand);
            box-shadow:0 6px 18px rgba(0,0,0,.05);
        }

        .speech{
            position:absolute;
            max-width:200px;
            background:#fff;
            border:1px solid var(--line);
            border-radius:24px;
            padding:14px 16px;
            box-shadow:0 12px 24px rgba(0,0,0,.06);
            z-index:9;
            animation:floatBubble 3.4s ease-in-out infinite;
        }

        .speech strong{
            display:block;
            font-size:14px;
            color:var(--text);
            line-height:1.4;
        }

        .speech small{
            display:block;
            margin-top:4px;
            color:var(--muted);
            font-size:12px;
            line-height:1.5;
        }

        .speech::after{
            content:"";
            position:absolute;
            width:18px;
            height:18px;
            background:#fff;
            border-left:1px solid var(--line);
            border-bottom:1px solid var(--line);
            transform:rotate(-45deg);
        }

        .speech-cat{
            left:28px;
            top:82px;
        }

        .speech-cat::after{
            bottom:-8px;
            left:28px;
        }

        .speech-dog{
            right:28px;
            top:130px;
            animation-delay:.7s;
        }

        .speech-dog::after{
            bottom:-8px;
            right:30px;
        }

        @keyframes floatBubble{
            0%,100%{ transform:translateY(0);}
            50%{ transform:translateY(-8px);}
        }

        .corridor{
            position:absolute;
            inset:auto 0 0 0;
            height:170px;
            background:
                linear-gradient(180deg, transparent 0%, transparent 25%, rgba(0,77,58,.04) 25%, rgba(0,77,58,.04) 28%, transparent 28%, transparent 100%),
                linear-gradient(180deg, #E8F2EB 0%, #DDEAE1 100%);
            border-top:1px solid #D5E3DA;
        }

        .corridor::before{
            content:"";
            position:absolute;
            left:0;
            right:0;
            top:28px;
            height:8px;
            background:repeating-linear-gradient(
                90deg,
                #C6D7CC 0 40px,
                transparent 40px 72px
            );
            opacity:.75;
        }

        .monitor{
            position:absolute;
            left:50%;
            transform:translateX(-50%);
            bottom:170px;
            width:280px;
            height:220px;
            border-radius:30px;
            background:#fff;
            border:8px solid #D2E3D8;
            box-shadow:0 20px 32px rgba(0,0,0,.08);
            overflow:hidden;
            animation:monitorBlink 2.4s ease-in-out infinite;
        }

        @keyframes monitorBlink{
            0%,100%{ transform:translateX(-50%) scale(1);}
            50%{ transform:translateX(-50%) scale(1.01);}
        }

        .monitor-screen{
            position:absolute;
            inset:18px 18px 52px 18px;
            border-radius:20px;
            background:
                radial-gradient(circle at 25% 22%, rgba(255,255,255,.1), transparent 25%),
                linear-gradient(135deg, #083B31 0%, #052E27 100%);
            display:flex;
            flex-direction:column;
            align-items:center;
            justify-content:center;
            overflow:hidden;
        }

        .monitor-glow{
            position:absolute;
            inset:auto;
            width:180px;
            height:180px;
            border-radius:50%;
            background:radial-gradient(circle, rgba(255,255,255,.08), transparent 70%);
            animation:monitorGlow 3.2s ease-in-out infinite;
        }

        @keyframes monitorGlow{
            0%,100%{ transform:scale(1); opacity:.6;}
            50%{ transform:scale(1.14); opacity:.9;}
        }

        .monitor-code{
            position:relative;
            z-index:2;
            font-size:64px;
            font-weight:900;
            letter-spacing:-.04em;
            color:@yield('monitor_color', #22C55E);
            line-height:1;
        }

        .monitor-label{
            position:relative;
            z-index:2;
            margin-top:10px;
            font-size:15px;
            font-weight:800;
            color:#E2F3EB;
            text-align:center;
            padding:0 18px;
        }

        .monitor-wave{
            position:absolute;
            left:18px;
            right:18px;
            bottom:18px;
            height:58px;
        }

        .monitor-wave svg{
            width:100%;
            height:100%;
        }

        .wave-path{
            fill:none;
            stroke:@yield('monitor_color', #22C55E);
            stroke-width:6;
            stroke-linecap:round;
            stroke-linejoin:round;
            stroke-dasharray:220;
            animation:dashRun 2.2s linear infinite;
        }

        @keyframes dashRun{
            from{ stroke-dashoffset:220; }
            to{ stroke-dashoffset:0; }
        }

        .monitor-base{
            position:absolute;
            left:50%;
            transform:translateX(-50%);
            bottom:18px;
            width:120px;
            height:22px;
            background:#A9BDB1;
            border-radius:999px;
        }

        .server-box{
            position:absolute;
            left:50%;
            transform:translateX(-50%);
            bottom:110px;
            width:120px;
            height:70px;
            background:#fff;
            border:5px solid #D2E3D8;
            border-radius:22px;
            box-shadow:0 10px 20px rgba(0,0,0,.05);
        }

        .server-led{
            position:absolute;
            width:10px;
            height:10px;
            border-radius:50%;
            background:#22C55E;
            top:18px;
            right:16px;
            animation:ledBlink 1.5s infinite;
        }

        .server-led:nth-child(2){
            right:34px;
            animation-delay:.4s;
            background:#F59E0B;
        }

        .server-led:nth-child(3){
            right:52px;
            animation-delay:.8s;
            background:#22C55E;
        }

        @keyframes ledBlink{
            0%,100%{ opacity:1; transform:scale(1);}
            50%{ opacity:.3; transform:scale(.8);}
        }

        .cat{
            position:absolute;
            left:70px;
            bottom:118px;
            width:190px;
            height:270px;
            animation:catRun 3.2s ease-in-out infinite;
            z-index:8;
        }

        @keyframes catRun{
            0%,100%{ transform:translateX(0) translateY(0);}
            25%{ transform:translateX(12px) translateY(-4px);}
            50%{ transform:translateX(24px) translateY(0);}
            75%{ transform:translateX(12px) translateY(-5px);}
        }

        .dog{
            position:absolute;
            right:70px;
            bottom:114px;
            width:200px;
            height:280px;
            animation:dogRun 3.2s ease-in-out infinite;
            z-index:7;
        }

        @keyframes dogRun{
            0%,100%{ transform:translateX(0) translateY(0);}
            25%{ transform:translateX(-8px) translateY(-3px);}
            50%{ transform:translateX(-18px) translateY(0);}
            75%{ transform:translateX(-8px) translateY(-4px);}
        }

        .leg-a{ animation:legA .55s ease-in-out infinite; transform-origin:top center; }
        .leg-b{ animation:legB .55s ease-in-out infinite; transform-origin:top center; }

        @keyframes legA{
            0%,100%{ transform:rotate(12deg);}
            50%{ transform:rotate(-14deg);}
        }

        @keyframes legB{
            0%,100%{ transform:rotate(-14deg);}
            50%{ transform:rotate(12deg);}
        }

        .tail-cat{ animation:tailCat .9s ease-in-out infinite; transform-origin:left center; }
        .tail-dog{ animation:tailDog .5s ease-in-out infinite; transform-origin:left center; }

        @keyframes tailCat{
            0%,100%{ transform:rotate(12deg);}
            50%{ transform:rotate(-8deg);}
        }

        @keyframes tailDog{
            0%,100%{ transform:rotate(20deg);}
            50%{ transform:rotate(-18deg);}
        }

        .cable{
            position:absolute;
            left:200px;
            bottom:240px;
            width:230px;
            height:120px;
            z-index:6;
            animation:cableMove 2.2s ease-in-out infinite;
        }

        @keyframes cableMove{
            0%,100%{ transform:rotate(0deg) translateY(0);}
            50%{ transform:rotate(-4deg) translateY(-3px);}
        }

        .rj45{
            animation:rjSwing 1s ease-in-out infinite;
            transform-origin:center;
        }

        @keyframes rjSwing{
            0%,100%{ transform:rotate(8deg);}
            50%{ transform:rotate(-8deg);}
        }

        .wifi-rings circle{
            fill:none;
            stroke:@yield('monitor_color', #22C55E);
            stroke-width:5;
            stroke-linecap:round;
            opacity:0;
            transform-origin:center;
            animation:wifiPulse 1.8s infinite;
        }

        .wifi-rings circle:nth-child(2){ animation-delay:.35s; }
        .wifi-rings circle:nth-child(3){ animation-delay:.7s; }

        @keyframes wifiPulse{
            0%{ opacity:0; transform:scale(.72);}
            35%{ opacity:1; }
            100%{ opacity:0; transform:scale(1.18);}
        }

        .speed-lines-left,
        .speed-lines-right{
            position:absolute;
            top:260px;
            width:90px;
            height:120px;
            opacity:.45;
        }

        .speed-lines-left{ left:12px; }
        .speed-lines-right{ right:12px; }

        .speed-lines-left span,
        .speed-lines-right span{
            display:block;
            height:8px;
            border-radius:999px;
            background:linear-gradient(90deg, transparent, rgba(0,77,58,.2), rgba(0,77,58,.55));
            margin:16px 0;
            animation:lineSpeed 1s linear infinite;
        }

        .speed-lines-right span{
            background:linear-gradient(270deg, transparent, rgba(0,77,58,.2), rgba(0,77,58,.55));
        }

        .speed-lines-left span:nth-child(2),
        .speed-lines-right span:nth-child(2){ width:70px; animation-delay:.2s; }
        .speed-lines-left span:nth-child(1),
        .speed-lines-right span:nth-child(1){ width:56px; }
        .speed-lines-left span:nth-child(3),
        .speed-lines-right span:nth-child(3){ width:82px; animation-delay:.35s; }

        @keyframes lineSpeed{
            0%{ transform:translateX(0); opacity:.2;}
            50%{ opacity:.7;}
            100%{ transform:translateX(8px); opacity:.2;}
        }

        .scene-caption{
            position:absolute;
            left:50%;
            transform:translateX(-50%);
            bottom:28px;
            width:calc(100% - 36px);
            text-align:center;
            background:rgba(255,255,255,.9);
            border:1px solid var(--line);
            border-radius:22px;
            padding:14px 16px;
            box-shadow:0 10px 20px rgba(0,0,0,.05);
            z-index:10;
        }

        .scene-caption strong{
            display:block;
            font-size:15px;
            color:var(--text);
            font-weight:900;
        }

        .scene-caption span{
            display:block;
            margin-top:4px;
            font-size:13px;
            color:var(--muted);
            line-height:1.55;
        }

        .dust{
            position:absolute;
            bottom:120px;
            width:10px;
            height:10px;
            border-radius:50%;
            background:rgba(0,77,58,.12);
            filter:blur(1px);
            animation:dustFloat 1.5s ease-in-out infinite;
        }

        .dust.d1{ left:240px; animation-delay:0s; }
        .dust.d2{ left:262px; animation-delay:.3s; }
        .dust.d3{ right:245px; animation-delay:.6s; }
        .dust.d4{ right:272px; animation-delay:.9s; }

        @keyframes dustFloat{
            0%,100%{ transform:translateY(0) scale(1); opacity:.2;}
            50%{ transform:translateY(-10px) scale(1.2); opacity:.55;}
        }

        @media (max-width: 1100px){
            .content-grid{
                grid-template-columns:1fr;
            }

            .scene-wrap{
                min-height:560px;
            }

            .scene-card{
                min-height:560px;
            }
        }

        @media (max-width: 640px){
            .main-wrap{
                padding:14px;
            }

            .left-panel{
                padding:24px;
                border-radius:26px;
            }

            .scene-card{
                border-radius:28px;
                min-height:520px;
            }

            .scene-wrap{
                min-height:520px;
            }

            .cat{
                left:24px;
                transform:scale(.82);
                transform-origin:bottom left;
            }

            .dog{
                right:18px;
                transform:scale(.82);
                transform-origin:bottom right;
            }

            .monitor{
                width:220px;
                height:190px;
                bottom:160px;
            }

            .monitor-code{
                font-size:52px;
            }

            .speech-cat{
                left:14px;
                top:76px;
                max-width:160px;
            }

            .speech-dog{
                right:14px;
                top:126px;
                max-width:160px;
            }

            .cable{
                left:110px;
                bottom:226px;
                width:180px;
            }
        }
    </style>
</head>
<body>
    <div class="grid-bg"></div>

    <main class="main-wrap">
        <div class="content-grid">

            <!-- COLUNA ESQUERDA -->
            <section class="panel left-panel">

                <div class="badge">
                    <span class="dot"></span>
                    Integrar ReSaúde — Tela de Erro Criativa
                </div>

                <div class="overline">Diagnóstico do Sistema</div>

                <h1 class="error-code">@yield('code')</h1>

                <h2 class="title">@yield('headline')</h2>

                <p class="desc">
                    @yield('message')
                </p>

                <div class="tips-box">
                    <div class="tips-icon">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             width="24"
                             height="24"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M11.25 3.75h1.5M12 7.5v4.5m0 0v3m0-3h3m-3 0H9m10.5 0a7.5 7.5 0 11-15 0 7.5 7.5 0 0115 0z"/>
                        </svg>
                    </div>

                    <div>
                        <div class="tips-title">O que você pode fazer agora?</div>
                        <div class="tips-text">
                            Tente voltar para a página inicial, retornar para a tela anterior
                            ou avisar o time de desenvolvimento caso o problema continue.
                        </div>
                    </div>
                </div>

                <div class="actions">
                    <a href="{{ url('/') }}" class="btn-main">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             width="20"
                             height="20"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 10.5 12 3l9 7.5M5.25 9.75V20.25a.75.75 0 0 0 .75.75h3.75v-5.25A.75.75 0 0 1 10.5 15h3a.75.75 0 0 1 .75.75V21H18a.75.75 0 0 0 .75-.75V9.75"/>
                        </svg>
                        Voltar ao início
                    </a>

                    <button class="btn-soft" onclick="history.back()">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             width="20"
                             height="20"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                        </svg>
                        Voltar
                    </button>
                </div>

                <div class="foot-note">
                    Dica: para a tela <strong>500</strong> aparecer bonitinha no ambiente local,
                    deixe temporariamente <strong>APP_DEBUG=false</strong> e rode
                    <strong>php artisan optimize:clear</strong>.
                </div>

            </section>

            <!-- COLUNA DIREITA -->
            <section class="scene-wrap">
                <div class="scene-card">

                    <div class="scene-header">
                        <div class="scene-chip">🎬 Mini filme do plantão digital</div>
                        <div class="scene-chip">@yield('scene_tag', 'Correria no corredor')</div>
                    </div>

                    <div class="speech speech-cat">
                        <strong>@yield('cat_phrase', 'Corre! O cabo é nosso!')</strong>
                        <small>Gato enfermeiro em modo emergência.</small>
                    </div>

                    <div class="speech speech-dog">
                        <strong>@yield('dog_phrase', 'Ei! Eu só quero internet!')</strong>
                        <small>Cachorro enfermeiro desesperado pelo Wi‑Fi.</small>
                    </div>

                    <div class="speed-lines-left">
                        <span></span><span></span><span></span>
                    </div>

                    <div class="speed-lines-right">
                        <span></span><span></span><span></span>
                    </div>

                    <div class="monitor">
                        <div class="monitor-screen">
                            <div class="monitor-glow"></div>
                            <div class="monitor-code">@yield('code')</div>
                            <div class="monitor-label">@yield('monitor_text', 'Sistema em observação')</div>

                            <div class="monitor-wave">
                                <svg viewBox="0 0 220 58" xmlns="http://www.w3.org/2000/svg">
                                    <path class="wave-path"
                                          d="M4 36 H28 L40 18 L56 44 L72 28 H100 L115 12 L132 46 L150 26 H182 L196 34 H216"/>
                                </svg>
                            </div>
                        </div>
                        <div class="monitor-base"></div>
                    </div>

                    <div class="server-box">
                        <span class="server-led"></span>
                        <span class="server-led"></span>
                        <span class="server-led"></span>
                    </div>

                    <!-- CABO -->
                    <div class="cable">
                        <svg viewBox="0 0 240 120" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16 86 C60 12, 120 6, 194 46"
                                  fill="none"
                                  stroke="#28433C"
                                  stroke-width="12"
                                  stroke-linecap="round"/>
                            <g class="rj45" transform="translate(186 35)">
                                <rect width="42" height="26" rx="8" fill="#F59E0B"/>
                                <rect x="6" y="3" width="30" height="10" rx="3" fill="#FFE6A8"/>
                                <path d="M8 16v7M14 16v7M20 16v7M26 16v7M32 16v7"
                                      stroke="#8B5E00"
                                      stroke-width="2"
                                      stroke-linecap="round"/>
                            </g>
                        </svg>
                    </div>

                    <!-- GATO -->
                    <div class="cat">
                        <svg viewBox="0 0 190 270" xmlns="http://www.w3.org/2000/svg">
                            <!-- sombra -->
                            <ellipse cx="92" cy="252" rx="56" ry="12" fill="rgba(0,0,0,.08)"/>

                            <!-- pernas -->
                            <g>
                                <rect class="leg-a" x="62" y="176" width="18" height="58" rx="9" fill="#FDBA74"/>
                                <rect class="leg-b" x="98" y="176" width="18" height="58" rx="9" fill="#FDBA74"/>
                                <ellipse cx="70" cy="238" rx="16" ry="8" fill="#D97706"/>
                                <ellipse cx="106" cy="238" rx="16" ry="8" fill="#D97706"/>
                            </g>

                            <!-- corpo -->
                            <rect x="42" y="78" width="82" height="106" rx="34" fill="#F59E0B"/>
                            <rect x="52" y="96" width="62" height="74" rx="28" fill="#FFF7ED"/>

                            <!-- roupa enfermeiro -->
                            <path d="M34 86 C44 70, 120 70, 132 86 L132 174 C110 186, 60 186, 34 174 Z"
                                  fill="#FFFFFF" stroke="#D8E5DD" stroke-width="3"/>
                            <path d="M74 84 H92 V158 H74 Z" fill="#ECFDF5"/>
                            <rect x="97" y="106" width="22" height="8" rx="4" fill="#16A34A"/>
                            <rect x="104" y="99" width="8" height="22" rx="4" fill="#16A34A"/>

                            <!-- braços -->
                            <rect x="20" y="104" width="22" height="68" rx="11" fill="#FFFFFF" stroke="#D8E5DD" stroke-width="3" transform="rotate(18 20 104)"/>
                            <rect x="120" y="102" width="22" height="70" rx="11" fill="#FFFFFF" stroke="#D8E5DD" stroke-width="3" transform="rotate(-18 120 102)"/>

                            <!-- mão -->
                            <circle cx="149" cy="146" r="12" fill="#FDBA74"/>
                            <circle cx="34" cy="153" r="12" fill="#FDBA74"/>

                            <!-- cabeça -->
                            <circle cx="84" cy="54" r="42" fill="#F59E0B"/>
                            <path d="M52 28 L64 6 L76 30 Z" fill="#F59E0B"/>
                            <path d="M92 30 L104 6 L116 28 Z" fill="#F59E0B"/>
                            <path d="M58 28 L65 14 L71 28 Z" fill="#FFEDD5"/>
                            <path d="M97 28 L104 14 L110 28 Z" fill="#FFEDD5"/>

                            <!-- rosto -->
                            <ellipse cx="84" cy="64" rx="24" ry="18" fill="#FFF7ED"/>
                            <circle cx="72" cy="52" r="4" fill="#1F2937"/>
                            <circle cx="96" cy="52" r="4" fill="#1F2937"/>
                            <path d="M80 60 L88 60 L84 65 Z" fill="#FB7185"/>
                            <path d="M76 70 Q84 76 92 70" stroke="#7C2D12" stroke-width="3" fill="none" stroke-linecap="round"/>

                            <!-- bigodes -->
                            <path d="M59 60 H42M59 66 H38M109 60 H126M109 66 H130" stroke="#8B5E34" stroke-width="2.6" stroke-linecap="round"/>

                            <!-- touca -->
                            <rect x="58" y="14" width="52" height="18" rx="9" fill="#FFFFFF" stroke="#D8E5DD" stroke-width="2"/>
                            <rect x="79" y="12" width="10" height="24" rx="4" fill="#16A34A"/>
                            <rect x="72" y="19" width="24" height="10" rx="4" fill="#16A34A"/>

                            <!-- cauda -->
                            <path class="tail-cat" d="M124 120 C150 100, 158 74, 148 42"
                                  stroke="#D97706"
                                  stroke-width="14"
                                  fill="none"
                                  stroke-linecap="round"/>

                            <!-- estetoscópio -->
                            <path d="M56 110 C56 138, 112 138, 112 110"
                                  stroke="#26453E"
                                  stroke-width="4"
                                  fill="none"
                                  stroke-linecap="round"/>
                            <circle cx="84" cy="146" r="10" fill="#26453E"/>
                            <circle cx="84" cy="146" r="4" fill="#EAF5EF"/>
                        </svg>
                    </div>

                    <!-- CACHORRO -->
                    <div class="dog">
                        <svg viewBox="0 0 200 280" xmlns="http://www.w3.org/2000/svg">
                            <!-- sombra -->
                            <ellipse cx="102" cy="260" rx="60" ry="12" fill="rgba(0,0,0,.08)"/>

                            <!-- pernas -->
                            <rect class="leg-b" x="68" y="186" width="20" height="60" rx="10" fill="#D6A56D"/>
                            <rect class="leg-a" x="110" y="186" width="20" height="60" rx="10" fill="#D6A56D"/>
                            <ellipse cx="78" cy="248" rx="17" ry="8" fill="#7C4A24"/>
                            <ellipse cx="120" cy="248" rx="17" ry="8" fill="#7C4A24"/>

                            <!-- corpo -->
                            <rect x="48" y="82" width="92" height="112" rx="38" fill="#C58A55"/>
                            <path d="M52 90 C72 72, 120 72, 138 90 L138 178 C116 192, 78 194, 52 178 Z"
                                  fill="#FFFFFF" stroke="#D8E5DD" stroke-width="3"/>
                            <rect x="88" y="112" width="10" height="24" rx="4" fill="#16A34A"/>
                            <rect x="81" y="119" width="24" height="10" rx="4" fill="#16A34A"/>

                            <!-- braços -->
                            <rect x="24" y="112" width="22" height="64" rx="11" fill="#FFFFFF" stroke="#D8E5DD" stroke-width="3" transform="rotate(20 24 112)"/>
                            <rect x="138" y="112" width="22" height="64" rx="11" fill="#FFFFFF" stroke="#D8E5DD" stroke-width="3" transform="rotate(-20 138 112)"/>
                            <circle cx="28" cy="164" r="12" fill="#D6A56D"/>
                            <circle cx="160" cy="162" r="12" fill="#D6A56D"/>

                            <!-- cabeça -->
                            <circle cx="96" cy="58" r="44" fill="#C58A55"/>
                            <ellipse cx="58" cy="44" rx="16" ry="24" fill="#8B5A2B" transform="rotate(-22 58 44)"/>
                            <ellipse cx="136" cy="44" rx="16" ry="24" fill="#8B5A2B" transform="rotate(22 136 44)"/>

                            <!-- rosto -->
                            <ellipse cx="96" cy="68" rx="26" ry="20" fill="#FAE3C8"/>
                            <circle cx="84" cy="55" r="4" fill="#1F2937"/>
                            <circle cx="108" cy="55" r="4" fill="#1F2937"/>
                            <ellipse cx="96" cy="66" rx="7" ry="5" fill="#1F2937"/>
                            <path d="M88 76 Q96 86 104 76" stroke="#7C2D12" stroke-width="3" fill="none" stroke-linecap="round"/>

                            <!-- touca -->
                            <rect x="68" y="16" width="56" height="18" rx="9" fill="#FFFFFF" stroke="#D8E5DD" stroke-width="2"/>
                            <rect x="91" y="14" width="10" height="24" rx="4" fill="#16A34A"/>
                            <rect x="84" y="21" width="24" height="10" rx="4" fill="#16A34A"/>

                            <!-- rabo -->
                            <path class="tail-dog" d="M142 126 C168 112, 180 118, 188 144"
                                  stroke="#8B5A2B"
                                  stroke-width="14"
                                  fill="none"
                                  stroke-linecap="round"/>

                            <!-- wi-fi -->
                            <g class="wifi-rings" transform="translate(154 78)">
                                <circle cx="0" cy="0" r="10"/>
                                <circle cx="0" cy="0" r="20"/>
                                <circle cx="0" cy="0" r="30"/>
                            </g>
                        </svg>
                    </div>

                    <span class="dust d1"></span>
                    <span class="dust d2"></span>
                    <span class="dust d3"></span>
                    <span class="dust d4"></span>

                    <div class="corridor"></div>

                    <div class="scene-caption">
                        <strong>@yield('caption_title', 'Cena do plantão digital')</strong>
                        <span>@yield('caption_text', 'Quando alguma parte do sistema falha, até o gato enfermeiro entra em corrida para salvar a internet do hospital.')</span>
                    </div>

                </div>
            </section>

        </div>
    </main>
</body>
</html>