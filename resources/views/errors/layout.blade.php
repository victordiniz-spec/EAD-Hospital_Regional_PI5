<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Erro')</title>

    @vite('resources/css/app.css')

    <style>
        @keyframes floatY {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-12px); }
        }

        @keyframes pulseSoft {
            0%, 100% { opacity: 0.45; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(1.08); }
        }

        @keyframes slideGlow {
            0% { transform: translateX(-20px); opacity: 0.3; }
            50% { transform: translateX(20px); opacity: 0.6; }
            100% { transform: translateX(-20px); opacity: 0.3; }
        }

        .floatY {
            animation: floatY 4s ease-in-out infinite;
        }

        .pulseSoft {
            animation: pulseSoft 5s ease-in-out infinite;
        }

        .slideGlow {
            animation: slideGlow 8s ease-in-out infinite;
        }

        .glass {
            background: rgba(255,255,255,0.72);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }
    </style>
</head>
<body class="min-h-screen overflow-hidden bg-gradient-to-br from-emerald-50 via-white to-slate-100 text-slate-800">

    <!-- FUNDO DECORATIVO -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-24 -left-24 w-72 h-72 bg-emerald-200/50 rounded-full blur-3xl pulseSoft"></div>
        <div class="absolute top-20 right-0 w-80 h-80 bg-cyan-200/40 rounded-full blur-3xl pulseSoft"></div>
        <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-teal-100/40 rounded-full blur-3xl slideGlow"></div>
    </div>

    <main class="relative z-10 min-h-screen flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-5xl grid lg:grid-cols-2 gap-8 items-center">

            <!-- LADO ESQUERDO -->
            <div class="order-2 lg:order-1">
                <div class="glass border border-white/60 shadow-2xl rounded-3xl p-8 sm:p-10">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-100 text-emerald-700 text-sm font-semibold mb-6">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Integrar ReSaúde
                    </div>

                    <h1 class="text-5xl sm:text-6xl font-black text-slate-900 tracking-tight mb-2">
                        @yield('code')
                    </h1>

                    <h2 class="text-2xl sm:text-3xl font-bold text-slate-800 mb-4">
                        @yield('headline')
                    </h2>

                    <p class="text-slate-600 text-base sm:text-lg leading-relaxed mb-8">
                        @yield('message')
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="{{ url('/') }}"
                           class="inline-flex items-center justify-center px-6 py-3 rounded-2xl bg-emerald-600 text-white font-semibold shadow-lg hover:bg-emerald-700 transition duration-300">
                            Voltar ao início
                        </a>

                        <button onclick="history.back()"
                                class="inline-flex items-center justify-center px-6 py-3 rounded-2xl bg-white text-slate-700 border border-slate-200 font-semibold hover:bg-slate-50 transition duration-300">
                            Voltar página anterior
                        </button>
                    </div>

                    <div class="mt-8 pt-6 border-t border-slate-200 text-sm text-slate-500">
                        Se o problema persistir, entre em contato com o suporte do sistema.
                    </div>
                </div>
            </div>

            <!-- LADO DIREITO -->
            <div class="order-1 lg:order-2 flex justify-center">
                <div class="relative w-full max-w-md h-[340px] sm:h-[420px] floatY">
                    @yield('illustration')
                </div>
            </div>

        </div>
    </main>
</body>
</html>