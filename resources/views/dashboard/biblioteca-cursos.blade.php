@extends('layout.app')

@section('title', 'Biblioteca de Cursos')

@section('content')

@php
    use Illuminate\Support\Facades\DB;

    $todosCursos = \App\Models\Curso::orderBy('nome')->get();
@endphp

<style>
    html, body {
        background: #F3F7F3 !important;
        margin: 0;
        padding: 0;
        width: 100%;
        min-height: 100%;
    }

    #app {
        background: #F3F7F3 !important;
        min-height: 100vh;
        width: 100%;
    }

    .highlight-search {
        background: #DCFCE7;
        color: #004D3A;
        padding: 1px 3px;
        border-radius: 6px;
        font-weight: 800;
    }
</style>

<div class="flex min-h-screen w-full bg-[#F3F7F3] text-[#003C2F] overflow-x-hidden">

    @include('partials.sidebar-professor')

    <main class="flex-1 min-w-0 w-full bg-[#F3F7F3] overflow-x-hidden">

        @include('partials.navbar')

        <section class="p-4 sm:p-6 lg:p-8">

            <!-- CABEÇALHO -->
            <div class="mb-7 flex flex-col xl:flex-row xl:items-end xl:justify-between gap-5">

                <div>
                    <div class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest text-[#00A63E] mb-2">
                        <span class="w-2 h-2 rounded-full bg-[#00A63E]"></span>
                        Reutilização de conteúdo
                    </div>

                    <h1 class="text-3xl sm:text-4xl font-extrabold text-[#003C2F] tracking-tight">
                        Biblioteca de Cursos
                    </h1>

                    <p class="text-sm text-[#60756B] mt-2 max-w-3xl">
                        Pesquise, filtre, reutilize cursos antigos, importe módulos completos ou exclua cópias que não serão mais usadas.
                    </p>
                </div>

                <a href="{{ route('videoaulas') }}"
                   class="bg-white border border-[#DCE7DE] text-[#004D3A] px-5 py-3 rounded-2xl hover:bg-[#F8FBF8] transition flex items-center justify-center gap-2 text-sm font-extrabold shadow-sm">
                    Voltar para videoaulas
                </a>

            </div>

            <!-- FILTROS -->
            <form method="GET" action="{{ route('biblioteca.cursos') }}"
                  class="bg-white border border-[#E3EBE4] rounded-3xl shadow-sm p-5 sm:p-6 mb-7">

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">

                    <!-- PESQUISA AO VIVO -->
                    <div class="lg:col-span-6">
                        <label class="block text-[11px] uppercase tracking-widest font-extrabold text-[#60756B] mb-2">
                            Pesquisar
                        </label>

                        <div class="relative">
                            <span class="absolute inset-y-0 left-5 flex items-center text-[#8A9B92]">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-6 h-6"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="1.8"
                                          d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0z"/>
                                </svg>
                            </span>

                            <input type="text"
                                   name="pesquisa"
                                   id="pesquisaBiblioteca"
                                   value="{{ request('pesquisa') }}"
                                   oninput="pesquisarBibliotecaAoVivo()"
                                   placeholder="Pesquisar como no WhatsApp: curso, módulo, aula, teste..."
                                   autocomplete="off"
                                   class="w-full px-5 py-4 pl-14 pr-12 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-bold placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition">

                            <button type="button"
                                    onclick="limparPesquisaBiblioteca()"
                                    class="absolute inset-y-0 right-4 hidden items-center text-[#8A9B92] hover:text-[#003C2F]"
                                    id="btnLimparPesquisaBiblioteca">
                                ✕
                            </button>
                        </div>

                        <p class="text-xs text-[#60756B] mt-2">
                            Digite e os cursos aparecem automaticamente, sem precisar apertar Enter.
                        </p>
                    </div>

                    <!-- DATA INICIAL -->
                    <div class="lg:col-span-2">
                        <label class="block text-[11px] uppercase tracking-widest font-extrabold text-[#60756B] mb-2">
                            Data inicial
                        </label>

                        <input type="date"
                               name="data_inicio"
                               value="{{ request('data_inicio') }}"
                               class="w-full px-4 py-4 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-bold focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition">
                    </div>

                    <!-- DATA FINAL -->
                    <div class="lg:col-span-2">
                        <label class="block text-[11px] uppercase tracking-widest font-extrabold text-[#60756B] mb-2">
                            Data final
                        </label>

                        <input type="date"
                               name="data_fim"
                               value="{{ request('data_fim') }}"
                               class="w-full px-4 py-4 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-bold focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition">
                    </div>

                    <!-- BOTÕES -->
                    <div class="lg:col-span-2 flex flex-col sm:flex-row lg:flex-col gap-3">
                        <button type="submit"
                                class="w-full bg-[#004D3A] hover:bg-[#003C2F] text-white px-5 py-4 rounded-2xl font-extrabold transition shadow-sm">
                            Filtrar data
                        </button>

                        <a href="{{ route('biblioteca.cursos') }}"
                           class="w-full bg-white border border-[#DCE7DE] text-[#60756B] px-5 py-4 rounded-2xl font-extrabold hover:bg-[#F8FBF8] transition text-center">
                            Limpar
                        </a>
                    </div>

                </div>

                <!-- CONTADOR -->
                <div class="mt-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl px-4 py-3">
                    <p class="text-sm text-[#60756B] font-bold">
                        Resultado da busca:
                        <span id="contadorBiblioteca" class="text-[#004D3A] font-extrabold">
                            {{ $cursos->count() }}
                        </span>
                        curso(s) encontrado(s)
                    </p>

                    <p class="text-xs text-[#8A9B92] font-semibold">
                        A busca considera nome do curso, descrição, módulos, aulas e pós-testes.
                    </p>
                </div>

            </form>

            <!-- SEM RESULTADOS -->
            <div id="semResultadosBiblioteca"
                 class="hidden bg-white border border-[#E3EBE4] rounded-3xl p-10 text-center mb-7">

                <div class="w-20 h-20 mx-auto rounded-full bg-[#EAF5EF] text-[#004D3A] flex items-center justify-center mb-5 text-3xl">
                    🔎
                </div>

                <h3 class="text-xl font-extrabold text-[#003C2F]">
                    Nenhum curso encontrado
                </h3>

                <p class="text-sm text-[#60756B] mt-2">
                    Tente pesquisar com outra palavra ou limpe os filtros.
                </p>
            </div>

            <!-- LISTA -->
            <div id="listaCursosBiblioteca" class="grid grid-cols-1 lg:grid-cols-2 2xl:grid-cols-3 gap-6">

                @forelse($cursos as $curso)

                    @php
                        $totalModulos = DB::table('modulos')
                            ->where('curso_id', $curso->id)
                            ->count();

                        $totalAulas = DB::table('aulas')
                            ->where('curso_id', $curso->id)
                            ->count();

                        $totalPostestes = DB::table('avaliacoes')
                            ->join('aulas', 'avaliacoes.aula_id', '=', 'aulas.id')
                            ->where('aulas.curso_id', $curso->id)
                            ->count();

                        $modulosCurso = DB::table('modulos')
                            ->where('curso_id', $curso->id)
                            ->orderBy('ordem')
                            ->get();

                        $textoBuscaCurso = ($curso->nome ?? '') . ' ' . ($curso->descricao ?? '') . ' ';

                        foreach ($modulosCurso as $moduloBusca) {
                            $textoBuscaCurso .= ' ' . ($moduloBusca->nome ?? '');

                            $aulasBusca = DB::table('aulas')
                                ->where('modulo_id', $moduloBusca->id)
                                ->orderBy('id')
                                ->get();

                            foreach ($aulasBusca as $aulaBusca) {
                                $textoBuscaCurso .= ' ' . ($aulaBusca->titulo ?? '') . ' ' . ($aulaBusca->descricao ?? '');

                                $temTesteBusca = DB::table('avaliacoes')
                                    ->where('aula_id', $aulaBusca->id)
                                    ->exists();

                                if ($temTesteBusca) {
                                    $textoBuscaCurso .= ' pós teste posteste teste avaliação avaliacao';
                                }
                            }
                        }
                    @endphp

                    <div class="curso-biblioteca-item bg-white border border-[#E3EBE4] rounded-3xl shadow-sm overflow-hidden hover:shadow-lg transition"
                         data-search="{{ strtolower($textoBuscaCurso . ' ' . ($curso->created_at ? \Carbon\Carbon::parse($curso->created_at)->format('d/m/Y') : '')) }}">

                        <div class="p-6 border-b border-[#E3EBE4]">

                            <div class="flex items-start justify-between gap-4">

                                <div class="min-w-0">
                                    <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                        Curso #{{ $curso->id }}
                                    </p>

                                    <h2 class="titulo-curso-biblioteca text-2xl font-extrabold text-[#003C2F] mt-2 break-words">
                                        {{ $curso->nome }}
                                    </h2>

                                    <p class="descricao-curso-biblioteca text-sm text-[#60756B] mt-2 leading-relaxed break-words">
                                        {{ $curso->descricao ?? 'Sem descrição cadastrada.' }}
                                    </p>

                                    <p class="text-xs text-[#8A9B92] mt-3 font-bold">
                                        Criado em {{ $curso->created_at ? \Carbon\Carbon::parse($curso->created_at)->format('d/m/Y H:i') : 'sem data' }}
                                    </p>
                                </div>

                                <div class="w-14 h-14 rounded-2xl bg-[#EAF5EF] text-[#004D3A] flex items-center justify-center shrink-0 text-2xl">
                                    📚
                                </div>

                            </div>

                            <div class="grid grid-cols-3 gap-3 mt-5">

                                <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-3 text-center">
                                    <p class="text-xl font-extrabold text-[#004D3A]">
                                        {{ $totalModulos }}
                                    </p>

                                    <p class="text-[10px] uppercase tracking-widest text-[#60756B] font-extrabold mt-1">
                                        Módulos
                                    </p>
                                </div>

                                <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-3 text-center">
                                    <p class="text-xl font-extrabold text-[#004D3A]">
                                        {{ $totalAulas }}
                                    </p>

                                    <p class="text-[10px] uppercase tracking-widest text-[#60756B] font-extrabold mt-1">
                                        Aulas
                                    </p>
                                </div>

                                <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-3 text-center">
                                    <p class="text-xl font-extrabold text-[#004D3A]">
                                        {{ $totalPostestes }}
                                    </p>

                                    <p class="text-[10px] uppercase tracking-widest text-[#60756B] font-extrabold mt-1">
                                        Testes
                                    </p>
                                </div>

                            </div>

                        </div>

                        <div class="p-5">

                            <button type="button"
                                    onclick="toggleDetalhesCurso({{ $curso->id }})"
                                    class="w-full bg-[#F8FBF8] border border-[#DCE7DE] text-[#004D3A] px-4 py-3 rounded-2xl font-extrabold hover:bg-[#EAF5EF] transition mb-3">
                                Ver estrutura
                            </button>

                            <div id="detalhesCurso{{ $curso->id }}" class="hidden mb-4 space-y-3">

                                @forelse($modulosCurso as $modulo)

                                    @php
                                        $aulasModulo = DB::table('aulas')
                                            ->where('modulo_id', $modulo->id)
                                            ->orderBy('id')
                                            ->get();
                                    @endphp

                                    <div class="modulo-biblioteca-item bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-4"
                                         data-search="{{ strtolower(($modulo->nome ?? '') . ' ' . ($curso->nome ?? '')) }}">

                                        <div class="flex items-start justify-between gap-3 mb-3">
                                            <div>
                                                <h3 class="font-extrabold text-[#003C2F]">
                                                    {{ $modulo->nome }}
                                                </h3>

                                                <p class="text-xs text-[#60756B] mt-1">
                                                    {{ $aulasModulo->count() }} aula(s)
                                                </p>
                                            </div>

                                            <button type="button"
                                                    onclick="abrirModalImportarModulo({{ $modulo->id }}, @json($modulo->nome))"
                                                    class="bg-[#004D3A] text-white px-3 py-2 rounded-xl text-xs font-extrabold hover:bg-[#003C2F] transition shrink-0">
                                                Importar módulo
                                            </button>
                                        </div>

                                        <div class="space-y-2">

                                            @forelse($aulasModulo as $aula)
                                                @php
                                                    $temTeste = DB::table('avaliacoes')
                                                        ->where('aula_id', $aula->id)
                                                        ->exists();
                                                @endphp

                                                <div class="aula-biblioteca-item bg-white border border-[#E3EBE4] rounded-xl p-3"
                                                     data-search="{{ strtolower(($aula->titulo ?? '') . ' ' . ($aula->descricao ?? '') . ' ' . ($modulo->nome ?? '') . ' ' . ($curso->nome ?? '') . ' ' . ($temTeste ? 'pós teste posteste teste avaliação avaliacao' : 'sem teste')) }}">

                                                    <p class="text-sm font-bold text-[#003C2F]">
                                                        {{ $aula->titulo }}
                                                    </p>

                                                    <p class="text-[11px] mt-1 {{ $temTeste ? 'text-green-700' : 'text-[#8A9B92]' }} font-bold">
                                                        {{ $temTeste ? 'Com pós-teste' : 'Sem pós-teste' }}
                                                    </p>

                                                </div>
                                            @empty
                                                <p class="text-xs text-[#60756B]">
                                                    Nenhuma aula neste módulo.
                                                </p>
                                            @endforelse

                                        </div>

                                    </div>

                                @empty
                                    <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-4 text-sm text-[#60756B]">
                                        Nenhum módulo cadastrado neste curso.
                                    </div>
                                @endforelse

                            </div>

                            <div class="grid grid-cols-1 gap-3">
                                <form method="POST" action="{{ route('biblioteca.cursos.duplicar', $curso->id) }}">
                                    @csrf

                                    <button type="submit"
                                            class="w-full bg-[#004D3A] hover:bg-[#003C2F] text-white px-5 py-4 rounded-2xl font-extrabold transition shadow-sm flex items-center justify-center gap-2">
                                        Usar este curso novamente
                                    </button>
                                </form>

                                <form id="formExcluirCurso{{ $curso->id }}"
                                      method="POST"
                                      action="{{ route('biblioteca.cursos.excluir', $curso->id) }}">
                                    @csrf
                                    @method('DELETE')

                                    <button type="button"
                                            onclick="confirmarExcluirCurso({{ $curso->id }}, @json($curso->nome))"
                                            class="w-full bg-red-50 border border-red-100 hover:bg-red-100 text-red-600 px-5 py-4 rounded-2xl font-extrabold transition flex items-center justify-center gap-2">
                                        Excluir curso/cópia
                                    </button>
                                </form>
                            </div>

                        </div>

                    </div>

                @empty
                    <div class="lg:col-span-2 2xl:col-span-3 bg-white border border-[#E3EBE4] rounded-3xl p-10 text-center">
                        <div class="w-20 h-20 mx-auto rounded-full bg-[#EAF5EF] text-[#004D3A] flex items-center justify-center mb-5 text-3xl">
                            📚
                        </div>

                        <h3 class="text-xl font-extrabold text-[#003C2F]">
                            Nenhum curso encontrado
                        </h3>

                        <p class="text-sm text-[#60756B] mt-2">
                            Tente limpar os filtros ou cadastre um curso na tela de videoaulas.
                        </p>
                    </div>
                @endforelse

            </div>

        </section>

    </main>

</div>

<!-- MODAL IMPORTAR MÓDULO -->
<div id="modalImportarModulo"
     class="fixed inset-0 hidden items-center justify-center bg-black/50 backdrop-blur-sm z-[90] px-4">

    <div class="bg-white w-full max-w-lg p-6 rounded-3xl border border-[#E3EBE4] shadow-2xl">

        <div class="flex items-start justify-between mb-6">
            <div>
                <h2 class="text-2xl font-extrabold text-[#003C2F]">
                    Importar módulo
                </h2>

                <p class="text-sm text-[#60756B] mt-1">
                    Escolha para qual curso esse módulo será copiado.
                </p>
            </div>

            <button type="button"
                    onclick="fecharModalImportarModulo()"
                    class="bg-[#F1F6F2] hover:bg-[#E6EFE8] text-[#003C2F] p-2 rounded-xl transition">
                ✕
            </button>
        </div>

        <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-4 mb-5">
            <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                Módulo selecionado
            </p>

            <p id="nomeModuloImportar" class="font-extrabold text-[#003C2F] mt-1">
                Módulo
            </p>
        </div>

        <form id="formImportarModulo" method="POST">
            @csrf

            <label class="block text-[11px] uppercase tracking-widest font-extrabold text-[#60756B] mb-2">
                Curso de destino
            </label>

            <select name="curso_destino_id"
                    class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-bold focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition"
                    required>
                @foreach($todosCursos as $cursoDestino)
                    <option value="{{ $cursoDestino->id }}">
                        {{ $cursoDestino->nome }}
                    </option>
                @endforeach
            </select>

            <div class="flex flex-col sm:flex-row justify-end gap-3 mt-7">
                <button type="button"
                        onclick="fecharModalImportarModulo()"
                        class="px-5 py-3 rounded-2xl bg-[#F1F6F2] text-[#60756B] font-bold hover:bg-[#E6EFE8] transition">
                    Cancelar
                </button>

                <button type="submit"
                        class="px-5 py-3 rounded-2xl bg-[#004D3A] text-white font-bold hover:bg-[#003C2F] transition shadow-sm">
                    Importar módulo
                </button>
            </div>
        </form>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function toggleDetalhesCurso(id) {
        const detalhes = document.getElementById('detalhesCurso' + id);

        if (detalhes) {
            detalhes.classList.toggle('hidden');
        }
    }

    function abrirModalImportarModulo(id, nome) {
        const modal = document.getElementById('modalImportarModulo');
        const form = document.getElementById('formImportarModulo');
        const titulo = document.getElementById('nomeModuloImportar');

        if (!modal || !form || !titulo) return;

        titulo.innerText = nome ?? 'Módulo';
        form.action = '/biblioteca-modulos/' + id + '/duplicar';

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function fecharModalImportarModulo() {
        const modal = document.getElementById('modalImportarModulo');

        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function confirmarExcluirCurso(id, nome) {
        Swal.fire({
            title: 'Excluir curso?',
            html: `
                <p style="color:#475569;margin-bottom:10px;">Você está prestes a excluir:</p>
                <strong style="color:#003C2F;font-size:16px;">${nome ?? 'este curso'}</strong>
                <p style="color:#ef4444;margin-top:14px;font-size:14px;">
                    Isso também remove módulos, aulas, pós-testes, perguntas e respostas desse curso.
                </p>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            background: '#ffffff',
            color: '#003C2F'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('formExcluirCurso' + id);

                if (form) {
                    form.submit();
                }
            }
        });
    }

    function normalizarPesquisa(texto) {
        return (texto || '')
            .toString()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    }

    function pesquisarBibliotecaAoVivo() {
        const input = document.getElementById('pesquisaBiblioteca');
        const btnLimpar = document.getElementById('btnLimparPesquisaBiblioteca');
        const contador = document.getElementById('contadorBiblioteca');
        const semResultados = document.getElementById('semResultadosBiblioteca');

        const termo = normalizarPesquisa(input ? input.value : '');
        const cursos = document.querySelectorAll('.curso-biblioteca-item');

        let encontrados = 0;

        if (btnLimpar) {
            btnLimpar.classList.toggle('hidden', termo.length === 0);
            btnLimpar.classList.toggle('flex', termo.length > 0);
        }

        cursos.forEach((curso) => {
            const textoCurso = normalizarPesquisa(curso.dataset.search || '');
            const modulos = curso.querySelectorAll('.modulo-biblioteca-item');
            const aulas = curso.querySelectorAll('.aula-biblioteca-item');

            let encontrouCurso = termo === '' || textoCurso.includes(termo);

            modulos.forEach((modulo) => {
                const textoModulo = normalizarPesquisa(modulo.dataset.search || '');
                const encontrouModulo = termo === '' || textoModulo.includes(termo) || textoCurso.includes(termo);

                if (encontrouModulo) {
                    encontrouCurso = true;
                }

                modulo.classList.toggle('hidden', !encontrouModulo && termo !== '');
            });

            aulas.forEach((aula) => {
                const textoAula = normalizarPesquisa(aula.dataset.search || '');
                const encontrouAula = termo === '' || textoAula.includes(termo) || textoCurso.includes(termo);

                if (encontrouAula) {
                    encontrouCurso = true;
                }

                aula.classList.toggle('hidden', !encontrouAula && termo !== '');
            });

            curso.classList.toggle('hidden', !encontrouCurso);

            if (encontrouCurso) {
                encontrados++;
            }
        });

        if (contador) {
            contador.innerText = encontrados;
        }

        if (semResultados) {
            semResultados.classList.toggle('hidden', encontrados > 0);
        }
    }

    function limparPesquisaBiblioteca() {
        const input = document.getElementById('pesquisaBiblioteca');

        if (input) {
            input.value = '';
            input.focus();
        }

        pesquisarBibliotecaAoVivo();
    }

    const modalImportarModulo = document.getElementById('modalImportarModulo');

    if (modalImportarModulo) {
        modalImportarModulo.addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalImportarModulo();
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            fecharModalImportarModulo();
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        pesquisarBibliotecaAoVivo();
    });
</script>

@endsection