@extends('layout.app')

@section('title', 'Dashboard Aluno')

@section('content')

<div class="flex h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-white">

    @include('partials.sidebar-aluno')

    <div class="flex-1 flex flex-col">

        <!-- TOPBAR -->
        <header class="bg-slate-900 border-b border-slate-800 px-8 h-16 flex items-center justify-between shadow">
            <div>
                <h1 class="text-lg font-bold">Dashboard</h1>
                <p class="text-xs text-slate-500">Continue seu aprendizado</p>
            </div>
        </header>

        <!-- CONTEÚDO -->
        <main class="flex-1 overflow-auto p-8">

            <!-- ALERTAS -->
            @if(session('success'))
                <div class="mb-4 bg-green-500/20 text-green-400 border border-green-500 p-4 rounded-xl">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-500/20 text-red-400 border border-red-500 p-4 rounded-xl">
                    {{ session('error') }}
                </div>
            @endif

            <!-- MÓDULOS -->
            @if(isset($modulos))
                <div id="modulos" class="mb-10">

                    <h2 class="text-xl font-bold mb-4">📚 Módulos</h2>

                    @forelse($modulos as $modulo)
                        <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl mb-3">

                            <h3 class="font-semibold mb-2 cursor-pointer flex items-center justify-between"
                                onclick="toggleModulo({{ $modulo->id }})">
                                <span>▶ {{ $modulo->nome }}</span>
                                <span class="text-xs text-slate-500">Clique para abrir</span>
                            </h3>

                            <div id="modulo-{{ $modulo->id }}" class="hidden mt-3">

                                @if(isset($modulo->aulas))
                                    @forelse($modulo->aulas as $aula)

                                        @php
                                            $avaliacaoId = DB::table('avaliacoes')
                                                ->where('aula_id', $aula->id)
                                                ->value('id');

                                            $aulaAssistida = DB::table('aulas_assistidas')
                                                ->where('aluno_id', auth()->id())
                                                ->where('aula_id', $aula->id)
                                                ->where('assistido', true)
                                                ->exists();

                                            $posTesteConcluido = false;

                                            if ($avaliacaoId) {
                                                $posTesteConcluido = DB::table('notas')
                                                    ->where('aluno_id', auth()->id())
                                                    ->where('avaliacao_id', $avaliacaoId)
                                                    ->exists();
                                            }

                                            $atividadeConcluida = $aulaAssistida && (!$avaliacaoId || $posTesteConcluido);
                                        @endphp

                                        <div class="
                                            p-3 rounded mb-2 border transition
                                            {{ $atividadeConcluida
                                                ? 'bg-slate-800/40 border-emerald-500/20 opacity-70'
                                                : 'bg-slate-800 border-slate-700'
                                            }}
                                        ">

                                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">

                                                <div>
                                                    <div class="flex items-center gap-2 flex-wrap">

                                                        @if($atividadeConcluida)
                                                            <span class="text-emerald-400 text-sm">✓</span>
                                                        @endif

                                                        <p class="
                                                            font-semibold transition
                                                            {{ $atividadeConcluida
                                                                ? 'text-slate-500 line-through decoration-slate-500'
                                                                : 'text-white'
                                                            }}
                                                        ">
                                                            {{ $aula->titulo }}
                                                        </p>

                                                        @if($atividadeConcluida)
                                                            <span class="text-[11px] bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 px-2 py-0.5 rounded-full">
                                                                Concluído
                                                            </span>
                                                        @endif

                                                    </div>

                                                    @if($atividadeConcluida)
                                                        <span class="inline-block mt-2 text-xs bg-slate-700/60 text-slate-400 border border-slate-600 px-2 py-1 rounded">
                                                            Aula e atividade finalizadas
                                                        </span>
                                                    @elseif($aulaAssistida && $avaliacaoId && !$posTesteConcluido)
                                                        <span class="inline-block mt-2 text-xs bg-blue-500/20 text-blue-400 border border-blue-500/40 px-2 py-1 rounded">
                                                            Aula assistida — pós-teste liberado
                                                        </span>
                                                    @elseif($aulaAssistida)
                                                        <span class="inline-block mt-2 text-xs bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 px-2 py-1 rounded">
                                                            Aula concluída
                                                        </span>
                                                    @else
                                                        <span class="inline-block mt-2 text-xs bg-yellow-500/20 text-yellow-300 border border-yellow-500/40 px-2 py-1 rounded">
                                                            Assista para liberar o pós-teste
                                                        </span>
                                                    @endif
                                                </div>

                                                <div class="flex flex-wrap gap-2">

                                                    @if(!$aulaAssistida)
                                                        <button
                                                            type="button"
                                                            data-video="{{ $aula->video_url }}"
                                                            data-aula="{{ $aula->id }}"
                                                            data-avaliacao="{{ $avaliacaoId }}"
                                                            onclick="abrirModal(this.dataset.video, this.dataset.aula, this.dataset.avaliacao)"
                                                            class="bg-emerald-600 hover:bg-emerald-700 px-3 py-1.5 rounded text-sm transition"
                                                        >
                                                            ▶ Assistir
                                                        </button>
                                                    @else
                                                        <button
                                                            type="button"
                                                            data-video="{{ $aula->video_url }}"
                                                            data-aula="{{ $aula->id }}"
                                                            data-avaliacao="{{ $avaliacaoId }}"
                                                            onclick="abrirModal(this.dataset.video, this.dataset.aula, this.dataset.avaliacao)"
                                                            class="bg-slate-600 hover:bg-slate-700 px-3 py-1.5 rounded text-sm transition"
                                                        >
                                                            ▶ Assistir novamente
                                                        </button>

                                                        @if($avaliacaoId && !$posTesteConcluido)
                                                            <button
                                                                type="button"
                                                                onclick="fazerPosTeste('{{ $avaliacaoId }}')"
                                                                class="bg-blue-600 hover:bg-blue-700 px-3 py-1.5 rounded text-sm transition"
                                                            >
                                                                📝 Fazer pós-teste
                                                            </button>
                                                        @elseif($avaliacaoId && $posTesteConcluido)
                                                            <button
                                                                type="button"
                                                                onclick="verResultadoPosTeste('{{ $avaliacaoId }}')"
                                                                class="bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-400 border border-emerald-500/40 px-3 py-1.5 rounded text-sm transition">
                                                                ✓ Pós-teste feito
                                                            </button>
                                                        @else
                                                            <span class="text-xs text-slate-400 self-center">
                                                                Sem pós-teste
                                                            </span>
                                                        @endif
                                                    @endif

                                                </div>

                                            </div>

                                        </div>

                                    @empty
                                        <p class="text-slate-400 text-sm bg-slate-800 p-3 rounded">
                                            Nenhuma aula neste módulo.
                                        </p>
                                    @endforelse
                                @endif

                            </div>

                        </div>
                    @empty
                        <div class="bg-slate-900 border border-slate-800 p-5 rounded-xl text-slate-400">
                            Nenhum módulo encontrado.
                        </div>
                    @endforelse

                </div>
            @endif

            <!-- AVISOS -->
            @if(isset($avisosRecentes) && $avisosRecentes->count() > 0)
                <div class="mb-8">
                    <h2 class="mb-4 font-bold text-lg flex items-center gap-2">
                        📢 Avisos
                    </h2>

                    <div class="grid gap-4">
                        @foreach($avisosRecentes as $aviso)
                            <div class="bg-slate-900 border border-slate-800 p-5 rounded-xl shadow hover:shadow-lg transition border-l-4
                                @if($aviso->categoria == 'urgente') border-red-500
                                @elseif($aviso->categoria == 'informativo') border-blue-500
                                @else border-emerald-500
                                @endif">

                                <p class="font-semibold text-base mb-1">
                                    {{ $aviso->titulo }}
                                </p>

                                <p class="text-sm text-slate-400">
                                    {{ $aviso->mensagem ?? $aviso->descricao ?? '' }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- CARDS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow hover:scale-[1.02] transition">
                    <p class="text-slate-400 text-sm mb-2">Progresso</p>
                    <h3 class="text-3xl font-bold text-emerald-400">
                        {{ number_format($progresso, 0) }}%
                    </h3>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow hover:scale-[1.02] transition">
                    <p class="text-slate-400 text-sm mb-2">Aulas</p>
                    <h3 class="text-3xl font-bold">
                        {{ $aulasAssistidas }} / {{ $totalAulas }}
                    </h3>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow hover:scale-[1.02] transition">
                    <p class="text-slate-400 text-sm mb-2">Testes</p>
                    <h3 class="text-3xl font-bold text-blue-400">
                        {{ $testesPendentes }}
                    </h3>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow hover:scale-[1.02] transition">
                    <p class="text-slate-400 text-sm mb-2">Média</p>
                    <h3 class="text-3xl font-bold text-yellow-400">
                        {{ number_format($media, 1) }}
                    </h3>
                </div>

            </div>

            <!-- CERTIFICADO -->
            <div class="mb-8">
                <a href="{{ route('certificado.gerar', 1) }}"
                   class="bg-green-600 px-6 py-3 rounded-lg shadow hover:bg-green-700 transition">
                    🎓 Baixar Certificado
                </a>
            </div>

        </main>

    </div>

</div>

<!-- MODAL VIDEO -->
<div id="modalVideo" class="fixed inset-0 bg-black bg-opacity-70 hidden items-center justify-center z-50">

    <div class="bg-slate-900 w-[800px] max-w-[95%] rounded-xl p-4 relative border border-slate-700">

        <button onclick="fecharModal()" class="absolute top-2 right-3 text-xl hover:text-red-400 transition">×</button>

        <iframe
            id="videoFrame"
            class="w-full h-[400px] rounded"
            src=""
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen>
        </iframe>

        <div class="mt-4 flex justify-between gap-3">
            <button onclick="fecharModal()" class="bg-gray-600 hover:bg-gray-700 px-4 py-2 rounded transition">
                Fechar
            </button>

            <button onclick="marcarAssistida()" class="bg-emerald-600 hover:bg-emerald-700 px-4 py-2 rounded transition">
                ✔ Concluir
            </button>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let aulaIdAtual = null;
let avaliacaoIdAtual = null;

function normalizarUrlYoutube(url) {
    if (!url) return '';

    let video = String(url).trim();

    if (video.includes('watch?v=')) {
        video = video.replace('watch?v=', 'embed/');
    }

    if (video.includes('youtu.be/')) {
        video = video.replace('youtu.be/', 'www.youtube.com/embed/');
    }

    return video;
}

function abrirModal(url, aulaId, avaliacaoId = null) {
    aulaIdAtual = aulaId;
    avaliacaoIdAtual = avaliacaoId && avaliacaoId !== 'null' && avaliacaoId !== '' ? avaliacaoId : null;

    const video = normalizarUrlYoutube(url);

    if (!video) {
        Swal.fire({
            icon: 'error',
            title: 'Vídeo não encontrado',
            text: 'Esta aula não possui link de vídeo cadastrado.',
            confirmButtonColor: '#dc2626'
        });
        return;
    }

    const modal = document.getElementById('modalVideo');
    const frame = document.getElementById('videoFrame');

    frame.src = video;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function fecharModal() {
    const modal = document.getElementById('modalVideo');
    const frame = document.getElementById('videoFrame');

    modal.classList.add('hidden');
    modal.classList.remove('flex');
    frame.src = "";
}

function fazerPosTeste(avaliacaoId) {
    if (!avaliacaoId || avaliacaoId === 'null') {
        Swal.fire({
            icon: 'info',
            title: 'Sem pós-teste',
            text: 'Esta aula ainda não possui pós-teste cadastrado.',
            confirmButtonColor: '#2563eb'
        });
        return;
    }

    window.location.href = '/avaliacoes/' + avaliacaoId;
}

function marcarAssistida() {
    if (!aulaIdAtual) return;

    fetch('/assistir-aula/' + aulaIdAtual)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro ao marcar aula como assistida.');
            }

            return response.json();
        })
        .then(() => {
            fecharModal();

            if (avaliacaoIdAtual) {
                Swal.fire({
                    icon: 'success',
                    title: 'Aula concluída!',
                    text: 'Deseja fazer o pós-teste agora?',
                    showCancelButton: true,
                    confirmButtonText: 'Sim, fazer agora',
                    cancelButtonText: 'Depois',
                    confirmButtonColor: '#2563eb',
                    cancelButtonColor: '#64748b'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fazerPosTeste(avaliacaoIdAtual);
                    } else {
                        location.reload();
                    }
                });
            } else {
                Swal.fire({
                    icon: 'success',
                    title: 'Aula concluída!',
                    text: 'Não há pós-teste cadastrado para esta aula.',
                    confirmButtonColor: '#2563eb'
                }).then(() => {
                    location.reload();
                });
            }
        })
        .catch(() => {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Não foi possível concluir a aula. Tente novamente.',
                confirmButtonColor: '#dc2626'
            });
        });
}

function verResultadoPosTeste(avaliacaoId) {
    if (!avaliacaoId || avaliacaoId === 'null') {
        Swal.fire({
            icon: 'info',
            title: 'Sem pós-teste',
            text: 'Esta aula ainda não possui pós-teste cadastrado.',
            confirmButtonColor: '#2563eb'
        });
        return;
    }

    fetch('/avaliacoes/' + avaliacaoId + '/resultado')
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: data.message || 'Não foi possível carregar o resultado.',
                    confirmButtonColor: '#dc2626'
                });
                return;
            }

            let html = `
                <div id="conteudoResultadoPDF" style="text-align:left; font-family: Arial, sans-serif;">
                    <div style="background:#f1f5f9; border-radius:14px; padding:16px; margin-bottom:16px;">
                        <h2 style="margin:0; color:#0f172a; font-size:20px;">
                            ${data.avaliacao.titulo || 'Pós-teste'}
                        </h2>
                        <p style="margin:8px 0 0; color:#475569;">
                            Nota: <strong>${data.nota !== null ? Number(data.nota).toFixed(1) : 'Não registrada'}</strong>
                        </p>
                    </div>
            `;

            data.perguntas.forEach((pergunta, index) => {
                html += `
                    <div style="border:1px solid #e2e8f0; border-radius:14px; padding:14px; margin-bottom:14px;">
                        <p style="font-weight:bold; color:#0f172a; margin-bottom:10px;">
                            ${index + 1}. ${pergunta.pergunta}
                        </p>
                `;

                pergunta.respostas.forEach(resposta => {
                    const correta = resposta.correta === true || resposta.correta === 1 || resposta.correta === '1';
                    const marcada = Number(pergunta.resposta_aluno_id) === Number(resposta.id);

                    let fundo = '#ffffff';
                    let borda = '#e2e8f0';
                    let extra = '';

                    if (correta) {
                        fundo = '#dcfce7';
                        borda = '#22c55e';
                        extra += ' ✅ Correta';
                    }

                    if (marcada && !correta) {
                        fundo = '#fee2e2';
                        borda = '#ef4444';
                        extra += ' ❌ Sua resposta';
                    }

                    if (marcada && correta) {
                        extra += ' — Sua resposta';
                    }

                    html += `
                        <div style="background:${fundo}; border:1px solid ${borda}; border-radius:10px; padding:10px; margin-bottom:8px; color:#334155;">
                            ${resposta.resposta}
                            <strong style="color:#0f172a;">${extra}</strong>
                        </div>
                    `;
                });

                if (!pergunta.resposta_aluno_id) {
                    html += `
                        <p style="color:#dc2626; font-size:13px; margin-top:8px;">
                            Resposta do aluno não registrada para esta pergunta.
                        </p>
                    `;
                }

                html += `</div>`;
            });

            html += `</div>`;

            Swal.fire({
                title: 'Resultado do pós-teste',
                html: html,
                width: 850,
                showCancelButton: true,
                confirmButtonText: 'Gerar PDF',
                cancelButtonText: 'Fechar',
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#64748b'
            }).then((result) => {
                if (result.isConfirmed) {
                    gerarPDFResultado();
                }
            });
        })
        .catch(() => {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Não foi possível carregar o resultado do pós-teste.',
                confirmButtonColor: '#dc2626'
            });
        });
}

function gerarPDFResultado() {
    const conteudo = document.getElementById('conteudoResultadoPDF');

    if (!conteudo) {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Não foi possível gerar o PDF.',
            confirmButtonColor: '#dc2626'
        });
        return;
    }

    const janela = window.open('', '_blank');

    janela.document.write(`
        <html>
            <head>
                <title>Resultado do Pós-teste</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        padding: 30px;
                        color: #0f172a;
                    }

                    h1, h2, h3 {
                        color: #0f172a;
                    }

                    @media print {
                        button {
                            display: none;
                        }
                    }
                </style>
            </head>
            <body>
                ${conteudo.innerHTML}

                <script>
                    window.onload = function() {
                        window.print();
                    }
                <\/script>
            </body>
        </html>
    `);

    janela.document.close();
}

function bloqueado() {
    Swal.fire({
        icon: 'warning',
        title: 'Bloqueado',
        text: 'Assista a aula primeiro!',
        confirmButtonColor: '#2563eb'
    });
}

function toggleModulo(id) {
    const el = document.getElementById('modulo-' + id);

    if (el) {
        el.classList.toggle('hidden');
    }
}
</script>

@endsection