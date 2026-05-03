<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Erro') - Integrar ReSaúde</title>

    @vite('resources/css/app.css')

    <style>
        @keyframes floatDoctor {
            0%, 100% {
                transform: translateY(0) rotate(-1deg);
            }
            50% {
                transform: translateY(-10px) rotate(1deg);
            }
        }

        @keyframes cableShake {
            0%, 100% {
                transform: rotate(0deg);
            }
            25% {
                transform: rotate(2deg);
            }
            75% {
                transform: rotate(-2deg);
            }
        }

        @keyframes monitorBlink {
            0%, 100% {
                opacity: 1;
                box-shadow: 0 0 0 rgba(0, 77, 58, 0);
            }
            50% {
                opacity: .75;
                box-shadow: 0 0 28px rgba(0, 77, 58, .25);
            }
        }

        @keyframes heartbeat {
            0%, 100% {
                transform: scale(1);
            }
            15% {
                transform: scale(1.05);
            }
            30% {
                transform: scale(1);
            }
            45% {
                transform: scale(1.08);
            }
            60% {
                transform: scale(1);
            }
        }

        @keyframes plugOut {
            0%, 100% {
                transform: translateX(0);
            }
            50% {
                transform: translateX(-16px);
            }
        }

        @keyframes spark {
            0%, 100% {
                opacity: 0;
                transform: scale(.5) rotate(0deg);
            }
            45% {
                opacity: 1;
                transform: scale(1.15) rotate(12deg);
            }
            70% {
                opacity: .4;
                transform: scale(.85) rotate(-8deg);
            }
        }

        @keyframes bubbleFloat {
            0%, 100% {
                transform: translateY(0);
                opacity: .85;
            }
            50% {
                transform: translateY(-12px);
                opacity: 1;
            }
        }

        @keyframes lineMove {
            0% {
                stroke-dashoffset: 120;
            }
            100% {
                stroke-dashoffset: 0;
            }
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(18px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .doctor-float {
            animation: floatDoctor 4s ease-in-out infinite;
            transform-origin: center;
        }

        .cable-shake {
            animation: cableShake 1.8s ease-in-out infinite;
            transform-origin: center;
        }

        .monitor-blink {
            animation: monitorBlink 2.4s ease-in-out infinite;
        }

        .heart-beat {
            animation: heartbeat 1.6s ease-in-out infinite;
            transform-origin: center;
        }

        .plug-out {
            animation: plugOut 2.8s ease-in-out infinite;
        }

        .spark {
            animation: spark 1.2s ease-in-out infinite;
            transform-origin: center;
        }

        .bubble-float {
            animation: bubbleFloat 3.5s ease-in-out infinite;
        }

        .line-move {
            stroke-dasharray: 120;
            animation: lineMove 2.2s linear infinite;
        }

        .fade-up {
            animation: fadeUp .8s ease-out both;
        }

        .glass-card {
            background: rgba(255, 255, 255, .82);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .grid-bg {
            background-image:
                linear-gradient(rgba(0, 77, 58, .06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 77, 58, .06) 1px, transparent 1px);
            background-size: 34px 34px;
        }
    </style>
</head>

<body class="min-h-screen bg-[#F3F7F3] text-[#003C2F] overflow-x-hidden">

    <div class="fixed inset-0 grid-bg"></div>

    <div class="fixed -top-32 -left-24 w-96 h-96 bg-green-200/40 rounded-full blur-3xl"></div>
    <div class="fixed -bottom-32 -right-24 w-96 h-96 bg-emerald-300/30 rounded-full blur-3xl"></div>

    <main class="relative min-h-screen flex items-center justify-center px-4 py-8">

        <section class="w-full max-w-6xl grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">

            <!-- TEXTO -->
            <div class="order-2 lg:order-1 fade-up">

                <div class="glass-card border border-white/80 rounded-[2rem] shadow-2xl p-7 sm:p-10">

                    <div class="inline-flex items-center gap-2 bg-[#EAF5EF] text-[#004D3A] px-4 py-2 rounded-full text-sm font-extrabold mb-6">
                        <span class="w-2.5 h-2.5 rounded-full @yield('dot_color', 'bg-green-600') animate-pulse"></span>
                        Integrar ReSaúde
                    </div>

                    <p class="text-[11px] uppercase tracking-[0.35em] text-[#60756B] font-extrabold mb-3">
                        Código do erro
                    </p>

                    <h1 class="text-7xl sm:text-8xl font-black tracking-tight @yield('code_color', 'text-[#004D3A]') leading-none">
                        @yield('code')
                    </h1>

                    <h2 class="text-2xl sm:text-3xl font-black text-[#003C2F] mt-5">
                        @yield('headline')
                    </h2>

                    <p class="text-[#60756B] text-base sm:text-lg leading-relaxed mt-4">
                        @yield('message')
                    </p>

                    <div class="mt-8 bg-white/80 border border-[#DCE7DE] rounded-3xl p-5">
                        <div class="flex items-start gap-3">
                            <div class="w-11 h-11 rounded-2xl bg-[#EAF5EF] text-[#004D3A] flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-6 h-6"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="1.8"
                                          d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.178-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M12 18h.008v.008H12V18z"/>
                                </svg>
                            </div>

                            <div>
                                <p class="font-extrabold text-[#003C2F]">
                                    O que fazer agora?
                                </p>

                                <p class="text-sm text-[#60756B] mt-1">
                                    Você pode voltar para a página inicial ou retornar para a tela anterior.
                                    Se o problema continuar, avise o time de desenvolvimento.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 mt-8">
                        <a href="{{ url('/') }}"
                           class="inline-flex items-center justify-center gap-2 bg-[#004D3A] hover:bg-[#003C2F] text-white px-6 py-3.5 rounded-2xl font-extrabold shadow-lg transition">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-5 h-5"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125C4.5 20.496 5.004 21 5.625 21H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/>
                            </svg>
                            Voltar ao início
                        </a>

                        <button type="button"
                                onclick="history.back()"
                                class="inline-flex items-center justify-center gap-2 bg-white hover:bg-[#F8FBF8] text-[#004D3A] border border-[#DCE7DE] px-6 py-3.5 rounded-2xl font-extrabold shadow-sm transition">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-5 h-5"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="1.8"
                                      d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                            </svg>
                            Voltar
                        </button>
                    </div>

                    <p class="text-xs text-[#8A9B92] mt-7">
                        Se estiver em produção, mantenha o <strong>APP_DEBUG=false</strong> para exibir esta tela amigável.
                    </p>

                </div>

            </div>

            <!-- ILUSTRAÇÃO -->
            <div class="order-1 lg:order-2 fade-up">

                <div class="relative min-h-[420px] sm:min-h-[520px] flex items-center justify-center">

                    <!-- BALÕES DE TEXTO -->
                    <div class="absolute top-2 sm:top-8 left-4 sm:left-10 bg-white border border-[#DCE7DE] rounded-3xl shadow-lg px-5 py-3 bubble-float">
                        <p class="text-sm font-extrabold text-[#003C2F]">
                            @yield('doctor_phrase')
                        </p>
                    </div>

                    <div class="absolute bottom-6 right-2 sm:right-8 bg-[#004D3A] text-white rounded-3xl shadow-lg px-5 py-3 bubble-float"
                         style="animation-delay: .8s;">
                        <p class="text-sm font-extrabold">
                            Sistema em observação 🩺
                        </p>
                    </div>

                    <!-- ILUSTRAÇÃO SVG -->
                    <svg viewBox="0 0 720 620"
                         class="w-full max-w-[560px] drop-shadow-2xl"
                         xmlns="http://www.w3.org/2000/svg">

                        <!-- CHÃO -->
                        <ellipse cx="360" cy="552" rx="260" ry="36" fill="#DCE7DE"/>

                        <!-- TOMADA PAREDE -->
                        <rect x="530" y="280" width="92" height="110" rx="24" fill="#FFFFFF" stroke="#BFD8C5" stroke-width="6"/>
                        <circle cx="562" cy="328" r="8" fill="#8A9B92"/>
                        <circle cx="590" cy="328" r="8" fill="#8A9B92"/>
                        <rect x="565" y="355" width="22" height="8" rx="4" fill="#8A9B92"/>

                        <!-- FAÍSCAS -->
                        <g class="spark">
                            <path d="M514 310 L492 296 L504 324 L478 330 L508 340 L492 364 L520 348"
                                  fill="none"
                                  stroke="@yield('accent_color', '#F59E0B')"
                                  stroke-width="7"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"/>
                        </g>

                        <!-- CABO -->
                        <path class="cable-shake"
                              d="M352 402 C405 432, 440 392, 484 380 C500 376, 510 368, 526 360"
                              fill="none"
                              stroke="#263F38"
                              stroke-width="12"
                              stroke-linecap="round"/>

                        <!-- PLUG -->
                        <g class="plug-out">
                            <rect x="468" y="336" width="58" height="42" rx="12" fill="#263F38"/>
                            <rect x="518" y="346" width="18" height="8" rx="4" fill="#263F38"/>
                            <rect x="518" y="363" width="18" height="8" rx="4" fill="#263F38"/>
                        </g>

                        <!-- MÁQUINA / MONITOR -->
                        <g class="monitor-blink">
                            <rect x="105" y="155" width="290" height="245" rx="34" fill="#FFFFFF" stroke="#BFD8C5" stroke-width="7"/>
                            <rect x="130" y="190" width="240" height="150" rx="20" fill="#063B32"/>
                            <text x="250" y="258"
                                  text-anchor="middle"
                                  font-size="54"
                                  font-weight="900"
                                  fill="@yield('accent_color', '#00A63E')">
                                @yield('code')
                            </text>
                            <text x="250" y="295"
                                  text-anchor="middle"
                                  font-size="18"
                                  font-weight="800"
                                  fill="#EAF5EF">
                                @yield('monitor_text')
                            </text>

                            <!-- LINHA CARDÍACA -->
                            <path class="line-move"
                                  d="M150 320 H185 L198 296 L216 338 L234 310 H272 L286 286 L306 340 L324 320 H356"
                                  fill="none"
                                  stroke="@yield('accent_color', '#00A63E')"
                                  stroke-width="7"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"/>

                            <rect x="190" y="400" width="120" height="28" rx="14" fill="#BFD8C5"/>
                            <rect x="150" y="426" width="200" height="30" rx="15" fill="#8A9B92"/>
                        </g>

                        <!-- MÉDICO CARTOON -->
                        <g class="doctor-float">

                            <!-- PERNAS -->
                            <path d="M430 425 C418 460, 415 498, 416 538"
                                  fill="none"
                                  stroke="#263F38"
                                  stroke-width="22"
                                  stroke-linecap="round"/>
                            <path d="M500 425 C512 460, 516 498, 514 538"
                                  fill="none"
                                  stroke="#263F38"
                                  stroke-width="22"
                                  stroke-linecap="round"/>

                            <!-- SAPATOS -->
                            <ellipse cx="410" cy="545" rx="33" ry="14" fill="#263F38"/>
                            <ellipse cx="525" cy="545" rx="33" ry="14" fill="#263F38"/>

                            <!-- JALECO -->
                            <path d="M390 260 C372 310, 374 388, 408 440 C432 456, 496 456, 522 438 C548 382, 552 315, 532 260 Z"
                                  fill="#FFFFFF"
                                  stroke="#BFD8C5"
                                  stroke-width="6"/>

                            <!-- CAMISA -->
                            <path d="M435 270 L468 418 L500 270 Z"
                                  fill="#EAF5EF"/>

                            <!-- CRUZ NO JALECO -->
                            <rect x="505" y="315" width="28" height="10" rx="5" fill="#00A63E"/>
                            <rect x="514" y="306" width="10" height="28" rx="5" fill="#00A63E"/>

                            <!-- BRAÇO ESQUERDO -->
                            <path d="M398 292 C362 320, 350 354, 352 396"
                                  fill="none"
                                  stroke="#FFFFFF"
                                  stroke-width="30"
                                  stroke-linecap="round"/>
                            <path d="M398 292 C362 320, 350 354, 352 396"
                                  fill="none"
                                  stroke="#BFD8C5"
                                  stroke-width="6"
                                  stroke-linecap="round"/>

                            <!-- MÃO ESQUERDA -->
                            <circle cx="352" cy="402" r="18" fill="#F2B690"/>

                            <!-- BRAÇO DIREITO PUXANDO PLUG -->
                            <path d="M530 292 C555 320, 548 348, 518 360"
                                  fill="none"
                                  stroke="#FFFFFF"
                                  stroke-width="30"
                                  stroke-linecap="round"/>
                            <path d="M530 292 C555 320, 548 348, 518 360"
                                  fill="none"
                                  stroke="#BFD8C5"
                                  stroke-width="6"
                                  stroke-linecap="round"/>

                            <!-- MÃO DIREITA -->
                            <circle cx="516" cy="360" r="18" fill="#F2B690"/>

                            <!-- PESCOÇO -->
                            <rect x="445" y="220" width="48" height="50" rx="20" fill="#F2B690"/>

                            <!-- CABEÇA -->
                            <circle cx="468" cy="188" r="62" fill="#F2B690"/>

                            <!-- CABELO -->
                            <path d="M410 178 C420 112, 500 104, 530 158 C500 142, 475 143, 442 130 C432 150, 422 165, 410 178 Z"
                                  fill="#263F38"/>

                            <!-- ORELHAS -->
                            <circle cx="406" cy="194" r="13" fill="#E9A983"/>
                            <circle cx="530" cy="194" r="13" fill="#E9A983"/>

                            <!-- OLHOS -->
                            <circle cx="446" cy="190" r="6" fill="#263F38"/>
                            <circle cx="490" cy="190" r="6" fill="#263F38"/>

                            <!-- SOBRANCELHAS -->
                            <path d="M435 174 Q446 166 457 174"
                                  fill="none"
                                  stroke="#263F38"
                                  stroke-width="5"
                                  stroke-linecap="round"/>
                            <path d="M479 174 Q490 166 501 174"
                                  fill="none"
                                  stroke="#263F38"
                                  stroke-width="5"
                                  stroke-linecap="round"/>

                            <!-- BOCA -->
                            @yield('mouth_svg')

                            <!-- ESTETOSCÓPIO -->
                            <path d="M430 282 C430 330, 456 342, 468 342 C480 342, 506 330, 506 282"
                                  fill="none"
                                  stroke="#263F38"
                                  stroke-width="7"
                                  stroke-linecap="round"/>
                            <circle cx="468" cy="352" r="14" fill="#263F38"/>
                            <circle cx="468" cy="352" r="6" fill="#EAF5EF"/>

                            <!-- PRANCHETA -->
                            <g transform="translate(330 245) rotate(-8)">
                                <rect x="0" y="0" width="82" height="104" rx="14" fill="#FFFFFF" stroke="#BFD8C5" stroke-width="5"/>
                                <rect x="22" y="-8" width="38" height="20" rx="8" fill="#8A9B92"/>
                                <path d="M18 36 H64 M18 58 H64 M18 80 H50"
                                      stroke="#60756B"
                                      stroke-width="5"
                                      stroke-linecap="round"/>
                            </g>

                        </g>

                        <!-- CORAÇÃO DE SAÚDE -->
                        <g class="heart-beat">
                            <path d="M110 110 C90 85, 48 98, 50 136 C52 178, 110 205, 110 205 C110 205, 168 178, 170 136 C172 98, 130 85, 110 110 Z"
                                  fill="#00A63E"/>
                            <path d="M78 148 H98 L108 128 L122 166 L134 148 H151"
                                  fill="none"
                                  stroke="#FFFFFF"
                                  stroke-width="7"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"/>
                        </g>

                    </svg>

                </div>

            </div>

        </section>

    </main>

</body>
</html>