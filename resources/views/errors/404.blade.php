<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#061711">
    <title>404 | Integrar ReSaúde</title>

    @vite('resources/css/app.css')

    <style>
        :root {
            --bg: #061711;
            --bg-2: #09231a;
            --text: #f4fff9;
            --muted: #9fc8b8;
            --soft: rgba(255,255,255,.075);
            --soft-2: rgba(255,255,255,.045);
            --line: rgba(186,255,226,.16);
            --line-2: rgba(186,255,226,.28);
            --green: #30d596;
            --green-2: #8ff4c6;
            --green-3: #0d7655;
            --blue: #8bd7ff;
            --danger: #ff6177;
            --shadow: 0 40px 110px rgba(0,0,0,.45);
            --mx: 0px;
            --my: 0px;
            --rx: 0deg;
            --ry: 0deg;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            min-height: 100%;
            overflow-x: hidden;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 16% 12%, rgba(48,213,150,.16), transparent 28%),
                radial-gradient(circle at 84% 14%, rgba(139,215,255,.10), transparent 26%),
                radial-gradient(circle at 48% 92%, rgba(48,213,150,.10), transparent 30%),
                linear-gradient(135deg, #04100c 0%, #071711 42%, #0a2118 100%);
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            opacity: .38;
            background-image:
                linear-gradient(rgba(186,255,226,.07) 1px, transparent 1px),
                linear-gradient(90deg, rgba(186,255,226,.07) 1px, transparent 1px);
            background-size: 36px 36px;
            mask-image: radial-gradient(circle at center, black 30%, transparent 92%);
        }

        body::after {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            opacity: .13;
            background:
                linear-gradient(180deg, rgba(255,255,255,.06), transparent 22%, transparent 76%, rgba(255,255,255,.05)),
                repeating-linear-gradient(0deg, rgba(255,255,255,.03) 0 1px, transparent 1px 4px);
            mix-blend-mode: screen;
        }

        .page-404 {
            min-height: 100vh;
            width: 100%;
            display: grid;
            grid-template-columns: minmax(360px, 560px) minmax(420px, 1fr);
            gap: 18px;
            padding: 18px;
            position: relative;
        }

        .left-panel,
        .right-panel {
            position: relative;
            border: 1px solid var(--line);
            background:
                linear-gradient(180deg, rgba(255,255,255,.065), rgba(255,255,255,.025)),
                rgba(5, 20, 15, .72);
            border-radius: 34px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(18px);
            overflow: hidden;
        }

        .left-panel::before,
        .right-panel::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 20% 0%, rgba(143,244,198,.14), transparent 34%),
                linear-gradient(180deg, rgba(255,255,255,.07), transparent 18%);
            pointer-events: none;
        }

        .left-content {
            position: relative;
            z-index: 2;
            min-height: calc(100vh - 36px);
            padding: clamp(30px, 4vw, 58px);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .brand-pill {
            width: max-content;
            max-width: 100%;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 11px 16px;
            border-radius: 999px;
            border: 1px solid rgba(143,244,198,.22);
            background: rgba(143,244,198,.08);
            color: #eefff8;
            font-weight: 900;
            font-size: 12px;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .brand-pill span {
            width: 11px;
            height: 11px;
            border-radius: 50%;
            background: var(--green);
            box-shadow: 0 0 18px rgba(48,213,150,.7);
            animation: livePulse 1.7s ease-in-out infinite;
            flex: 0 0 auto;
        }

        @keyframes livePulse {
            0%, 100% { transform: scale(.85); opacity: .75; }
            50% { transform: scale(1.18); opacity: 1; }
        }

        .label {
            margin-top: 34px;
            color: #8bd4b6;
            text-transform: uppercase;
            letter-spacing: .42em;
            font-size: 12px;
            font-weight: 950;
        }

        .error-code {
            margin-top: 16px;
            font-size: clamp(112px, 16vw, 210px);
            line-height: .82;
            font-weight: 1000;
            letter-spacing: -.08em;
            color: #f5fff9;
            text-shadow:
                0 1px 0 rgba(255,255,255,.3),
                0 22px 70px rgba(0,0,0,.34),
                0 0 45px rgba(48,213,150,.12);
        }

        .headline {
            margin: 8px 0 0;
            font-size: clamp(30px, 3.6vw, 54px);
            line-height: 1.02;
            font-weight: 1000;
            letter-spacing: -.055em;
            color: #f5fff9;
        }

        .headline em {
            font-style: normal;
            color: var(--green-2);
        }

        .description {
            margin: 20px 0 0;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.82;
            max-width: 680px;
        }

        .status-card {
            margin-top: 28px;
            display: grid;
            grid-template-columns: 58px 1fr;
            gap: 16px;
            padding: 19px;
            border-radius: 24px;
            border: 1px solid rgba(143,244,198,.12);
            background: rgba(255,255,255,.045);
        }

        .status-icon {
            width: 58px;
            height: 58px;
            border-radius: 20px;
            display: grid;
            place-items: center;
            color: #eafff5;
            border: 1px solid rgba(143,244,198,.18);
            background: linear-gradient(180deg, rgba(48,213,150,.18), rgba(48,213,150,.055));
            box-shadow: inset 0 1px 0 rgba(255,255,255,.09);
        }

        .status-title {
            font-weight: 950;
            color: #f4fff9;
            font-size: 16px;
        }

        .status-text {
            margin-top: 5px;
            color: var(--muted);
            line-height: 1.65;
            font-size: 14px;
        }

        .metrics {
            margin-top: 16px;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .metric {
            padding: 16px;
            border-radius: 21px;
            border: 1px solid rgba(143,244,198,.10);
            background: rgba(255,255,255,.035);
        }

        .metric small {
            display: block;
            color: #8ccfb5;
            text-transform: uppercase;
            letter-spacing: .16em;
            font-weight: 900;
            font-size: 10px;
        }

        .metric strong {
            display: block;
            margin-top: 8px;
            color: #f5fff9;
            font-size: 15px;
            line-height: 1.35;
        }

        .actions {
            margin-top: 28px;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .btn-404 {
            appearance: none;
            border: 0;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 52px;
            padding: 14px 22px;
            border-radius: 18px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 950;
            transition: transform .22s ease, box-shadow .22s ease, background .22s ease;
        }

        .btn-home {
            color: #062016;
            background: linear-gradient(180deg, #91f5c8, #30d596 70%);
            box-shadow: 0 18px 38px rgba(48,213,150,.22);
        }

        .btn-back {
            color: #eefdf7;
            border: 1px solid rgba(255,255,255,.10);
            background: rgba(255,255,255,.055);
        }

        .btn-404:hover {
            transform: translateY(-2px);
        }

        .btn-home:hover {
            box-shadow: 0 22px 45px rgba(48,213,150,.28);
        }

        .fine-print {
            margin-top: 24px;
            color: #7fbda4;
            font-size: 12px;
            line-height: 1.7;
        }

        .right-panel {
            min-height: calc(100vh - 36px);
        }

        .experience {
            position: absolute;
            inset: 0;
            overflow: hidden;
            perspective: 1800px;
            cursor: grab;
        }

        .experience:active {
            cursor: grabbing;
        }

        .scene {
            position: absolute;
            inset: 0;
            transform-style: preserve-3d;
            transform: rotateX(var(--rx)) rotateY(var(--ry));
            transition: transform .16s ease-out;
        }

        .scene::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at calc(50% + var(--mx)) calc(45% + var(--my)), rgba(143,244,198,.18), transparent 30%),
                radial-gradient(circle at 50% 60%, rgba(139,215,255,.08), transparent 38%);
            transform: translateZ(-180px);
            pointer-events: none;
        }

        .topbar-scene {
            position: absolute;
            z-index: 25;
            top: 18px;
            left: 18px;
            right: 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            transform: translateZ(180px);
        }

        .scene-chip {
            padding: 11px 15px;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,.10);
            background: rgba(255,255,255,.055);
            backdrop-filter: blur(10px);
            color: #f1fff9;
            font-size: 11px;
            font-weight: 950;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .big-bg-code {
            position: absolute;
            left: 50%;
            top: 53%;
            transform: translate(-50%, -50%) translateZ(-220px);
            font-size: clamp(220px, 30vw, 520px);
            line-height: .8;
            font-weight: 1000;
            letter-spacing: -.09em;
            color: rgba(255,255,255,.045);
            user-select: none;
            pointer-events: none;
            text-shadow: 0 0 80px rgba(48,213,150,.09);
        }

        .aura {
            position: absolute;
            left: 50%;
            top: 50%;
            width: min(58vw, 620px);
            height: min(58vw, 620px);
            border-radius: 50%;
            transform: translate(-50%, -48%) translateZ(-90px);
            background: radial-gradient(circle, rgba(143,244,198,.24), rgba(48,213,150,.07) 38%, transparent 68%);
            filter: blur(8px);
        }

        .orbit {
            position: absolute;
            left: 50%;
            top: 50%;
            width: min(56vw, 600px);
            height: min(56vw, 600px);
            border-radius: 50%;
            border: 1px solid rgba(143,244,198,.18);
            transform: translate(-50%, -48%) translateZ(-40px) rotateX(68deg);
            box-shadow:
                0 0 0 34px rgba(143,244,198,.025),
                0 0 0 72px rgba(143,244,198,.018),
                inset 0 0 60px rgba(48,213,150,.035);
            animation: orbitPulse 3.4s ease-in-out infinite;
        }

        @keyframes orbitPulse {
            0%, 100% { opacity: .95; transform: translate(-50%, -48%) translateZ(-40px) rotateX(68deg) scale(1); }
            50% { opacity: .55; transform: translate(-50%, -48%) translateZ(-40px) rotateX(68deg) scale(1.045); }
        }

        .helmet-area {
            position: absolute;
            left: 50%;
            top: 50%;
            width: min(82vw, 640px);
            max-width: 92%;
            aspect-ratio: 1 / 1;
            transform: translate(-50%, -48%) translateZ(120px) translateX(calc(var(--mx) * .14)) translateY(calc(var(--my) * .14));
            transform-style: preserve-3d;
        }

        .helmet-3d {
            position: absolute;
            inset: 0;
            transform-style: preserve-3d;
            filter: drop-shadow(0 44px 55px rgba(0,0,0,.45));
            animation: helmetFloat 4.6s ease-in-out infinite;
        }

        @keyframes helmetFloat {
            0%, 100% { transform: translateY(0) rotateZ(-1deg); }
            50% { transform: translateY(-14px) rotateZ(1.2deg); }
        }

        .helmet-svg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            transform: translateZ(44px) rotateX(calc(var(--ry) * -.38)) rotateY(calc(var(--rx) * -.38));
        }

        .visor-light {
            position: absolute;
            left: 48%;
            top: 46%;
            width: 47%;
            height: 17%;
            border-radius: 999px 999px 46px 46px;
            transform: translate(-50%, -50%) translateZ(110px) rotate(-5deg);
            background: linear-gradient(100deg, transparent 0%, rgba(255,255,255,.06) 30%, rgba(255,255,255,.26) 48%, rgba(255,255,255,.05) 66%, transparent 100%);
            mix-blend-mode: screen;
            filter: blur(.7px);
            opacity: .9;
            pointer-events: none;
        }

        .helmet-shadow {
            position: absolute;
            left: 50%;
            bottom: 10%;
            width: 70%;
            height: 48px;
            border-radius: 50%;
            transform: translateX(-50%) translateZ(-40px);
            background: radial-gradient(circle, rgba(0,0,0,.46), rgba(0,0,0,.12) 56%, transparent 75%);
            filter: blur(12px);
        }

        .data-card {
            position: absolute;
            z-index: 12;
            width: 230px;
            padding: 17px;
            border-radius: 24px;
            border: 1px solid rgba(143,244,198,.15);
            background: rgba(6, 22, 16, .72);
            backdrop-filter: blur(14px);
            box-shadow: 0 24px 58px rgba(0,0,0,.28);
            transform-style: preserve-3d;
        }

        .data-card small {
            display: block;
            color: #8dd2b6;
            text-transform: uppercase;
            letter-spacing: .16em;
            font-size: 10px;
            font-weight: 950;
        }

        .data-card strong {
            display: block;
            margin-top: 9px;
            color: #f4fff9;
            font-weight: 950;
            font-size: 15px;
            line-height: 1.35;
        }

        .data-card p {
            margin: 7px 0 0;
            color: var(--muted);
            line-height: 1.55;
            font-size: 12px;
        }

        .card-a {
            left: 34px;
            top: 110px;
            transform: translateZ(170px) translateX(calc(var(--mx) * -.11)) translateY(calc(var(--my) * -.08));
        }

        .card-b {
            right: 34px;
            top: 150px;
            transform: translateZ(190px) translateX(calc(var(--mx) * .10)) translateY(calc(var(--my) * -.08));
        }

        .card-c {
            left: 62px;
            bottom: 120px;
            width: 210px;
            transform: translateZ(155px) translateX(calc(var(--mx) * -.08)) translateY(calc(var(--my) * .10));
        }

        .ecg-panel {
            position: absolute;
            right: 38px;
            bottom: 118px;
            z-index: 12;
            width: 250px;
            padding: 17px;
            border-radius: 24px;
            border: 1px solid rgba(143,244,198,.15);
            background: rgba(6, 22, 16, .72);
            backdrop-filter: blur(14px);
            transform: translateZ(175px) translateX(calc(var(--mx) * .08)) translateY(calc(var(--my) * .08));
        }

        .ecg-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            color: #eafff5;
            font-size: 12px;
            font-weight: 950;
            text-transform: uppercase;
            letter-spacing: .12em;
        }

        .ecg-title span {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: var(--green);
            box-shadow: 0 0 14px rgba(48,213,150,.9);
        }

        .ecg-line {
            margin-top: 14px;
            height: 56px;
            overflow: hidden;
            border-radius: 16px;
            background: rgba(255,255,255,.035);
            border: 1px solid rgba(255,255,255,.06);
        }

        .ecg-line svg {
            width: 200%;
            height: 100%;
            animation: ecgMove 2.4s linear infinite;
        }

        @keyframes ecgMove {
            from { transform: translateX(0); }
            to { transform: translateX(-50%); }
        }

        .floor {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 220px;
            transform: translateZ(-150px);
            background:
                linear-gradient(180deg, rgba(255,255,255,.03), transparent 32%),
                linear-gradient(180deg, #10271d, #06110d);
            border-top: 1px solid rgba(143,244,198,.12);
        }

        .floor::before {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            top: 26px;
            height: 8px;
            opacity: .8;
            background: repeating-linear-gradient(90deg, rgba(143,244,198,.18) 0 50px, transparent 50px 88px);
        }

        .floor::after {
            content: "";
            position: absolute;
            left: 50%;
            top: 54px;
            width: 60%;
            height: 70px;
            transform: translateX(-50%);
            border-radius: 50%;
            background: radial-gradient(circle, rgba(0,0,0,.35), transparent 70%);
            filter: blur(14px);
        }

        .particle {
            position: absolute;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: rgba(143,244,198,.82);
            box-shadow: 0 0 20px rgba(143,244,198,.55);
            animation: particleUp 5s linear infinite;
            opacity: 0;
        }

        .p-1 { left: 28%; top: 66%; animation-delay: .1s; }
        .p-2 { left: 72%; top: 70%; animation-delay: 1s; }
        .p-3 { left: 44%; top: 78%; animation-delay: 1.8s; }
        .p-4 { left: 62%; top: 46%; animation-delay: 2.7s; }
        .p-5 { left: 35%; top: 38%; animation-delay: 3.4s; }

        @keyframes particleUp {
            0% { transform: translateY(30px) scale(.6); opacity: 0; }
            20% { opacity: .75; }
            100% { transform: translateY(-90px) scale(1.18); opacity: 0; }
        }

        .bottom-caption {
            position: absolute;
            z-index: 30;
            left: 18px;
            right: 18px;
            bottom: 18px;
            padding: 15px 18px;
            border-radius: 22px;
            border: 1px solid rgba(143,244,198,.16);
            background: rgba(5, 20, 15, .76);
            backdrop-filter: blur(14px);
            text-align: center;
            transform: translateZ(230px);
        }

        .bottom-caption strong {
            display: block;
            font-weight: 950;
            font-size: 14px;
            color: #f2fff8;
        }

        .bottom-caption span {
            display: block;
            margin-top: 4px;
            color: var(--muted);
            line-height: 1.55;
            font-size: 12px;
        }

        .click-pulse .helmet-3d {
            animation: clickPop .5s ease;
        }

        @keyframes clickPop {
            0% { transform: scale(1); }
            50% { transform: scale(1.035) translateY(-10px); }
            100% { transform: scale(1); }
        }

        @media (max-width: 1180px) {
            .page-404 {
                grid-template-columns: 1fr;
            }

            .left-content,
            .right-panel {
                min-height: auto;
            }

            .right-panel {
                min-height: 720px;
            }
        }

        @media (max-width: 760px) {
            .page-404 {
                padding: 12px;
            }

            .left-panel,
            .right-panel {
                border-radius: 26px;
            }

            .left-content {
                padding: 26px;
            }

            .brand-pill {
                width: 100%;
                justify-content: center;
                text-align: center;
                font-size: 10px;
            }

            .description {
                font-size: 14px;
            }

            .metrics {
                grid-template-columns: 1fr;
            }

            .actions {
                flex-direction: column;
            }

            .btn-404 {
                width: 100%;
            }

            .right-panel {
                min-height: 650px;
            }

            .experience {
                perspective: 1200px;
            }

            .topbar-scene {
                top: 12px;
                left: 12px;
                right: 12px;
            }

            .scene-chip {
                font-size: 9px;
                padding: 9px 11px;
            }

            .helmet-area {
                width: 96%;
                top: 50%;
            }

            .big-bg-code {
                font-size: 190px;
            }

            .orbit {
                width: 320px;
                height: 320px;
            }

            .data-card {
                width: 160px;
                padding: 13px;
                border-radius: 18px;
            }

            .data-card strong {
                font-size: 12px;
            }

            .data-card p {
                font-size: 10px;
            }

            .card-a {
                left: 10px;
                top: 82px;
            }

            .card-b {
                right: 10px;
                top: 124px;
            }

            .card-c {
                display: none;
            }

            .ecg-panel {
                left: 12px;
                right: 12px;
                bottom: 92px;
                width: auto;
            }

            .bottom-caption {
                left: 12px;
                right: 12px;
                bottom: 12px;
            }
        }
    </style>
</head>
<body>
    <main class="page-404">
        <section class="left-panel">
            <div class="left-content">
                <div class="brand-pill">
                    <span></span>
                    Integrar ReSaúde • Sistema em monitoramento
                </div>

                <div class="label">erro de rota</div>
                <div class="error-code">404</div>

                <h1 class="headline">
                    Esta página saiu do <em>trajeto.</em>
                </h1>

                <p class="description">
                    A rota que você tentou acessar não foi encontrada. Talvez o endereço tenha mudado,
                    sido removido ou não exista mais no sistema. Enquanto isso, o ambiente continua ativo
                    e você pode voltar para uma área segura da plataforma.
                </p>

                <div class="status-card">
                    <div class="status-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v7.5M8.25 12h7.5"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                    </div>

                    <div>
                        <div class="status-title">Navegação interrompida com segurança</div>
                        <div class="status-text">
                            Não se preocupe: isso não afeta seus dados. Use uma das opções abaixo para retornar ao fluxo principal.
                        </div>
                    </div>
                </div>

                <div class="metrics">
                    <div class="metric">
                        <small>status</small>
                        <strong>Rota não encontrada</strong>
                    </div>
                    <div class="metric">
                        <small>ambiente</small>
                        <strong>Integrar ReSaúde</strong>
                    </div>
                </div>

                <div class="actions">
                    <a href="{{ url('/') }}" class="btn-404 btn-home">
                        Voltar ao início
                    </a>

                    <button class="btn-404 btn-back" type="button" onclick="history.back()">
                        Voltar para a tela anterior
                    </button>
                </div>

                <div class="fine-print">
                    Erro 404 personalizado • experiência 3D interativa • tema institucional da saúde
                </div>
            </div>
        </section>

        <section class="right-panel">
            <div class="experience" id="experience404">
                <div class="scene" id="scene404">
                    <div class="topbar-scene">
                        <div class="scene-chip">3D interactive error room</div>
                        <div class="scene-chip">mova o mouse</div>
                    </div>

                    <div class="big-bg-code">404</div>
                    <div class="aura"></div>
                    <div class="orbit"></div>

                    <div class="data-card card-a">
                        <small>health route</small>
                        <strong>Destino não localizado</strong>
                        <p>O caminho solicitado não existe no mapa atual do sistema.</p>
                    </div>

                    <div class="data-card card-b">
                        <small>security layer</small>
                        <strong>Retorno seguro disponível</strong>
                        <p>Você pode voltar sem perder a sessão da plataforma.</p>
                    </div>

                    <div class="data-card card-c">
                        <small>diagnóstico</small>
                        <strong>404 confirmado</strong>
                        <p>A rota foi analisada e não retornou conteúdo válido.</p>
                    </div>

                    <div class="ecg-panel">
                        <div class="ecg-title">
                            sistema ativo
                            <span></span>
                        </div>

                        <div class="ecg-line">
                            <svg viewBox="0 0 600 100" preserveAspectRatio="none" aria-hidden="true">
                                <path d="M0 54 L70 54 L90 54 L108 20 L132 82 L158 54 L218 54 L238 54 L256 34 L278 70 L302 54 L370 54 L390 54 L410 18 L434 84 L460 54 L520 54 L540 54 L558 35 L580 70 L600 54"
                                      fill="none" stroke="#8ff4c6" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M600 54 L670 54 L690 54 L708 20 L732 82 L758 54 L818 54 L838 54 L856 34 L878 70 L902 54 L970 54 L990 54 L1010 18 L1034 84 L1060 54 L1120 54 L1140 54 L1158 35 L1180 70 L1200 54"
                                      fill="none" stroke="#8ff4c6" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </div>

                    <div class="helmet-area">
                        <div class="helmet-shadow"></div>

                        <div class="helmet-3d">
                            <svg class="helmet-svg" viewBox="0 0 640 640" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <defs>
                                    <linearGradient id="shell" x1=".12" y1=".06" x2=".86" y2=".92">
                                        <stop offset="0%" stop-color="#ffffff"/>
                                        <stop offset="43%" stop-color="#e9fff7"/>
                                        <stop offset="100%" stop-color="#95dfc1"/>
                                    </linearGradient>

                                    <linearGradient id="darkGreen" x1="0" y1="0" x2="1" y2="1">
                                        <stop offset="0%" stop-color="#0b8b62"/>
                                        <stop offset="100%" stop-color="#0a3d2f"/>
                                    </linearGradient>

                                    <linearGradient id="visor" x1="0" y1="0" x2="1" y2="0">
                                        <stop offset="0%" stop-color="#071914"/>
                                        <stop offset="48%" stop-color="#16392f"/>
                                        <stop offset="100%" stop-color="#081a15"/>
                                    </linearGradient>

                                    <linearGradient id="stripe" x1="0" y1="0" x2="1" y2="1">
                                        <stop offset="0%" stop-color="#91f5c8"/>
                                        <stop offset="100%" stop-color="#30d596"/>
                                    </linearGradient>

                                    <filter id="helmetDrop" x="-30%" y="-30%" width="160%" height="160%">
                                        <feDropShadow dx="0" dy="28" stdDeviation="20" flood-color="#000000" flood-opacity=".34"/>
                                    </filter>
                                </defs>

                                <ellipse cx="324" cy="530" rx="152" ry="36" fill="rgba(0,0,0,.18)"/>

                                <path filter="url(#helmetDrop)"
                                      d="M164 333
                                         C154 231 209 139 300 112
                                         C379 89 470 112 522 185
                                         C565 246 566 342 520 415
                                         C484 474 425 507 348 511
                                         L258 511
                                         C216 510 185 491 170 457
                                         C158 431 160 406 174 382
                                         C143 369 126 347 126 319
                                         C126 293 141 271 166 258
                                         C161 282 160 307 164 333Z"
                                      fill="url(#shell)"/>

                                <path d="M314 113
                                         C404 102 486 143 526 215
                                         C480 211 436 208 386 204
                                         C356 202 327 201 297 202
                                         C292 164 297 134 314 113Z"
                                      fill="url(#darkGreen)"/>

                                <path d="M218 167
                                         C263 128 343 112 426 134
                                         C410 152 400 174 395 199
                                         C318 190 259 197 205 222
                                         C198 198 201 181 218 167Z"
                                      fill="url(#stripe)"/>

                                <g transform="translate(285 158)">
                                    <rect width="70" height="70" rx="20" fill="#ffffff" stroke="#0a7d59" stroke-width="5"/>
                                    <rect x="29" y="13" width="12" height="44" rx="6" fill="#30d596"/>
                                    <rect x="13" y="29" width="44" height="12" rx="6" fill="#30d596"/>
                                </g>

                                <path d="M186 255
                                         C240 229 346 224 456 248
                                         C486 255 505 273 507 295
                                         C509 317 493 334 460 346
                                         C386 373 295 378 203 360
                                         C170 353 152 336 149 313
                                         C146 289 160 268 186 255Z"
                                      fill="url(#visor)" stroke="rgba(255,255,255,.22)" stroke-width="5"/>

                                <path d="M198 266
                                         C255 245 342 246 436 264
                                         C390 272 337 276 286 273
                                         C250 271 220 268 198 266Z"
                                      fill="rgba(255,255,255,.15)"/>

                                <path d="M190 235
                                         C247 213 307 209 382 217
                                         C382 228 380 238 377 247
                                         C335 242 291 244 251 252
                                         L234 253 L224 229 L208 276 L193 235Z"
                                      fill="#30d596"/>

                                <path d="M180 393
                                         C214 382 259 378 308 379
                                         C305 425 318 469 345 511
                                         L258 511
                                         C216 510 185 491 170 457
                                         C158 429 160 407 180 393Z"
                                      fill="#0c7656"/>

                                <path d="M374 389
                                         C430 383 477 365 508 336
                                         C529 361 537 392 527 426
                                         C506 485 444 512 348 511
                                         C368 465 376 423 374 389Z"
                                      fill="#f0fff8"/>

                                <g transform="translate(204 411)">
                                    <rect width="93" height="44" rx="18" fill="#071914"/>
                                    <rect x="16" y="12" width="60" height="5" rx="3" fill="#8ff4c6"/>
                                    <rect x="16" y="26" width="60" height="5" rx="3" fill="#8ff4c6"/>
                                </g>

                                <path d="M335 118 C431 123 492 170 516 244" stroke="rgba(255,255,255,.26)" stroke-width="8" fill="none" stroke-linecap="round"/>
                                <path d="M173 307 C224 289 280 286 338 292" stroke="rgba(255,255,255,.16)" stroke-width="7" fill="none" stroke-linecap="round"/>
                            </svg>

                            <div class="visor-light"></div>
                        </div>
                    </div>

                    <div class="particle p-1"></div>
                    <div class="particle p-2"></div>
                    <div class="particle p-3"></div>
                    <div class="particle p-4"></div>
                    <div class="particle p-5"></div>

                    <div class="floor"></div>

                    <div class="bottom-caption">
                        <strong>Capacete 3D interativo • rota não encontrada</strong>
                        <span>Experiência visual inspirada em páginas premium, com identidade própria do Integrar ReSaúde.</span>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        (function () {
            const experience = document.getElementById('experience404');
            const root = document.documentElement;

            if (!experience || !root) return;

            let raf = null;

            function update(clientX, clientY) {
                const rect = experience.getBoundingClientRect();
                const x = clientX - rect.left;
                const y = clientY - rect.top;

                const px = (x / rect.width) - 0.5;
                const py = (y / rect.height) - 0.5;

                const rotateX = py * -14;
                const rotateY = px * 18;
                const moveX = px * 42;
                const moveY = py * 34;

                root.style.setProperty('--rx', rotateX.toFixed(2) + 'deg');
                root.style.setProperty('--ry', rotateY.toFixed(2) + 'deg');
                root.style.setProperty('--mx', moveX.toFixed(2) + 'px');
                root.style.setProperty('--my', moveY.toFixed(2) + 'px');
            }

            function reset() {
                root.style.setProperty('--rx', '0deg');
                root.style.setProperty('--ry', '0deg');
                root.style.setProperty('--mx', '0px');
                root.style.setProperty('--my', '0px');
            }

            experience.addEventListener('mousemove', function (event) {
                if (raf) cancelAnimationFrame(raf);
                raf = requestAnimationFrame(function () {
                    update(event.clientX, event.clientY);
                });
            });

            experience.addEventListener('mouseleave', reset);

            experience.addEventListener('click', function () {
                experience.classList.add('click-pulse');

                setTimeout(function () {
                    experience.classList.remove('click-pulse');
                }, 520);
            });

            experience.addEventListener('touchmove', function (event) {
                if (!event.touches || !event.touches[0]) return;
                const touch = event.touches[0];
                update(touch.clientX, touch.clientY);
            }, { passive: true });

            experience.addEventListener('touchend', reset);
        })();
    </script>
</body>
</html>
