<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#061811">
    <title>404 | Integrar ReSaúde</title>

    <style>
        :root {
            --bg-1: #04110c;
            --bg-2: #082118;
            --panel: rgba(8, 27, 20, 0.78);
            --panel-2: rgba(255, 255, 255, 0.05);
            --line: rgba(173, 255, 220, 0.14);
            --text: #f2fff8;
            --muted: #9dc8b8;
            --green: #2fd393;
            --green-2: #8ef3c5;
            --green-3: #0e7d57;
            --shadow: 0 30px 100px rgba(0, 0, 0, 0.38);
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            min-height: 100%;
            overflow-x: hidden;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 12% 12%, rgba(47, 211, 147, 0.16), transparent 24%),
                radial-gradient(circle at 86% 18%, rgba(142, 243, 197, 0.10), transparent 22%),
                radial-gradient(circle at 50% 100%, rgba(47, 211, 147, 0.10), transparent 34%),
                linear-gradient(140deg, var(--bg-1) 0%, #071812 45%, var(--bg-2) 100%);
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            opacity: .22;
            background-image:
                linear-gradient(rgba(255,255,255,.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.06) 1px, transparent 1px);
            background-size: 36px 36px;
            mask-image: radial-gradient(circle at center, black 28%, transparent 85%);
        }

        .page-404 {
            min-height: 100vh;
            width: 100%;
            display: grid;
            grid-template-columns: minmax(360px, 510px) minmax(420px, 1fr);
            gap: 18px;
            padding: 18px;
        }

        .panel {
            position: relative;
            border: 1px solid var(--line);
            border-radius: 34px;
            background:
                linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.02)),
                var(--panel);
            box-shadow: var(--shadow);
            backdrop-filter: blur(18px);
            overflow: hidden;
        }

        .panel::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(circle at top left, rgba(142, 243, 197, 0.12), transparent 28%),
                linear-gradient(180deg, rgba(255,255,255,.06), transparent 18%);
        }

        .left-content {
            position: relative;
            z-index: 2;
            min-height: calc(100vh - 36px);
            padding: clamp(28px, 3vw, 52px);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            width: max-content;
            max-width: 100%;
            padding: 10px 16px;
            border-radius: 999px;
            border: 1px solid rgba(142, 243, 197, 0.2);
            background: rgba(142, 243, 197, 0.08);
            text-transform: uppercase;
            letter-spacing: .08em;
            font-size: 12px;
            font-weight: 900;
        }

        .eyebrow span {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--green);
            box-shadow: 0 0 18px rgba(47, 211, 147, .75);
            animation: pulse 1.8s ease-in-out infinite;
        }

        @keyframes pulse {
            0%,100% { transform: scale(.85); opacity: .75; }
            50% { transform: scale(1.18); opacity: 1; }
        }

        .mini-label {
            margin-top: 34px;
            color: #8fd9b8;
            letter-spacing: .38em;
            text-transform: uppercase;
            font-size: 12px;
            font-weight: 950;
        }

        .error-code {
            margin-top: 14px;
            font-size: clamp(110px, 15vw, 200px);
            line-height: .82;
            letter-spacing: -.08em;
            font-weight: 1000;
            color: #f6fff9;
            text-shadow: 0 14px 45px rgba(0,0,0,.24), 0 0 34px rgba(47,211,147,.12);
        }

        .title {
            margin: 10px 0 0;
            font-size: clamp(30px, 3.5vw, 54px);
            line-height: 1.02;
            letter-spacing: -.05em;
            font-weight: 1000;
        }

        .title em {
            font-style: normal;
            color: var(--green-2);
        }

        .description {
            margin: 20px 0 0;
            color: var(--muted);
            line-height: 1.8;
            font-size: 16px;
            max-width: 650px;
        }

        .info-box {
            margin-top: 28px;
            padding: 18px;
            border-radius: 24px;
            border: 1px solid rgba(142,243,197,.12);
            background: rgba(255,255,255,.04);
        }

        .info-title {
            font-size: 16px;
            font-weight: 950;
            color: #f4fff8;
        }

        .info-text {
            margin-top: 8px;
            font-size: 14px;
            line-height: 1.7;
            color: var(--muted);
        }

        .grid-info {
            margin-top: 16px;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .grid-item {
            padding: 15px;
            border-radius: 20px;
            border: 1px solid rgba(142,243,197,.08);
            background: rgba(255,255,255,.035);
        }

        .grid-item small {
            display: block;
            color: #89cdb1;
            letter-spacing: .16em;
            text-transform: uppercase;
            font-size: 10px;
            font-weight: 900;
        }

        .grid-item strong {
            display: block;
            margin-top: 8px;
            font-size: 15px;
            line-height: 1.4;
            color: #f5fff8;
        }

        .actions {
            margin-top: 28px;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .btn-404 {
            border: 0;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 52px;
            padding: 14px 22px;
            border-radius: 18px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 900;
            transition: transform .2s ease, box-shadow .2s ease, opacity .2s ease;
        }

        .btn-primary {
            color: #052217;
            background: linear-gradient(180deg, #92f5c8, #2fd393 78%);
            box-shadow: 0 16px 34px rgba(47,211,147,.25);
        }

        .btn-secondary {
            color: #effef7;
            background: rgba(255,255,255,.055);
            border: 1px solid rgba(255,255,255,.1);
        }

        .btn-404:hover {
            transform: translateY(-2px);
        }

        .footer-note {
            margin-top: 22px;
            font-size: 12px;
            color: #7eb9a1;
            line-height: 1.7;
        }

        .viewer-panel {
            position: relative;
            min-height: calc(100vh - 36px);
        }

        #scene-wrap {
            position: absolute;
            inset: 0;
        }

        #webgl-container {
            position: absolute;
            inset: 0;
        }

        .viewer-ui {
            position: absolute;
            inset: 0;
            z-index: 3;
            pointer-events: none;
        }

        .top-ui {
            position: absolute;
            top: 18px;
            left: 18px;
            right: 18px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            flex-wrap: wrap;
        }

        .chip,
        .viewer-card,
        .control-bar {
            pointer-events: auto;
        }

        .chip {
            padding: 10px 14px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .08em;
            border: 1px solid rgba(255,255,255,.1);
            background: rgba(6, 22, 16, 0.72);
            backdrop-filter: blur(12px);
            color: #f3fff8;
        }

        .viewer-card {
            position: absolute;
            max-width: 240px;
            padding: 16px;
            border-radius: 22px;
            border: 1px solid rgba(142,243,197,.14);
            background: rgba(6, 22, 16, 0.72);
            backdrop-filter: blur(14px);
            box-shadow: 0 20px 55px rgba(0,0,0,.25);
        }

        .viewer-card small {
            display: block;
            color: #8fd6b8;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .15em;
        }

        .viewer-card strong {
            display: block;
            margin-top: 8px;
            font-size: 15px;
            line-height: 1.35;
            color: #f4fff9;
        }

        .viewer-card p {
            margin: 8px 0 0;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.55;
        }

        .card-a { top: 110px; left: 24px; }
        .card-b { top: 140px; right: 24px; }
        .card-c { bottom: 120px; left: 42px; }

        .control-bar {
            position: absolute;
            left: 18px;
            right: 18px;
            bottom: 18px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 14px 16px;
            border-radius: 24px;
            border: 1px solid rgba(142,243,197,.16);
            background: rgba(6, 22, 16, 0.76);
            backdrop-filter: blur(16px);
            box-shadow: 0 18px 40px rgba(0,0,0,.22);
        }

        .control-text strong {
            display: block;
            font-size: 14px;
            font-weight: 900;
            color: #f6fff9;
        }

        .control-text span {
            display: block;
            margin-top: 4px;
            font-size: 12px;
            line-height: 1.5;
            color: var(--muted);
        }

        .control-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .control-btn {
            border: 1px solid rgba(255,255,255,.1);
            background: rgba(255,255,255,.06);
            color: #effef7;
            cursor: pointer;
            padding: 11px 14px;
            border-radius: 14px;
            font-size: 12px;
            font-weight: 900;
            transition: transform .2s ease, background .2s ease, border-color .2s ease;
        }

        .control-btn:hover {
            transform: translateY(-1px);
            background: rgba(142,243,197,.12);
            border-color: rgba(142,243,197,.2);
        }

        .loading-cover {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 12px;
            background: linear-gradient(180deg, rgba(4, 16, 12, .32), rgba(4, 16, 12, .64));
            z-index: 5;
            transition: opacity .45s ease, visibility .45s ease;
        }

        .loading-cover.hidden {
            opacity: 0;
            visibility: hidden;
        }

        .loading-ring {
            width: 62px;
            height: 62px;
            border-radius: 50%;
            border: 3px solid rgba(255,255,255,.08);
            border-top-color: var(--green);
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .loading-cover strong {
            font-size: 15px;
            font-weight: 900;
            color: #f4fff8;
        }

        .loading-cover span {
            font-size: 13px;
            color: var(--muted);
        }

        @media (max-width: 1180px) {
            .page-404 {
                grid-template-columns: 1fr;
            }

            .left-content,
            .viewer-panel {
                min-height: auto;
            }

            .viewer-panel {
                min-height: 760px;
            }
        }

        @media (max-width: 760px) {
            .page-404 {
                padding: 12px;
            }

            .panel {
                border-radius: 26px;
            }

            .left-content {
                min-height: auto;
                padding: 26px;
            }

            .eyebrow {
                width: 100%;
                justify-content: center;
                text-align: center;
                font-size: 10px;
            }

            .description {
                font-size: 14px;
            }

            .grid-info {
                grid-template-columns: 1fr;
            }

            .actions {
                flex-direction: column;
            }

            .btn-404 {
                width: 100%;
            }

            .viewer-panel {
                min-height: 660px;
            }

            .top-ui {
                top: 12px;
                left: 12px;
                right: 12px;
            }

            .chip {
                font-size: 9px;
                padding: 8px 11px;
            }

            .viewer-card {
                max-width: 170px;
                padding: 12px;
            }

            .viewer-card strong {
                font-size: 12px;
            }

            .viewer-card p {
                font-size: 10px;
            }

            .card-a { top: 78px; left: 10px; }
            .card-b { top: 110px; right: 10px; }
            .card-c { display: none; }

            .control-bar {
                left: 12px;
                right: 12px;
                bottom: 12px;
                flex-direction: column;
                align-items: stretch;
            }

            .control-buttons {
                width: 100%;
            }

            .control-btn {
                flex: 1 1 auto;
            }
        }
    </style>
</head>
<body>
    <main class="page-404">
        <section class="panel">
            <div class="left-content">
                <div class="eyebrow">
                    <span></span>
                    Integrar ReSaúde • Página 404 interativa
                </div>

                <div class="mini-label">erro de navegação</div>
                <div class="error-code">404</div>

                <h1 class="title">
                    A página não foi encontrada, mas o <em>sistema continua ativo.</em>
                </h1>

                <p class="description">
                    A rota que você tentou acessar não existe ou foi movida. Para deixar a experiência mais profissional,
                    esta página ganhou um visual 3D com uma personagem de enfermagem em estilo bloco, que pode ser girada
                    em todos os ângulos com o mouse, como uma vitrine interativa.
                </p>

                <div class="info-box">
                    <div class="info-title">O que esta versão faz</div>
                    <div class="info-text">
                        Você pode arrastar para girar a enfermeira 3D, usar o scroll para aproximar e afastar,
                        ver de frente, de lado, por cima e por baixo, além de usar botões rápidos para mudar a vista.
                    </div>

                    <div class="grid-info">
                        <div class="grid-item">
                            <small>modelo</small>
                            <strong>Enfermeira 3D em estilo bloco</strong>
                        </div>
                        <div class="grid-item">
                            <small>interação</small>
                            <strong>Rotação livre com mouse</strong>
                        </div>
                    </div>
                </div>

                <div class="actions">
                    <a href="{{ url('/') }}" class="btn-404 btn-primary">Voltar ao início</a>
                    <button type="button" class="btn-404 btn-secondary" onclick="history.back()">Voltar para a tela anterior</button>
                </div>

                <div class="footer-note">
                    Integrar ReSaúde • página 404 redesenhada com visual mais profissional e experiência 3D interativa.
                </div>
            </div>
        </section>

        <section class="panel viewer-panel">
            <div id="scene-wrap">
                <div id="webgl-container"></div>

                <div class="viewer-ui">
                    <div class="top-ui">
                        <div class="chip">arraste para girar</div>
                        <div class="chip">scroll para zoom</div>
                    </div>

                    <div class="viewer-card card-a">
                        <small>visual 3d</small>
                        <strong>Veja de todos os lados</strong>
                        <p>Rotacione livremente a personagem para observar frente, costas, laterais e ângulos baixos.</p>
                    </div>

                    <div class="viewer-card card-b">
                        <small>identidade</small>
                        <strong>Tema da saúde</strong>
                        <p>Visual profissional com uniforme, crachá, touca e base de apresentação no padrão do sistema.</p>
                    </div>

                    <div class="viewer-card card-c">
                        <small>experiência</small>
                        <strong>Mais moderna</strong>
                        <p>Substitui a tela simples por uma página 404 mais impactante e elegante.</p>
                    </div>

                    <div class="control-bar">
                        <div class="control-text">
                            <strong>Enfermeira 3D interativa</strong>
                            <span>Use os botões para reposicionar a câmera ou deixe a rotação automática ligada.</span>
                        </div>

                        <div class="control-buttons">
                            <button type="button" class="control-btn" id="btnFront">Frente</button>
                            <button type="button" class="control-btn" id="btnSide">Lado</button>
                            <button type="button" class="control-btn" id="btnTop">Cima</button>
                            <button type="button" class="control-btn" id="btnBottom">Baixo</button>
                            <button type="button" class="control-btn" id="btnAuto">Auto rotação</button>
                            <button type="button" class="control-btn" id="btnReset">Resetar</button>
                        </div>
                    </div>
                </div>

                <div class="loading-cover" id="loadingCover">
                    <div class="loading-ring"></div>
                    <strong>Carregando experiência 3D</strong>
                    <span>Preparando a personagem de enfermagem...</span>
                </div>
            </div>
        </section>
    </main>

    <script type="module">
        import * as THREE from 'https://cdn.jsdelivr.net/npm/three@0.164.1/build/three.module.js';
        import { OrbitControls } from 'https://cdn.jsdelivr.net/npm/three@0.164.1/examples/jsm/controls/OrbitControls.js';

        const container = document.getElementById('webgl-container');
        const loadingCover = document.getElementById('loadingCover');

        let scene, camera, renderer, controls, nurseGroup, mixerClock;
        let autoRotateEnabled = true;
        let floatTick = 0;

        init();
        animate();

        function init() {
            scene = new THREE.Scene();
            scene.background = new THREE.Color(0x081913);
            scene.fog = new THREE.Fog(0x081913, 8, 16);

            camera = new THREE.PerspectiveCamera(40, container.clientWidth / container.clientHeight, 0.1, 100);
            camera.position.set(3.8, 2.6, 5.8);

            renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
            renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
            renderer.setSize(container.clientWidth, container.clientHeight);
            renderer.shadowMap.enabled = true;
            renderer.shadowMap.type = THREE.PCFSoftShadowMap;
            container.appendChild(renderer.domElement);

            controls = new OrbitControls(camera, renderer.domElement);
            controls.enableDamping = true;
            controls.dampingFactor = 0.06;
            controls.target.set(0, 1.35, 0);
            controls.minDistance = 3;
            controls.maxDistance = 9;
            controls.minPolarAngle = 0.10;
            controls.maxPolarAngle = Math.PI - 0.10;
            controls.autoRotate = autoRotateEnabled;
            controls.autoRotateSpeed = 1.3;
            controls.enablePan = false;

            addLights();
            addEnvironment();
            nurseGroup = createNurse();
            scene.add(nurseGroup);

            wireButtons();
            window.addEventListener('resize', onResize);

            setTimeout(() => {
                loadingCover?.classList.add('hidden');
            }, 650);
        }

        function addLights() {
            const ambient = new THREE.AmbientLight(0xffffff, 1.7);
            scene.add(ambient);

            const hemi = new THREE.HemisphereLight(0xc9fff0, 0x16352b, 1.35);
            hemi.position.set(0, 6, 0);
            scene.add(hemi);

            const key = new THREE.DirectionalLight(0xffffff, 2.2);
            key.position.set(4, 7, 5);
            key.castShadow = true;
            key.shadow.mapSize.set(2048, 2048);
            key.shadow.camera.near = 0.5;
            key.shadow.camera.far = 30;
            key.shadow.camera.left = -8;
            key.shadow.camera.right = 8;
            key.shadow.camera.top = 8;
            key.shadow.camera.bottom = -8;
            scene.add(key);

            const rim = new THREE.DirectionalLight(0x8ef3c5, 1.4);
            rim.position.set(-4, 3, -5);
            scene.add(rim);

            const spot = new THREE.SpotLight(0x8ef3c5, 2.1, 20, Math.PI / 5, 0.35, 1.5);
            spot.position.set(0, 7, 2);
            spot.target.position.set(0, 1.2, 0);
            scene.add(spot);
            scene.add(spot.target);
        }

        function addEnvironment() {
            const floorGeo = new THREE.CylinderGeometry(2.25, 2.45, 0.48, 48);
            const floorMat = new THREE.MeshStandardMaterial({
                color: 0x123327,
                roughness: 0.65,
                metalness: 0.12,
                emissive: 0x0a1f18,
                emissiveIntensity: 0.7
            });
            const floor = new THREE.Mesh(floorGeo, floorMat);
            floor.position.y = -0.25;
            floor.receiveShadow = true;
            floor.castShadow = true;
            scene.add(floor);

            const floorTopGeo = new THREE.CylinderGeometry(1.85, 2.05, 0.08, 48);
            const floorTopMat = new THREE.MeshStandardMaterial({
                color: 0x2fd393,
                roughness: 0.35,
                metalness: 0.05,
                emissive: 0x184733,
                emissiveIntensity: 0.5
            });
            const floorTop = new THREE.Mesh(floorTopGeo, floorTopMat);
            floorTop.position.y = 0.03;
            floorTop.receiveShadow = true;
            scene.add(floorTop);

            const ringGeo = new THREE.TorusGeometry(2.15, 0.03, 24, 100);
            const ringMat = new THREE.MeshStandardMaterial({ color: 0x8ef3c5, emissive: 0x8ef3c5, emissiveIntensity: 0.55 });
            const ring = new THREE.Mesh(ringGeo, ringMat);
            ring.rotation.x = Math.PI / 2;
            ring.position.y = 0.16;
            scene.add(ring);

            const glowGeo = new THREE.CircleGeometry(2.35, 48);
            const glowMat = new THREE.MeshBasicMaterial({ color: 0x2fd393, transparent: true, opacity: 0.08 });
            const glow = new THREE.Mesh(glowGeo, glowMat);
            glow.rotation.x = -Math.PI / 2;
            glow.position.y = 0.12;
            scene.add(glow);

            const particlesGeo = new THREE.BufferGeometry();
            const particleCount = 120;
            const positions = [];
            for (let i = 0; i < particleCount; i++) {
                const radius = 3 + Math.random() * 3.5;
                const angle = Math.random() * Math.PI * 2;
                const height = Math.random() * 5.5;
                positions.push(Math.cos(angle) * radius, height, Math.sin(angle) * radius);
            }
            particlesGeo.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));
            const particlesMat = new THREE.PointsMaterial({
                color: 0x8ef3c5,
                size: 0.05,
                transparent: true,
                opacity: 0.65,
                sizeAttenuation: true
            });
            const particles = new THREE.Points(particlesGeo, particlesMat);
            scene.add(particles);
        }

        function createNurse() {
            const group = new THREE.Group();

            const skin = new THREE.MeshStandardMaterial({ color: 0xe8b795, roughness: 0.9 });
            const white = new THREE.MeshStandardMaterial({ color: 0xf6fbff, roughness: 0.8 });
            const green = new THREE.MeshStandardMaterial({ color: 0x42c692, roughness: 0.72 });
            const darkGreen = new THREE.MeshStandardMaterial({ color: 0x0f6d50, roughness: 0.72 });
            const gray = new THREE.MeshStandardMaterial({ color: 0x47605b, roughness: 0.85 });
            const hair = new THREE.MeshStandardMaterial({ color: 0x423126, roughness: 0.95 });
            const shoes = new THREE.MeshStandardMaterial({ color: 0xffffff, roughness: 0.7 });
            const badge = new THREE.MeshStandardMaterial({ color: 0x86d5f8, roughness: 0.65 });
            const black = new THREE.MeshStandardMaterial({ color: 0x161a1a, roughness: 0.95 });

            const castShadowTo = (mesh) => {
                mesh.castShadow = true;
                mesh.receiveShadow = true;
                return mesh;
            };

            // pedestal mini base
            const stand = castShadowTo(new THREE.Mesh(
                new THREE.BoxGeometry(1.45, 0.22, 1.45),
                new THREE.MeshStandardMaterial({ color: 0xf0fff9, roughness: 0.55, metalness: 0.04 })
            ));
            stand.position.y = 0.18;
            group.add(stand);

            // shoes
            const shoeL = castShadowTo(new THREE.Mesh(new THREE.BoxGeometry(0.36, 0.18, 0.6), shoes));
            shoeL.position.set(-0.22, 0.40, 0.02);
            group.add(shoeL);

            const shoeR = castShadowTo(new THREE.Mesh(new THREE.BoxGeometry(0.36, 0.18, 0.6), shoes));
            shoeR.position.set(0.22, 0.40, 0.02);
            group.add(shoeR);

            // legs
            const legL = castShadowTo(new THREE.Mesh(new THREE.BoxGeometry(0.26, 0.82, 0.26), white));
            legL.position.set(-0.22, 0.90, 0);
            group.add(legL);

            const legR = castShadowTo(new THREE.Mesh(new THREE.BoxGeometry(0.26, 0.82, 0.26), white));
            legR.position.set(0.22, 0.90, 0);
            group.add(legR);

            // torso / coat
            const torso = castShadowTo(new THREE.Mesh(new THREE.BoxGeometry(1.0, 1.26, 0.58), white));
            torso.position.set(0, 1.95, 0);
            group.add(torso);

            const chestPanel = castShadowTo(new THREE.Mesh(new THREE.BoxGeometry(0.86, 0.98, 0.61), green));
            chestPanel.position.set(0, 1.96, 0.015);
            group.add(chestPanel);

            const pocketL = castShadowTo(new THREE.Mesh(new THREE.BoxGeometry(0.18, 0.18, 0.03), white));
            pocketL.position.set(-0.22, 1.7, 0.31);
            group.add(pocketL);

            const pocketR = castShadowTo(new THREE.Mesh(new THREE.BoxGeometry(0.18, 0.18, 0.03), white));
            pocketR.position.set(0.22, 1.7, 0.31);
            group.add(pocketR);

            const badgeMesh = castShadowTo(new THREE.Mesh(new THREE.BoxGeometry(0.18, 0.26, 0.02), badge));
            badgeMesh.position.set(0.27, 2.17, 0.31);
            group.add(badgeMesh);

            // center health symbol on chest
            const chestCrossVertical = castShadowTo(new THREE.Mesh(new THREE.BoxGeometry(0.08, 0.28, 0.03), white));
            chestCrossVertical.position.set(0, 2.08, 0.325);
            group.add(chestCrossVertical);

            const chestCrossHorizontal = castShadowTo(new THREE.Mesh(new THREE.BoxGeometry(0.28, 0.08, 0.03), white));
            chestCrossHorizontal.position.set(0, 2.08, 0.325);
            group.add(chestCrossHorizontal);

            // arms
            const armL = castShadowTo(new THREE.Mesh(new THREE.BoxGeometry(0.24, 1.0, 0.24), white));
            armL.position.set(-0.68, 1.94, 0);
            armL.rotation.z = THREE.MathUtils.degToRad(7);
            group.add(armL);

            const armR = castShadowTo(new THREE.Mesh(new THREE.BoxGeometry(0.24, 1.0, 0.24), white));
            armR.position.set(0.68, 1.94, 0);
            armR.rotation.z = THREE.MathUtils.degToRad(-7);
            group.add(armR);

            const handL = castShadowTo(new THREE.Mesh(new THREE.BoxGeometry(0.20, 0.20, 0.20), skin));
            handL.position.set(-0.72, 1.38, 0);
            group.add(handL);

            const handR = castShadowTo(new THREE.Mesh(new THREE.BoxGeometry(0.20, 0.20, 0.20), skin));
            handR.position.set(0.72, 1.38, 0);
            group.add(handR);

            // neck
            const neck = castShadowTo(new THREE.Mesh(new THREE.BoxGeometry(0.22, 0.16, 0.22), skin));
            neck.position.set(0, 2.72, 0);
            group.add(neck);

            // head
            const head = castShadowTo(new THREE.Mesh(new THREE.BoxGeometry(0.82, 0.82, 0.82), skin));
            head.position.set(0, 3.16, 0);
            group.add(head);

            // hair back
            const hairBack = castShadowTo(new THREE.Mesh(new THREE.BoxGeometry(0.86, 0.48, 0.84), hair));
            hairBack.position.set(0, 3.2, -0.05);
            group.add(hairBack);

            const fringe = castShadowTo(new THREE.Mesh(new THREE.BoxGeometry(0.84, 0.18, 0.30), hair));
            fringe.position.set(0, 3.48, 0.15);
            group.add(fringe);

            // face details
            const eyeL = castShadowTo(new THREE.Mesh(new THREE.BoxGeometry(0.08, 0.08, 0.02), black));
            eyeL.position.set(-0.16, 3.18, 0.42);
            group.add(eyeL);

            const eyeR = castShadowTo(new THREE.Mesh(new THREE.BoxGeometry(0.08, 0.08, 0.02), black));
            eyeR.position.set(0.16, 3.18, 0.42);
            group.add(eyeR);

            const mouth = castShadowTo(new THREE.Mesh(new THREE.BoxGeometry(0.18, 0.04, 0.02), darkGreen));
            mouth.position.set(0, 2.95, 0.42);
            group.add(mouth);

            const blushL = castShadowTo(new THREE.Mesh(new THREE.BoxGeometry(0.10, 0.06, 0.02), new THREE.MeshStandardMaterial({ color: 0xed9f9f, roughness: 0.85 })));
            blushL.position.set(-0.24, 3.00, 0.42);
            group.add(blushL);

            const blushR = castShadowTo(new THREE.Mesh(new THREE.BoxGeometry(0.10, 0.06, 0.02), new THREE.MeshStandardMaterial({ color: 0xed9f9f, roughness: 0.85 })));
            blushR.position.set(0.24, 3.00, 0.42);
            group.add(blushR);

            // nurse cap
            const capBase = castShadowTo(new THREE.Mesh(new THREE.BoxGeometry(0.68, 0.16, 0.62), white));
            capBase.position.set(0, 3.64, -0.02);
            group.add(capBase);

            const capTop = castShadowTo(new THREE.Mesh(new THREE.BoxGeometry(0.38, 0.10, 0.50), white));
            capTop.position.set(0, 3.76, -0.04);
            group.add(capTop);

            const capCrossV = castShadowTo(new THREE.Mesh(new THREE.BoxGeometry(0.06, 0.18, 0.02), green));
            capCrossV.position.set(0, 3.63, 0.29);
            group.add(capCrossV);

            const capCrossH = castShadowTo(new THREE.Mesh(new THREE.BoxGeometry(0.18, 0.06, 0.02), green));
            capCrossH.position.set(0, 3.63, 0.29);
            group.add(capCrossH);

            // stethoscope
            const tubeMaterial = new THREE.MeshStandardMaterial({ color: 0x264a41, roughness: 0.72 });
            const metallic = new THREE.MeshStandardMaterial({ color: 0xc9d6d2, roughness: 0.38, metalness: 0.65 });

            const tubeLeft = castShadowTo(new THREE.Mesh(new THREE.CylinderGeometry(0.03, 0.03, 0.8, 18), tubeMaterial));
            tubeLeft.position.set(-0.20, 1.90, 0.27);
            tubeLeft.rotation.z = THREE.MathUtils.degToRad(20);
            group.add(tubeLeft);

            const tubeRight = castShadowTo(new THREE.Mesh(new THREE.CylinderGeometry(0.03, 0.03, 0.8, 18), tubeMaterial));
            tubeRight.position.set(0.20, 1.90, 0.27);
            tubeRight.rotation.z = THREE.MathUtils.degToRad(-20);
            group.add(tubeRight);

            const chestDisc = castShadowTo(new THREE.Mesh(new THREE.CylinderGeometry(0.09, 0.09, 0.04, 24), metallic));
            chestDisc.position.set(0, 1.55, 0.31);
            chestDisc.rotation.x = Math.PI / 2;
            group.add(chestDisc);

            const earpieceL = castShadowTo(new THREE.Mesh(new THREE.CylinderGeometry(0.03, 0.03, 0.22, 16), metallic));
            earpieceL.position.set(-0.26, 2.44, 0.22);
            earpieceL.rotation.z = THREE.MathUtils.degToRad(15);
            group.add(earpieceL);

            const earpieceR = castShadowTo(new THREE.Mesh(new THREE.CylinderGeometry(0.03, 0.03, 0.22, 16), metallic));
            earpieceR.position.set(0.26, 2.44, 0.22);
            earpieceR.rotation.z = THREE.MathUtils.degToRad(-15);
            group.add(earpieceR);

            group.position.y = 0;
            return group;
        }

        function wireButtons() {
            document.getElementById('btnFront')?.addEventListener('click', () => moveCameraTo(0, 1.9, 5.8));
            document.getElementById('btnSide')?.addEventListener('click', () => moveCameraTo(5.8, 1.9, 0));
            document.getElementById('btnTop')?.addEventListener('click', () => moveCameraTo(0.01, 6.8, 0.01));
            document.getElementById('btnBottom')?.addEventListener('click', () => moveCameraTo(0.01, -2.2, 4.4));
            document.getElementById('btnReset')?.addEventListener('click', () => moveCameraTo(3.8, 2.6, 5.8));
            document.getElementById('btnAuto')?.addEventListener('click', () => {
                autoRotateEnabled = !autoRotateEnabled;
                controls.autoRotate = autoRotateEnabled;
                const btn = document.getElementById('btnAuto');
                if (btn) btn.textContent = autoRotateEnabled ? 'Pausar rotação' : 'Auto rotação';
            });
        }

        function moveCameraTo(x, y, z) {
            controls.autoRotate = false;
            autoRotateEnabled = false;
            const btn = document.getElementById('btnAuto');
            if (btn) btn.textContent = 'Auto rotação';

            const start = camera.position.clone();
            const end = new THREE.Vector3(x, y, z);
            const duration = 700;
            const startTime = performance.now();

            function step(now) {
                const progress = Math.min((now - startTime) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                camera.position.lerpVectors(start, end, eased);
                controls.target.set(0, 1.55, 0);
                controls.update();
                if (progress < 1) requestAnimationFrame(step);
            }

            requestAnimationFrame(step);
        }

        function onResize() {
            if (!renderer || !camera) return;
            camera.aspect = container.clientWidth / container.clientHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(container.clientWidth, container.clientHeight);
        }

        function animate() {
            requestAnimationFrame(animate);
            floatTick += 0.018;

            if (nurseGroup) {
                nurseGroup.position.y = Math.sin(floatTick) * 0.06;
                nurseGroup.rotation.y += 0.0012;
            }

            controls?.update();
            renderer?.render(scene, camera);
        }
    </script>
</body>
</html>
