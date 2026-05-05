@extends('layout.app')

@section('title', 'Videoaulas')

@section('content')

@php
    use Illuminate\Support\Facades\DB;

    $aulasConteudo = collect();
    $totalEtapasCurso = 0;
    $etapasConcluidasCurso = 0;

    if (isset($modulos)) {
        foreach ($modulos as $moduloIndex => $modulo) {
            $aulasModulo = collect($modulo->aulas ?? []);
            $totalEtapasModulo = 0;
            $etapasConcluidasModulo = 0;

            foreach ($aulasModulo as $aulaIndex => $aula) {
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

                $totalEtapasModulo++;
                $totalEtapasCurso++;

                if ($aulaAssistida) {
                    $etapasConcluidasModulo++;
                    $etapasConcluidasCurso++;
                }

                if ($avaliacaoId) {
                    $totalEtapasModulo++;
                    $totalEtapasCurso++;

                    if ($posTesteConcluido) {
                        $etapasConcluidasModulo++;
                        $etapasConcluidasCurso++;
                    }
                }

                $aulasConteudo->push((object) [
                    'id' => $aula->id,
                    'titulo' => $aula->titulo,
                    'video_url' => $aula->video_url,
                    'modulo_id' => $modulo->id,
                    'modulo_nome' => $modulo->nome,
                    'modulo_numero' => $moduloIndex + 1,
                    'ordem' => $aulaIndex + 1,
                    'avaliacao_id' => $avaliacaoId,
                    'aula_assistida' => $aulaAssistida,
                    'pos_teste_concluido' => $posTesteConcluido,
                    'atividade_concluida' => $atividadeConcluida,
                ]);
            }

            $modulo->progresso_calculado = $totalEtapasModulo > 0
                ? round(($etapasConcluidasModulo / $totalEtapasModulo) * 100)
                : 0;
        }
    }

    $aulaAtual = $aulasConteudo->firstWhere('atividade_concluida', false) ?? $aulasConteudo->first();
    $aulasModuloAtual = $aulaAtual
        ? $aulasConteudo->where('modulo_id', $aulaAtual->modulo_id)->values()
        : collect();
    $progressoCurso = $totalEtapasCurso > 0 ? round(($etapasConcluidasCurso / $totalEtapasCurso) * 100) : 0;
@endphp

<style>
    @media (min-width: 1024px) {
        #sidebarAluno {
            width: 13.5rem;
            padding: 1.25rem;
        }

        #sidebarAluno nav {
            font-size: 0.75rem;
        }

        #sidebarAluno nav a,
        #sidebarAluno nav button {
            padding: 0.7rem 0.75rem;
        }
    }
</style>

<div class="min-h-screen bg-[#eef4ef] text-[#073f34]">
    <div class="flex min-h-screen">

        @include('partials.sidebar-aluno')

        <div class="flex-1 flex flex-col min-w-0">
            <header class="h-16 bg-[#f8fbf8] border-b border-[#dfe8e1] px-5 sm:px-8 flex items-center justify-end">
                <div class="flex items-center gap-3 pl-14 lg:pl-0">
                    <span class="text-xs font-medium text-[#0d5b4c]">Meu Perfil</span>
                    <div class="w-9 h-9 rounded-full border border-[#d6e3da] bg-white overflow-hidden">
                        @if(auth()->user()->foto)
                            <img
                                src="{{ asset('storage/' . auth()->user()->foto) }}"
                                alt="Foto do perfil"
                                class="w-full h-full object-cover"
                            >
                        @endif
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-auto px-4 py-6 sm:px-8 lg:px-10">
                @if(session('success'))
                    <div class="mb-4 bg-emerald-50 text-emerald-700 border border-emerald-200 px-4 py-3 rounded-lg text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 bg-red-50 text-red-700 border border-red-200 px-4 py-3 rounded-lg text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                @if($aulaAtual)
                    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_280px] gap-6 max-w-6xl mx-auto">
                        <section class="min-w-0">
                            <div class="mb-4">
                                <p class="text-xs text-[#52665f]">
                                    {{ $aulaAtual->modulo_nome }} <span class="px-1">›</span>
                                    <strong class="text-[#0d5b4c]">Módulo {{ str_pad($aulaAtual->modulo_numero, 2, '0', STR_PAD_LEFT) }}: Protocolos de Decisão</strong>
                                </p>

                                <h1 class="mt-2 max-w-3xl text-2xl sm:text-3xl font-extrabold leading-tight text-[#004d3a]">
                                    Aula {{ $aulaAtual->ordem }}: {{ $aulaAtual->titulo }}
                                </h1>
                            </div>

                            <button
                                type="button"
                                data-video="{{ $aulaAtual->video_url }}"
                                data-aula="{{ $aulaAtual->id }}"
                                data-avaliacao="{{ $aulaAtual->avaliacao_id }}"
                                onclick="abrirModal(this.dataset.video, this.dataset.aula, this.dataset.avaliacao)"
                                class="group relative w-full aspect-video bg-black rounded-lg shadow-sm overflow-hidden flex items-center justify-center"
                                aria-label="Assistir aula {{ $aulaAtual->titulo }}"
                            >
                                <span class="w-14 h-14 rounded-full border-4 border-[#6f819f] flex items-center justify-center transition group-hover:scale-105 group-hover:border-white">
                                    <svg class="w-7 h-7 text-[#6f819f] ml-1 group-hover:text-white" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M8 5v14l11-7z"/>
                                    </svg>
                                </span>
                            </button>

                            <div class="grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_180px] gap-4 mt-5">
                                <article class="bg-white/70 border border-[#e3ebe5] rounded-lg p-5">
                                    <h2 class="text-sm font-extrabold text-[#004d3a] mb-2">Sobre esta aula</h2>
                                    <p class="text-sm leading-relaxed text-[#50645d]">
                                        Nesta aula, acompanhe o conteúdo do módulo e finalize as etapas para liberar as próximas atividades do curso.
                                    </p>
                                </article>

                                <aside class="bg-[#005543] text-white rounded-lg p-5 flex flex-col justify-between gap-4">
                                    <div>
                                        <p class="text-[11px] uppercase font-bold tracking-wide text-white/70">Sua atividade</p>
                                        <div class="mt-3 flex items-center justify-between text-xs font-semibold">
                                            <span>Progresso da aula</span>
                                            <span>{{ $aulaAtual->atividade_concluida ? '100' : ($aulaAtual->aula_assistida ? '50' : '0') }}%</span>
                                        </div>
                                        <div class="mt-2 h-2 bg-white/20 rounded-full overflow-hidden">
                                            <div
                                                class="h-full bg-[#90d8c6] rounded-full"
                                                style="width: {{ $aulaAtual->atividade_concluida ? '100' : ($aulaAtual->aula_assistida ? '50' : '0') }}%;"
                                            ></div>
                                        </div>
                                    </div>

                                    @if($aulaAtual->avaliacao_id && $aulaAtual->aula_assistida && !$aulaAtual->pos_teste_concluido)
                                        <button type="button" onclick="fazerPosTeste('{{ $aulaAtual->avaliacao_id }}')" class="bg-white text-[#005543] rounded-md px-4 py-3 text-xs font-bold hover:bg-[#ecf7f3] transition">
                                            Realizar teste rápido
                                        </button>
                                    @elseif($aulaAtual->avaliacao_id && $aulaAtual->pos_teste_concluido)
                                        <button type="button" onclick="verResultadoPosTeste('{{ $aulaAtual->avaliacao_id }}')" class="bg-white text-[#005543] rounded-md px-4 py-3 text-xs font-bold hover:bg-[#ecf7f3] transition">
                                            Ver resultado
                                        </button>
                                    @else
                                        <button
                                            type="button"
                                            data-video="{{ $aulaAtual->video_url }}"
                                            data-aula="{{ $aulaAtual->id }}"
                                            data-avaliacao="{{ $aulaAtual->avaliacao_id }}"
                                            onclick="abrirModal(this.dataset.video, this.dataset.aula, this.dataset.avaliacao)"
                                            class="bg-white text-[#005543] rounded-md px-4 py-3 text-xs font-bold hover:bg-[#ecf7f3] transition"
                                        >
                                            Assistir aula
                                        </button>
                                    @endif
                                </aside>
                            </div>
                        </section>

                        <aside class="bg-white rounded-xl border border-[#e3ebe5] shadow-sm p-5 h-max">
                            <div class="flex items-start justify-between gap-4 mb-4">
                                <h2 class="text-sm font-extrabold leading-tight text-[#004d3a]">Conteúdo do Módulo</h2>
                                <div class="bg-[#eef1ed] text-[#52645e] rounded px-3 py-2 text-[10px] leading-none font-bold text-center">
                                    {{ $etapasConcluidasCurso }}/{{ $totalEtapasCurso }}<br>
                                    Aulas
                                </div>
                            </div>

                            <div class="space-y-3">
                                @foreach($aulasModuloAtual as $itemAula)
                                    @php
                                        $statusLabel = $itemAula->atividade_concluida
                                            ? 'Concluída'
                                            : ($itemAula->aula_assistida ? 'Pendente' : ($itemAula->id === $aulaAtual->id ? 'Assistindo agora' : 'Pendente'));

                                        $statusClasses = $itemAula->atividade_concluida
                                            ? 'text-[#00815f]'
                                            : ($itemAula->id === $aulaAtual->id ? 'text-[#004d3a]' : 'text-[#53645f]');
                                    @endphp

                                    <button
                                        type="button"
                                        data-video="{{ $itemAula->video_url }}"
                                        data-aula="{{ $itemAula->id }}"
                                        data-avaliacao="{{ $itemAula->avaliacao_id }}"
                                        onclick="abrirModal(this.dataset.video, this.dataset.aula, this.dataset.avaliacao)"
                                        class="w-full flex items-center gap-3 p-2 rounded-lg text-left transition {{ $itemAula->id === $aulaAtual->id ? 'bg-[#edf2ee]' : 'hover:bg-[#f3f7f3]' }}"
                                    >
                                        <div class="w-16 h-16 rounded-md bg-[#d7dfd9] shrink-0 overflow-hidden relative">
                                            <div class="absolute inset-0 bg-gradient-to-br from-[#003f35] via-[#0a6755] to-[#d6ddd8]"></div>
                                            <div class="absolute inset-0 flex items-center justify-center">
                                                <span class="w-8 h-8 rounded-full border border-white/70 bg-black/20 flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-white ml-0.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path d="M8 5v14l11-7z"/>
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <p class="text-[10px] uppercase font-extrabold {{ $statusClasses }}">{{ $statusLabel }}</p>
                                            <p class="text-xs font-bold text-[#173f36] leading-snug truncate">
                                                {{ $itemAula->ordem }}. {{ $itemAula->titulo }}
                                            </p>
                                            <p class="text-[10px] text-[#73827d] mt-1">{{ $itemAula->avaliacao_id ? '42 min' : '28 min' }}</p>
                                        </div>
                                    </button>
                                @endforeach
                            </div>

                            <div class="mt-5 pt-4 border-t border-[#edf1ee]">
                                <div class="flex justify-between text-xs font-bold text-[#004d3a] mb-2">
                                    <span>Progresso do curso</span>
                                    <span>{{ $progressoCurso }}%</span>
                                </div>
                                <div class="h-2 bg-[#e7eee9] rounded-full overflow-hidden">
                                    <div class="h-full bg-[#005543] rounded-full" style="width: {{ $progressoCurso }}%;"></div>
                                </div>
                            </div>
                        </aside>
                    </div>
                @else
                    <div class="max-w-2xl mx-auto bg-white rounded-xl border border-[#e3ebe5] shadow-sm p-8 text-center">
                        <h1 class="text-2xl font-extrabold text-[#004d3a]">Nenhum módulo disponível ainda.</h1>
                        <p class="mt-2 text-sm text-[#52645e]">Assim que novas aulas forem cadastradas, elas aparecerão aqui.</p>
                    </div>
                @endif
            </main>
        </div>
    </div>
</div>

<!-- MODAL DE VIDEO -->
<div id="modalVideo" class="fixed inset-0 bg-black bg-opacity-70 hidden items-center justify-center z-50 px-4">
    <div class="bg-white w-[900px] max-w-full rounded-xl p-4 relative border border-[#dfe8e1] shadow-2xl">
        <button onclick="fecharModal()" class="absolute top-3 right-4 text-2xl leading-none text-[#52645e] hover:text-red-600 transition">×</button>

        <iframe
            id="videoFrame"
            class="w-full h-[240px] sm:h-[440px] rounded-lg bg-black"
            src=""
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen>
        </iframe>

        <div class="mt-4 flex flex-col sm:flex-row sm:justify-between gap-3">
            <button onclick="fecharModal()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition">
                Fechar
            </button>

            <button onclick="marcarAssistida()" class="bg-[#005543] hover:bg-[#004636] text-white px-4 py-2 rounded-lg transition">
                Concluir aula
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
                    confirmButtonColor: '#005543',
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
                    confirmButtonColor: '#005543'
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
                        extra += ' - Correta';
                    }

                    if (marcada && !correta) {
                        fundo = '#fee2e2';
                        borda = '#ef4444';
                        extra += ' - Sua resposta';
                    }

                    if (marcada && correta) {
                        extra += ' - Sua resposta';
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
                confirmButtonColor: '#005543',
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
</script>

@endsection
