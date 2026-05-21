<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 | Integrar ReSaúde</title>

    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            min-height: 100%;
            overflow: hidden;
            font-family: Inter, Arial, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(16, 185, 129, 0.22), transparent 28%),
                radial-gradient(circle at top right, rgba(59, 130, 246, 0.16), transparent 24%),
                linear-gradient(135deg, #07120f 0%, #0c1d18 35%, #132d24 100%);
            color: #fff;
        }

        .page-404 {
            position: relative;
            width: 100%;
            min-height: 100vh;
            overflow: hidden;
        }

        .bg-grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.035) 1px, transparent 1px);
            background-size: 40px 40px;
            mask-image: radial-gradient(circle at center, black 35%, transparent 95%);
            pointer-events: none;
        }

        .glow-1,
        .glow-2,
        .glow-3 {
            position: absolute;
            border-radius: 999px;
            filter: blur(80px);
            opacity: .45;
            pointer-events: none;
        }

        .glow-1 {
            width: 380px;
            height: 380px;
            background: rgba(16, 185, 129, 0.28);
            top: -60px;
            left: -40px;
        }

        .glow-2 {
            width: 320px;
            height: 320px;
            background: rgba(56, 189, 248, 0.20);
            right: -60px;
            top: 80px;
        }

        .glow-3 {
            width: 280px;
            height: 280px;
            background: rgba(52, 211, 153, 0.18);
            left: 50%;
            bottom: -60px;
            transform: translateX(-50%);
        }

        .layout {
            position: relative;
            z-index: 2;
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1.05fr 1fr;
            gap: 24px;
            padding: 28px;
        }

        .panel-left {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 56px);
        }

        .viewer-card {
            position: relative;
            width: 100%;
            height: 100%;
            min-height: 640px;
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 32px;
            background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.02));
            box-shadow:
                0 30px 80px rgba(0,0,0,.35),
                inset 0 1px 0 rgba(255,255,255,.06);
            overflow: hidden;
            backdrop-filter: blur(12px);
        }

        #webgl-container {
            position: absolute;
            inset: 0;
        }

        .viewer-ui {
            position: absolute;
            left: 22px;
            right: 22px;
            bottom: 18px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: space-between;
            align-items: center;
            z-index: 5;
        }

        .viewer-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(8, 20, 17, .72);
            border: 1px solid rgba(255,255,255,.09);
            color: #d1fae5;
            font-size: 13px;
            font-weight: 700;
            backdrop-filter: blur(10px);
        }

        .dot-live {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #34d399;
            box-shadow: 0 0 14px #34d399;
            animation: pulseLive 1.6s infinite;
        }

        @keyframes pulseLive {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.35); opacity: .7; }
        }

        .viewer-help {
            padding: 10px 14px;
            border-radius: 16px;
            background: rgba(8, 20, 17, .72);
            border: 1px solid rgba(255,255,255,.09);
            color: rgba(255,255,255,.88);
            font-size: 12px;
            backdrop-filter: blur(10px);
        }

        .panel-right {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .content-card {
            width: 100%;
            max-width: 620px;
            padding: 24px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.10);
            color: #d1fae5;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 20px;
            backdrop-filter: blur(10px);
        }

        .brand-icon {
            width: 28px;
            height: 28px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #16a34a, #10b981);
            box-shadow: 0 10px 20px rgba(16, 185, 129, .25);
        }

        .error-code {
            font-size: clamp(76px, 11vw, 160px);
            font-weight: 900;
            line-height: .9;
            letter-spacing: -4px;
            color: #ffffff;
            text-shadow: 0 8px 34px rgba(0,0,0,.35);
        }

        .title {
            margin-top: 14px;
            font-size: clamp(28px, 4vw, 46px);
            font-weight: 900;
            line-height: 1.08;
            letter-spacing: -.02em;
            color: #f8fffc;
        }

        .title span {
            background: linear-gradient(90deg, #6ee7b7, #34d399, #a7f3d0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .description {
            margin-top: 18px;
            max-width: 560px;
            color: rgba(233, 255, 247, 0.84);
            font-size: 16px;
            line-height: 1.7;
        }

        .mini-info {
            margin-top: 24px;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .info-card {
            padding: 16px;
            border-radius: 22px;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.08);
            backdrop-filter: blur(10px);
        }

        .info-card small {
            display: block;
            color: #9fe8cd;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .12em;
            margin-bottom: 8px;
        }

        .info-card strong {
            display: block;
            color: #fff;
            font-size: 15px;
            line-height: 1.4;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 28px;
        }

        .btn {
            appearance: none;
            border: none;
            outline: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 54px;
            padding: 0 22px;
            border-radius: 18px;
            font-weight: 800;
            font-size: 15px;
            transition: .25s ease;
        }

        .btn-primary {
            color: #ffffff;
            background: linear-gradient(135deg, #10b981, #0f9f74);
            box-shadow: 0 16px 34px rgba(16, 185, 129, .28);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 42px rgba(16, 185, 129, .34);
        }

        .btn-secondary {
            color: #eafff6;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.10);
            backdrop-filter: blur(10px);
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            background: rgba(255,255,255,.10);
        }

        .tip-box {
            margin-top: 28px;
            padding: 18px 20px;
            border-radius: 22px;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.08);
            color: rgba(232,255,246,.88);
            font-size: 14px;
            line-height: 1.7;
        }

        .tip-box strong {
            color: #ffffff;
        }

        .loading-cover {
            position: absolute;
            inset: 0;
            z-index: 20;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 16px;
            background:
                radial-gradient(circle at center, rgba(17,24,39,.35), rgba(4,8,14,.92));
            backdrop-filter: blur(8px);
            transition: opacity .35s ease, visibility .35s ease;
        }

        .loading-cover.hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .loading-ring {
            width: 56px;
            height: 56px;
            border-radius: 999px;
            border: 4px solid rgba(255,255,255,.12);
            border-top-color: #34d399;
            animation: spin 1s linear infinite;
        }

        .loading-cover strong {
            font-size: 18px;
            color: #fff;
        }

        .loading-cover span {
            color: rgba(255,255,255,.72);
            font-size: 14px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 1180px) {
            .layout {
                grid-template-columns: 1fr;
                padding: 18px;
            }

            .panel-left {
                min-height: 420px;
            }

            .viewer-card {
                min-height: 420px;
            }

            .content-card {
                max-width: 100%;
                padding: 6px 4px 24px;
            }
        }

        @media (max-width: 640px) {
            html, body {
                overflow-y: auto;
            }

            .page-404 {
                overflow: visible;
            }

            .mini-info {
                grid-template-columns: 1fr;
            }

            .actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }

            .viewer-ui {
                left: 12px;
                right: 12px;
                bottom: 12px;
                gap: 8px;
                flex-direction: column;
                align-items: stretch;
            }

            .viewer-badge,
            .viewer-help {
                justify-content: center;
                text-align: center;
            }
        }
    </style>
</head>
<body>
<div class="page-404">
    <div class="bg-grid"></div>
    <div class="glow-1"></div>
    <div class="glow-2"></div>
    <div class="glow-3"></div>

    <div class="layout">

        <section class="panel-left">
            <div class="viewer-card">
                <div id="webgl-container"></div>

                <div class="loading-cover" id="loadingCover">
                    <div class="loading-ring"></div>
                    <strong>Carregando enfermeiro 3D...</strong>
                    <span>Preparando a experiência interativa</span>
                </div>

                <div class="viewer-ui">
                    <div class="viewer-badge">
                        <span class="dot-live"></span>
                        Visual 3D interativo
                    </div>

                    <div class="viewer-help">
                        Arraste com o mouse • Role para zoom • Gire livremente
                    </div>
                </div>
            </div>
        </section>

        <section class="panel-right">
            <div class="content-card">
                <div class="brand">
                    <span class="brand-icon">+</span>
                    Integrar ReSaúde
                </div>

                <div class="error-code">404</div>

                <h1 class="title">
                    O enfermeiro procurou,
                    <br>
                    mas essa página <span>não foi encontrada</span>.
                </h1>

                <p class="description">
                    A rota acessada não existe ou foi movida dentro da plataforma.
                    Enquanto isso, você pode interagir com o modelo 3D ao lado e voltar
                    para uma área segura do sistema.
                </p>

                <div class="mini-info">
                    <div class="info-card">
                        <small>Status</small>
                        <strong>Conteúdo indisponível no momento</strong>
                    </div>

                    <div class="info-card">
                        <small>Sugestão</small>
                        <strong>Voltar para o início ou retornar à página anterior</strong>
                    </div>
                </div>

                <div class="actions">
                    <a href="{{ url('/') }}" class="btn btn-primary">
                        Ir para o início
                    </a>

                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                        Voltar página anterior
                    </button>
                </div>

                <div class="tip-box">
                    <strong>Dica:</strong> para esse visual ficar realmente profissional,
                    use um arquivo <strong>.glb</strong> de enfermeiro realista com boa malha,
                    textura e rig. A qualidade final depende bastante do modelo 3D usado.
                </div>
            </div>
        </section>

    </div>
</div>

<script type="importmap">
{
    "imports": {
        "three": "https://cdn.jsdelivr.net/npm/three@0.164.1/build/three.module.js",
        "three/addons/": "https://cdn.jsdelivr.net/npm/three@0.164.1/examples/jsm/"
    }
}
</script>

<script type="module">
    import * as THREE from 'three';
    import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
    import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
    import { RGBELoader } from 'three/addons/loaders/RGBELoader.js';

    const container = document.getElementById('webgl-container');
    const loadingCover = document.getElementById('loadingCover');

    const MODEL_URL = '/models/enfermeiro-realista.glb';
    const HDR_URL = '/hdr/studio.hdr';

    let scene, camera, renderer, controls;
    let model = null;
    let clock = new THREE.Clock();

    init();

    async function init() {
        scene = new THREE.Scene();

        camera = new THREE.PerspectiveCamera(
            38,
            container.clientWidth / container.clientHeight,
            0.1,
            100
        );
        camera.position.set(0, 1.45, 4.8);

        renderer = new THREE.WebGLRenderer({
            antialias: true,
            alpha: true,
            powerPreference: 'high-performance'
        });
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        renderer.setSize(container.clientWidth, container.clientHeight);
        renderer.shadowMap.enabled = true;
        renderer.shadowMap.type = THREE.PCFSoftShadowMap;
        renderer.toneMapping = THREE.ACESFilmicToneMapping;
        renderer.toneMappingExposure = 1.08;
        renderer.outputColorSpace = THREE.SRGBColorSpace;
        container.appendChild(renderer.domElement);

        controls = new OrbitControls(camera, renderer.domElement);
        controls.enableDamping = true;
        controls.enablePan = false;
        controls.minDistance = 2.2;
        controls.maxDistance = 8;
        controls.minPolarAngle = Math.PI / 2.8;
        controls.maxPolarAngle = Math.PI - 0.28;
        controls.autoRotate = true;
        controls.autoRotateSpeed = 1.6;
        controls.target.set(0, 1.2, 0);

        const ambient = new THREE.HemisphereLight(0xe8fff7, 0x0b1713, 1.35);
        scene.add(ambient);

        const key = new THREE.DirectionalLight(0xffffff, 2.2);
        key.position.set(4, 7, 6);
        key.castShadow = true;
        key.shadow.mapSize.set(2048, 2048);
        key.shadow.camera.near = 0.5;
        key.shadow.camera.far = 30;
        key.shadow.camera.left = -6;
        key.shadow.camera.right = 6;
        key.shadow.camera.top = 6;
        key.shadow.camera.bottom = -6;
        scene.add(key);

        const rim = new THREE.DirectionalLight(0x7dd3fc, 0.8);
        rim.position.set(-5, 3, -5);
        scene.add(rim);

        const fill = new THREE.PointLight(0x6ee7b7, 0.55, 18);
        fill.position.set(0, 2, 3);
        scene.add(fill);

        const floor = new THREE.Mesh(
            new THREE.CircleGeometry(4.2, 96),
            new THREE.ShadowMaterial({ opacity: 0.22 })
        );
        floor.rotation.x = -Math.PI / 2;
        floor.position.y = -1.02;
        floor.receiveShadow = true;
        scene.add(floor);

        await carregarHDR();
        await carregarModelo();

        loadingCover.classList.add('hidden');

        window.addEventListener('resize', onResize);
        animate();
    }

    async function carregarHDR() {
        try {
            const pmrem = new THREE.PMREMGenerator(renderer);
            pmrem.compileEquirectangularShader();

            const hdri = await new Promise((resolve, reject) => {
                new RGBELoader().load(HDR_URL, resolve, undefined, reject);
            });

            const envMap = pmrem.fromEquirectangular(hdri).texture;
            scene.environment = envMap;

            hdri.dispose();
            pmrem.dispose();
        } catch (e) {
            console.warn('HDR não carregado, seguindo sem HDR.');
        }
    }

    async function carregarModelo() {
        const loader = new GLTFLoader();

        try {
            const gltf = await new Promise((resolve, reject) => {
                loader.load(MODEL_URL, resolve, undefined, reject);
            });

            model = gltf.scene;

            model.traverse((obj) => {
                if (obj.isMesh) {
                    obj.castShadow = true;
                    obj.receiveShadow = true;

                    if (obj.material) {
                        obj.material.envMapIntensity = 1.2;

                        if ('roughness' in obj.material && obj.material.roughness > 0.92) {
                            obj.material.roughness = 0.78;
                        }

                        if ('metalness' in obj.material && obj.material.metalness < 0.02) {
                            obj.material.metalness = 0.04;
                        }

                        obj.material.needsUpdate = true;
                    }
                }
            });

            const box = new THREE.Box3().setFromObject(model);
            const size = new THREE.Vector3();
            const center = new THREE.Vector3();
            box.getSize(size);
            box.getCenter(center);

            model.position.sub(center);

            const maxDim = Math.max(size.x, size.y, size.z);
            const scale = 2.5 / maxDim;
            model.scale.setScalar(scale);

            model.position.y = -1.02 + (size.y * scale) / 2;

            scene.add(model);
        } catch (error) {
            console.error('Erro ao carregar modelo GLB:', error);

            loadingCover.innerHTML = `
                <div class="loading-ring"></div>
                <strong>Não foi possível carregar o modelo 3D</strong>
                <span>Verifique se o arquivo existe em <b>/public/models/enfermeiro-realista.glb</b></span>
            `;
            loadingCover.classList.remove('hidden');
        }
    }

    function animate() {
        requestAnimationFrame(animate);

        const delta = clock.getDelta();

        if (model) {
            model.rotation.y += 0.0012;
        }

        controls.update();
        renderer.render(scene, camera);
    }

    function onResize() {
        if (!container || !renderer || !camera) return;

        camera.aspect = container.clientWidth / container.clientHeight;
        camera.updateProjectionMatrix();

        renderer.setSize(container.clientWidth, container.clientHeight);
    }
</script>
</body>
</html>