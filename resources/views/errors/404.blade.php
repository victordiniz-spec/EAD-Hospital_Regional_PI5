<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro 404 - Página não encontrada</title>
    @vite('resources/css/app.css')

    <style>
        :root{
            --bg:#071712;
            --bg-soft:#0c211a;
            --panel:rgba(9, 30, 23, .82);
            --line:rgba(46, 116, 92, .35);
            --text:#ecfff7;
            --muted:#9fc7ba;
            --brand:#16d087;
            --brand-soft:rgba(22,208,135,.15);
            --shadow:0 20px 50px rgba(0,0,0,.30);
        }

        *{ box-sizing:border-box; }
        body{
            margin:0;
            min-height:100vh;
            font-family: Inter, Arial, sans-serif;
            color:var(--text);
            background:
                radial-gradient(circle at 15% 20%, rgba(22,208,135,.12), transparent 25%),
                radial-gradient(circle at 80% 10%, rgba(0,255,200,.06), transparent 20%),
                linear-gradient(135deg, #03110d 0%, #071712 100%);
            overflow-x:hidden;
        }

        .grid-bg{
            position:fixed;
            inset:0;
            background-image:
                linear-gradient(rgba(36,100,80,.12) 1px, transparent 1px),
                linear-gradient(90deg, rgba(36,100,80,.12) 1px, transparent 1px);
            background-size:32px 32px;
            opacity:.35;
            pointer-events:none;
        }

        .page{
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:24px;
        }

        .wrapper{
            width:100%;
            max-width:1400px;
            display:grid;
            grid-template-columns: 1fr 1fr;
            gap:24px;
        }

        .card{
            background:var(--panel);
            border:1px solid var(--line);
            border-radius:30px;
            box-shadow:var(--shadow);
            backdrop-filter: blur(10px);
            overflow:hidden;
            position:relative;
        }

        .left{
            padding:42px;
        }

        .badge{
            display:inline-flex;
            align-items:center;
            gap:10px;
            padding:12px 18px;
            border-radius:999px;
            border:1px solid rgba(22,208,135,.2);
            background:rgba(22,208,135,.09);
            color:#e8fff6;
            font-weight:800;
            font-size:13px;
            letter-spacing:.04em;
        }

        .badge-dot{
            width:12px;
            height:12px;
            border-radius:50%;
            background:var(--brand);
            box-shadow:0 0 0 0 rgba(22,208,135,.5);
            animation:pulse 1.8s infinite;
        }

        @keyframes pulse{
            0%{ box-shadow:0 0 0 0 rgba(22,208,135,.45); }
            70%{ box-shadow:0 0 0 12px rgba(22,208,135,0); }
            100%{ box-shadow:0 0 0 0 rgba(22,208,135,0); }
        }

        .eyebrow{
            margin-top:34px;
            font-size:14px;
            text-transform:uppercase;
            letter-spacing:.35em;
            font-weight:800;
            color:#8cc8b0;
        }

        .code{
            margin-top:20px;
            font-size:clamp(96px, 16vw, 180px);
            line-height:.9;
            font-weight:900;
            letter-spacing:-.06em;
            color:#eafff7;
        }

        .title{
            font-size:clamp(30px, 4vw, 46px);
            font-weight:900;
            margin-top:6px;
        }

        .desc{
            margin-top:18px;
            color:var(--muted);
            line-height:1.9;
            font-size:17px;
            max-width:700px;
        }

        .info-box{
            margin-top:30px;
            display:flex;
            gap:16px;
            align-items:flex-start;
            padding:22px;
            border-radius:24px;
            border:1px solid rgba(77,155,126,.2);
            background:rgba(255,255,255,.02);
        }

        .info-icon{
            width:56px;
            height:56px;
            border-radius:18px;
            background:var(--brand-soft);
            display:flex;
            align-items:center;
            justify-content:center;
            flex-shrink:0;
            border:1px solid rgba(22,208,135,.18);
        }

        .info-title{
            font-size:16px;
            font-weight:800;
        }

        .info-text{
            margin-top:4px;
            color:var(--muted);
            line-height:1.7;
        }

        .actions{
            display:flex;
            flex-wrap:wrap;
            gap:14px;
            margin-top:28px;
        }

        .btn{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:10px;
            padding:15px 24px;
            border-radius:18px;
            text-decoration:none;
            font-weight:800;
            transition:.25s ease;
        }

        .btn-primary{
            background:var(--brand);
            color:#062016;
            box-shadow:0 12px 28px rgba(22,208,135,.25);
        }

        .btn-primary:hover{
            transform:translateY(-2px);
        }

        .btn-secondary{
            background:rgba(255,255,255,.04);
            color:#eafff7;
            border:1px solid rgba(255,255,255,.08);
        }

        .btn-secondary:hover{
            transform:translateY(-2px);
        }

        .note{
            margin-top:26px;
            color:#7db09f;
            font-size:13px;
            line-height:1.7;
        }

        .scene{
            min-height:720px;
            position:relative;
            overflow:hidden;
            background:
                radial-gradient(circle at center, rgba(22,208,135,.08), transparent 40%);
        }

        .scene-header{
            position:absolute;
            top:18px;
            left:18px;
            right:18px;
            display:flex;
            justify-content:space-between;
            gap:12px;
            flex-wrap:wrap;
            z-index:10;
        }

        .chip{
            padding:12px 18px;
            border-radius:999px;
            background:rgba(255,255,255,.04);
            border:1px solid rgba(255,255,255,.08);
            color:#f2fff9;
            font-size:13px;
            font-weight:800;
        }

        .bubble{
            position:absolute;
            padding:16px 18px;
            border-radius:24px;
            background:rgba(10, 29, 22, .92);
            border:1px solid rgba(75,152,123,.24);
            box-shadow:0 16px 34px rgba(0,0,0,.20);
            max-width:250px;
            z-index:12;
            animation:floaty 3s ease-in-out infinite;
        }

        .bubble strong{
            display:block;
            line-height:1.5;
            font-size:16px;
        }

        .bubble small{
            display:block;
            margin-top:6px;
            color:#96c4b3;
            line-height:1.6;
            font-size:13px;
        }

        .bubble::after{
            content:"";
            position:absolute;
            width:14px;
            height:14px;
            background:rgba(10, 29, 22, .92);
            border-left:1px solid rgba(75,152,123,.24);
            border-bottom:1px solid rgba(75,152,123,.24);
            transform:rotate(-45deg);
        }

        .bubble-cat{
            left:38px;
            top:120px;
        }
        .bubble-cat::after{
            left:28px;
            bottom:-8px;
        }

        .bubble-dog{
            right:38px;
            top:160px;
            animation-delay:.8s;
        }
        .bubble-dog::after{
            right:28px;
            bottom:-8px;
        }

        @keyframes floaty{
            0%,100%{ transform:translateY(0); }
            50%{ transform:translateY(-8px); }
        }

        .monitor{
            position:absolute;
            top:135px;
            left:50%;
            transform:translateX(-50%);
            width:320px;
            height:250px;
            border-radius:30px;
            background:linear-gradient(180deg, rgba(255,255,255,.04), rgba(255,255,255,.02)), #0c231b;
            border:1px solid rgba(82,165,134,.26);
            box-shadow:0 18px 45px rgba(0,0,0,.25);
            z-index:8;
            overflow:hidden;
        }

        .monitor-screen{
            position:absolute;
            inset:16px 16px 56px;
            border-radius:20px;
            background:linear-gradient(180deg, #071b14, #09261d);
            display:flex;
            align-items:center;
            justify-content:center;
            flex-direction:column;
            overflow:hidden;
        }

        .monitor-screen::before{
            content:"";
            position:absolute;
            inset:0;
            background:linear-gradient(180deg, transparent, rgba(22,208,135,.09), transparent);
            transform:translateY(-100%);
            animation:scan 2.8s linear infinite;
        }

        @keyframes scan{
            to{ transform:translateY(100%); }
        }

        .monitor-code{
            position:relative;
            z-index:2;
            font-size:76px;
            font-weight:900;
            color:var(--brand);
            text-shadow:0 0 18px rgba(22,208,135,.18);
        }

        .monitor-text{
            position:relative;
            z-index:2;
            margin-top:10px;
            font-weight:800;
            font-size:16px;
            text-align:center;
            padding:0 20px;
        }

        .stand{
            position:absolute;
            bottom:18px;
            left:50%;
            transform:translateX(-50%);
            width:120px;
            height:16px;
            background:#44685b;
            border-radius:999px;
        }

        .ring{
            position:absolute;
            width:330px;
            height:330px;
            border-radius:50%;
            border:1px solid rgba(22,208,135,.14);
            left:50%;
            top:130px;
            transform:translateX(-50%);
            box-shadow:0 0 0 24px rgba(22,208,135,.03), 0 0 0 54px rgba(22,208,135,.02);
            animation:ring 2.2s ease-in-out infinite;
        }

        @keyframes ring{
            0%,100%{ transform:translateX(-50%) scale(1); opacity:.95; }
            50%{ transform:translateX(-50%) scale(1.05); opacity:.55; }
        }

        .floor{
            position:absolute;
            left:0;
            right:0;
            bottom:0;
            height:180px;
            background:linear-gradient(180deg, rgba(255,255,255,.03), rgba(255,255,255,0)), linear-gradient(180deg, #0d221b, #081611);
            border-top:1px solid rgba(73,154,124,.16);
        }

        .floor::before{
            content:"";
            position:absolute;
            left:0;
            right:0;
            top:32px;
            height:8px;
            background:repeating-linear-gradient(90deg, rgba(97,180,149,.18) 0 48px, transparent 48px 80px);
        }

        .character{
            position:absolute;
            bottom:88px;
            z-index:9;
        }

        .cat{
            left:55px;
            width:240px;
            animation:catRun 2.2s ease-in-out infinite;
        }

        .dog{
            right:45px;
            width:250px;
            animation:dogRun 2.2s ease-in-out infinite;
        }

        @keyframes catRun{
            0%,100%{ transform:translateX(0) translateY(0); }
            25%{ transform:translateX(8px) translateY(-4px); }
            50%{ transform:translateX(18px) translateY(0); }
            75%{ transform:translateX(8px) translateY(-5px); }
        }

        @keyframes dogRun{
            0%,100%{ transform:translateX(0) translateY(0); }
            25%{ transform:translateX(-8px) translateY(-4px); }
            50%{ transform:translateX(-16px) translateY(0); }
            75%{ transform:translateX(-8px) translateY(-5px); }
        }

        .subtitle{
            position:absolute;
            left:20px;
            right:20px;
            bottom:20px;
            z-index:11;
            padding:16px 20px;
            border-radius:22px;
            background:rgba(10, 29, 22, .92);
            border:1px solid rgba(75,152,123,.22);
            text-align:center;
        }

        .subtitle strong{
            display:block;
            font-size:15px;
            font-weight:900;
        }

        .subtitle span{
            display:block;
            margin-top:4px;
            color:#96c4b3;
            line-height:1.6;
            font-size:13px;
        }

        @media (max-width: 1100px){
            .wrapper{
                grid-template-columns:1fr;
            }
            .scene{
                min-height:680px;
            }
        }

        @media (max-width: 700px){
            .left{
                padding:28px;
            }
            .scene{
                min-height:620px;
            }
            .monitor{
                width:260px;
                height:220px;
            }
            .monitor-code{
                font-size:60px;
            }
            .cat{
                left:10px;
                width:190px;
            }
            .dog{
                right:10px;
                width:200px;
            }
            .bubble-cat{
                left:14px;
                top:90px;
                max-width:180px;
            }
            .bubble-dog{
                right:14px;
                top:145px;
                max-width:180px;
            }
        }
    </style>
</head>
<body>
<div class="grid-bg"></div>

<div class="page">
    <div class="wrapper">
        <section class="card left">
            <div class="badge">
                <span class="badge-dot"></span>
                INTEGRAR RESAÚDE • TELA DE ERRO FUTURISTA
            </div>

            <div class="eyebrow">STATUS DO SISTEMA</div>
            <div class="code">404</div>
            <div class="title">Página não encontrada</div>

            <div class="desc">
                O gato enfermeiro saiu correndo com o cabo de rede para salvar a conexão,
                o cachorro enfermeiro foi atrás querendo internet,
                e no meio dessa perseguição a página se perdeu do mapa.
                Parece que a rota não existe ou foi removida.
            </div>

            <div class="info-box">
                <div class="info-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 3.75h1.5M12 7.5v4.5m0 0v3m0-3h3m-3 0H9m10.5 0a7.5 7.5 0 11-15 0 7.5 7.5 0 0115 0z"/>
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
                <a href="{{ url('/') }}" class="btn btn-primary">Voltar ao início</a>
                <button onclick="history.back()" class="btn btn-secondary" type="button">Voltar</button>
            </div>

            <div class="note">
                Integrar ReSaúde • Erro 404 personalizado
            </div>
        </section>

        <section class="card scene">
            <div class="scene-header">
                <div class="chip">CENA CINEMATOGRÁFICA • MODO ANIME ORIGINAL</div>
                <div class="chip">Rota perdida</div>
            </div>

            <div class="bubble bubble-cat">
                <strong>Preciso salvar a rede agora!</strong>
                <small>Gato enfermeiro em corrida máxima.</small>
            </div>

            <div class="bubble bubble-dog">
                <strong>Ei! Eu quero internet!</strong>
                <small>Cachorro enfermeiro em perseguição.</small>
            </div>

            <div class="ring"></div>

            <div class="monitor">
                <div class="monitor-screen">
                    <div class="monitor-code">404</div>
                    <div class="monitor-text">Rota não encontrada</div>
                </div>
                <div class="stand"></div>
            </div>

            <div class="character cat">
                <svg viewBox="0 0 220 260" xmlns="http://www.w3.org/2000/svg">
                    <ellipse cx="110" cy="248" rx="50" ry="10" fill="rgba(0,0,0,.18)"/>
                    <rect x="84" y="145" width="18" height="70" rx="9" fill="#F4B06A"/>
                    <rect x="118" y="145" width="18" height="70" rx="9" fill="#F4B06A"/>
                    <ellipse cx="92" cy="220" rx="16" ry="8" fill="#D97706"/>
                    <ellipse cx="128" cy="220" rx="16" ry="8" fill="#D97706"/>
                    <rect x="60" y="70" width="100" height="100" rx="30" fill="#fff" stroke="#1e4f40" stroke-width="3"/>
                    <circle cx="110" cy="60" r="42" fill="#F59E0B"/>
                    <path d="M76 36 L88 10 L98 38 Z" fill="#F59E0B"/>
                    <path d="M122 38 L132 10 L144 36 Z" fill="#F59E0B"/>
                    <ellipse cx="110" cy="72" rx="25" ry="18" fill="#FFF2DF"/>
                    <circle cx="98" cy="60" r="4" fill="#172A24"/>
                    <circle cx="122" cy="60" r="4" fill="#172A24"/>
                    <path d="M104 70 L116 70 L110 76 Z" fill="#FB7185"/>
                    <path d="M102 82 Q110 90 118 82" stroke="#7C2D12" stroke-width="3" fill="none" stroke-linecap="round"/>
                    <rect x="84" y="20" width="52" height="18" rx="9" fill="#fff" stroke="#1e4f40" stroke-width="2"/>
                    <rect x="105" y="18" width="10" height="22" rx="3" fill="#16d087"/>
                    <rect x="98" y="25" width="24" height="8" rx="3" fill="#16d087"/>
                    <rect x="48" y="100" width="18" height="58" rx="9" fill="#fff" stroke="#1e4f40" stroke-width="3" transform="rotate(18 48 100)"/>
                    <circle cx="52" cy="154" r="10" fill="#F4B06A"/>
                    <rect x="154" y="96" width="18" height="58" rx="9" fill="#fff" stroke="#1e4f40" stroke-width="3" transform="rotate(-18 154 96)"/>
                    <circle cx="166" cy="146" r="10" fill="#F4B06A"/>
                    <path d="M160 118 C188 98, 194 76, 185 48" stroke="#D97706" stroke-width="14" fill="none" stroke-linecap="round"/>
                </svg>
            </div>

            <div class="character dog">
                <svg viewBox="0 0 220 260" xmlns="http://www.w3.org/2000/svg">
                    <ellipse cx="110" cy="248" rx="50" ry="10" fill="rgba(0,0,0,.18)"/>
                    <rect x="84" y="150" width="18" height="68" rx="9" fill="#DDB081"/>
                    <rect x="118" y="150" width="18" height="68" rx="9" fill="#DDB081"/>
                    <ellipse cx="92" cy="222" rx="16" ry="8" fill="#8B5A2B"/>
                    <ellipse cx="128" cy="222" rx="16" ry="8" fill="#8B5A2B"/>
                    <rect x="60" y="72" width="100" height="100" rx="30" fill="#fff" stroke="#1e4f40" stroke-width="3"/>
                    <circle cx="110" cy="62" r="42" fill="#C78A57"/>
                    <ellipse cx="76" cy="48" rx="14" ry="22" fill="#8B5A2B" transform="rotate(-20 76 48)"/>
                    <ellipse cx="144" cy="48" rx="14" ry="22" fill="#8B5A2B" transform="rotate(20 144 48)"/>
                    <ellipse cx="110" cy="78" rx="28" ry="20" fill="#FCE7CF"/>
                    <circle cx="98" cy="64" r="4" fill="#172A24"/>
                    <circle cx="122" cy="64" r="4" fill="#172A24"/>
                    <ellipse cx="110" cy="76" rx="7" ry="5" fill="#172A24"/>
                    <path d="M102 88 Q110 96 118 88" stroke="#7C2D12" stroke-width="3" fill="none" stroke-linecap="round"/>
                    <rect x="84" y="22" width="52" height="18" rx="9" fill="#fff" stroke="#1e4f40" stroke-width="2"/>
                    <rect x="105" y="20" width="10" height="22" rx="3" fill="#16d087"/>
                    <rect x="98" y="27" width="24" height="8" rx="3" fill="#16d087"/>
                    <rect x="48" y="105" width="18" height="56" rx="9" fill="#fff" stroke="#1e4f40" stroke-width="3" transform="rotate(18 48 105)"/>
                    <circle cx="54" cy="156" r="10" fill="#DDB081"/>
                    <rect x="154" y="101" width="18" height="56" rx="9" fill="#fff" stroke="#1e4f40" stroke-width="3" transform="rotate(-18 154 101)"/>
                    <circle cx="166" cy="150" r="10" fill="#DDB081"/>
                    <path d="M160 122 C186 108, 198 118, 204 142" stroke="#8B5A2B" stroke-width="14" fill="none" stroke-linecap="round"/>
                </svg>
            </div>

            <div class="floor"></div>

            <div class="subtitle">
                <strong>Episódio 404 — página desaparecida</strong>
                <span>O gato correu com a conexão, o cachorro foi atrás e a rota acabou sumindo do sistema.</span>
            </div>
        </section>
    </div>
</div>
</body>
</html>