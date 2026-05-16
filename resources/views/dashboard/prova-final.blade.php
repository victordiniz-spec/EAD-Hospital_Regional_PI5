@extends('layout.app')

@section('title', 'Prova Final')

@section('content')

@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;

    $alunoId = auth()->id();

    // ACESSO DE TESTE
    // Senha temporária: 123
    $acessoTeste = request('teste') === '123';

    /*
    |--------------------------------------------------------------------------
    | CURSO ATUAL DO ALUNO
    |--------------------------------------------------------------------------
    | A prova final deve considerar somente o curso atual do aluno.
    | Prioridade:
    | 1. Curso vinculado na tabela matriculas
    | 2. Curso publicado/ativo mais recente
    | 3. Último curso cadastrado
    */

    $cursoAtual = null;

    if (Schema::hasTable('matriculas') && Schema::hasTable('cursos')) {
        $cursoMatriculadoId = DB::table('matriculas')
            ->where('aluno_id', $alunoId)
            ->orderBy('id', 'desc')
            ->value('curso_id');

        if ($cursoMatriculadoId) {
            $cursoAtual = DB::table('cursos')
                ->where('id', $cursoMatriculadoId)
                ->first();
        }
    }

    if (!$cursoAtual && Schema::hasTable('cursos')) {
        $queryCurso = DB::table('cursos');

        if (Schema::hasColumn('cursos', 'publicado')) {
            $queryCurso->where('publicado', true);
        } elseif (Schema::hasColumn('cursos', 'ativo')) {
            $queryCurso->where('ativo', true);
        } elseif (Schema::hasColumn('cursos', 'status')) {
            $queryCurso->whereIn('status', ['publicado', 'ativo', 'aprovado']);
        }

        $cursoAtual = $queryCurso
            ->orderBy('id', 'desc')
            ->first();
    }

    $cursoAtualId = $cursoAtual->id ?? null;

    /*
    |--------------------------------------------------------------------------
    | AULAS E PÓS-TESTES DO CURSO ATUAL
    |--------------------------------------------------------------------------
    */

    $modulosCursoIds = collect();
    $aulasCursoIds = collect();

    if ($cursoAtualId && Schema::hasTable('modulos')) {
        $modulosCursoIds = DB::table('modulos')
            ->where('curso_id', $cursoAtualId)
            ->pluck('id');
    }

    if ($modulosCursoIds->count() > 0 && Schema::hasTable('aulas')) {
        $aulasCursoIds = DB::table('aulas')
            ->whereIn('modulo_id', $modulosCursoIds)
            ->pluck('id');
    }

    // Caso exista curso_id diretamente na tabela aulas, usa também como segurança.
    if ($cursoAtualId && Schema::hasTable('aulas') && Schema::hasColumn('aulas', 'curso_id')) {
        $aulasPorCursoDireto = DB::table('aulas')
            ->where('curso_id', $cursoAtualId)
            ->pluck('id');

        $aulasCursoIds = $aulasCursoIds
            ->merge($aulasPorCursoDireto)
            ->unique()
            ->values();
    }

    $totalAulas = $aulasCursoIds->count();

    $totalAulasAssistidas = $totalAulas > 0 && Schema::hasTable('aulas_assistidas')
        ? DB::table('aulas_assistidas')
            ->where('aluno_id', $alunoId)
            ->whereIn('aula_id', $aulasCursoIds)
            ->where('assistido', true)
            ->distinct('aula_id')
            ->count('aula_id')
        : 0;

    $avaliacoesPosTesteIds = collect();

    if ($totalAulas > 0 && Schema::hasTable('avaliacoes')) {
        $avaliacoesPosTesteQuery = DB::table('avaliacoes')
            ->whereIn('aula_id', $aulasCursoIds);

        if (Schema::hasColumn('avaliacoes', 'tipo')) {
            $avaliacoesPosTesteQuery->where(function ($query) {
                $query->where('tipo', 'normal')
                      ->orWhere('tipo', 'pos_teste')
                      ->orWhere('tipo', 'pós-teste')
                      ->orWhereNull('tipo');
            });
        }

        $avaliacoesPosTesteIds = $avaliacoesPosTesteQuery->pluck('id');
    }

    $totalPosTestes = $avaliacoesPosTesteIds->count();

    $totalPosTestesFeitos = $totalPosTestes > 0 && Schema::hasTable('notas')
        ? DB::table('notas')
            ->where('aluno_id', $alunoId)
            ->whereIn('avaliacao_id', $avaliacoesPosTesteIds)
            ->distinct('avaliacao_id')
            ->count('avaliacao_id')
        : 0;

    /*
    |--------------------------------------------------------------------------
    | REGRA DE LIBERAÇÃO
    |--------------------------------------------------------------------------
    | Agora a prova final libera com 70% do curso atual concluído.
    | Não é mais obrigatório concluir 100% das aulas/módulos.
    */

    $totalEtapas = $totalAulas + $totalPosTestes;
    $etapasConcluidas = $totalAulasAssistidas + $totalPosTestesFeitos;

    $porcentagemConclusao = $totalEtapas > 0
        ? round(($etapasConcluidas / $totalEtapas) * 100)
        : 0;

    $cursoTemConteudo = $cursoAtual && $totalEtapas > 0;

    $aulasOk = $totalAulas > 0
        ? $totalAulasAssistidas >= $totalAulas
        : false;

    $posTestesOk = $totalPosTestes > 0
        ? $totalPosTestesFeitos >= $totalPosTestes
        : true;

    $provaLiberada = ($cursoTemConteudo && $porcentagemConclusao >= 70) || $acessoTeste;

    $faltamPorcentagem = max(0, 70 - $porcentagemConclusao);

    $tentativas = isset($avaliacao) && isset($avaliacao->tentativas)
        ? $avaliacao->tentativas
        : 2;
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
</style>

<div class="flex min-h-screen w-full bg-[#F3F7F3] text-[#003C2F] overflow-x-hidden">

    @include('partials.sidebar-aluno')

    <main class="flex-1 min-w-0 w-full bg-[#F3F7F3] overflow-x-hidden">

        @include('partials.navbar')

        <section class="p-4 sm:p-6 lg:p-8">

            <!-- CABEÇALHO -->
            <div class="mb-7 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-5">

                <div>
                    <div class="flex items-center gap-2 text-[11px] font-extrabold uppercase tracking-widest text-[#60756B] mb-2">
                        <span>Aluno</span>
                        <span>›</span>
                        <span class="text-[#004D3A]">Prova Final</span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl font-extrabold text-[#003C2F] tracking-tight">
                        Prova Final
                    </h1>

                    <p class="text-sm text-[#60756B] mt-2 max-w-2xl">
                        A avaliação final será liberada quando você concluir pelo menos 70% do curso atual.
                    </p>

                    @if($cursoAtual)
                        <p class="text-sm text-[#004D3A] mt-2 font-extrabold">
                            Curso atual: {{ $cursoAtual->nome }}
                        </p>
                    @else
                        <p class="text-sm text-red-600 mt-2 font-extrabold">
                            Nenhum curso atual foi encontrado para este aluno.
                        </p>
                    @endif

                    @if($acessoTeste)
                        <div class="mt-4 inline-flex items-center gap-2 bg-yellow-100 text-yellow-800 border border-yellow-200 px-4 py-2 rounded-2xl text-sm font-bold">
                            <span>⚠️</span>
                            Acesso de teste ativado
                        </div>
                    @endif
                </div>

                @if(isset($avaliacao))
                    <div class="bg-white border border-[#E3EBE4] rounded-3xl px-5 py-4 shadow-sm">
                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                            Tempo limite
                        </p>

                        <p class="text-2xl font-extrabold text-[#004D3A] mt-1">
                            {{ $avaliacao->tempo_limite ?? 60 }} min
                        </p>
                    </div>
                @endif

            </div>

            <!-- ALERTAS -->
            @if(session('success'))
                <div class="mb-5 bg-green-100 text-green-700 px-4 py-3 rounded-2xl border border-green-200 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-5 bg-red-100 text-red-700 px-4 py-3 rounded-2xl border border-red-200 shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            @if(!isset($avaliacao))

                <!-- SEM PROVA -->
                <div class="bg-white border border-[#E3EBE4] rounded-3xl p-8 shadow-sm text-center">

                    <div class="w-20 h-20 mx-auto rounded-full bg-yellow-100 text-yellow-700 flex items-center justify-center mb-5">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="w-10 h-10"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="1.8"
                                  d="M12 9v3.75m0 3.75h.008v.008H12V16.5zm9-4.5a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                        </svg>
                    </div>

                    <h2 class="text-2xl font-extrabold text-[#003C2F] mb-2">
                        Nenhuma prova disponível
                    </h2>

                    <p class="text-[#60756B] text-sm max-w-md mx-auto">
                        A prova final ainda não foi cadastrada pelo professor. Volte novamente mais tarde.
                    </p>

                    <a href="{{ route('dashboard.aluno') }}"
                       class="inline-flex items-center justify-center mt-6 bg-[#004D3A] text-white px-6 py-3 rounded-2xl font-bold hover:bg-[#003C2F] transition">
                        Voltar ao dashboard
                    </a>

                </div>

            @elseif(!$provaLiberada)

                <!-- PROVA BLOQUEADA -->
                <div class="grid grid-cols-1 xl:grid-cols-12 gap-7">

                    <div class="xl:col-span-8">

                        <div class="bg-white border border-[#E3EBE4] rounded-3xl p-6 sm:p-8 shadow-sm">

                            <div class="w-20 h-20 rounded-full bg-red-50 text-red-600 flex items-center justify-center mb-5">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-10 h-10"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="1.8"
                                          d="M16.5 10.5V6.75a4.5 4.5 0 0 0-9 0v3.75m-.75 11.25h10.5A2.25 2.25 0 0 0 19.5 19.5v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 12.75v6.75a2.25 2.25 0 0 0 2.25 2.25z"/>
                                </svg>
                            </div>

                            <h2 class="text-2xl sm:text-3xl font-extrabold text-[#003C2F] mb-3">
                                Prova final bloqueada
                            </h2>

                            <p class="text-[#60756B] text-sm leading-relaxed max-w-2xl">
                                Para acessar a prova final, você precisa concluir pelo menos 70% do curso atual, considerando aulas assistidas e pós-testes realizados.
                            </p>

                            @if($cursoAtual)
                                <p class="text-sm text-[#004D3A] font-extrabold mt-3">
                                    Curso atual: {{ $cursoAtual->nome }}
                                </p>
                            @endif

                            @if($totalEtapas > 0)
                                <p class="text-sm text-[#60756B] mt-2">
                                    Você está com {{ $porcentagemConclusao }}%. Faltam {{ $faltamPorcentagem }} ponto(s) percentual(is) para liberar.
                                </p>
                            @endif

                            <div class="mt-6 bg-[#F8FBF8] border border-[#E3EBE4] rounded-3xl p-5">

                                <div class="flex items-center justify-between mb-3">
                                    <p class="text-sm font-extrabold text-[#003C2F]">
                                        Progresso do curso atual
                                    </p>

                                    <p class="text-sm font-extrabold text-[#004D3A]">
                                        {{ $porcentagemConclusao }}%
                                    </p>
                                </div>

                                <div class="w-full h-3 bg-[#E8EFE9] rounded-full overflow-hidden">
                                    <div class="h-full bg-[#004D3A] rounded-full transition-all duration-700"
                                         style="width: {{ $porcentagemConclusao }}%;">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-5">

                                    <div class="bg-white rounded-2xl border border-[#E3EBE4] p-4">
                                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                            Videoaulas assistidas
                                        </p>

                                        <p class="text-2xl font-extrabold mt-1 {{ $aulasOk ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $totalAulasAssistidas }} / {{ $totalAulas }}
                                        </p>
                                    </div>

                                    <div class="bg-white rounded-2xl border border-[#E3EBE4] p-4">
                                        <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                            Pós-testes feitos
                                        </p>

                                        <p class="text-2xl font-extrabold mt-1 {{ $posTestesOk ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $totalPosTestesFeitos }} / {{ $totalPosTestes }}
                                        </p>
                                    </div>

                                </div>

                            </div>

                            <div class="flex flex-col sm:flex-row gap-3 mt-6">
                                <a href="{{ route('aluno.aulas') }}"
                                   class="inline-flex items-center justify-center bg-[#004D3A] text-white px-6 py-3 rounded-2xl font-bold hover:bg-[#003C2F] transition">
                                    Continuar minhas aulas
                                </a>

                                <button type="button"
                                        onclick="abrirAcessoTesteProva()"
                                        class="inline-flex items-center justify-center bg-yellow-100 text-yellow-800 border border-yellow-200 px-6 py-3 rounded-2xl font-bold hover:bg-yellow-200 transition">
                                    Acesso de teste
                                </button>
                            </div>

                        </div>

                    </div>

                    <aside class="xl:col-span-4">
                        <div class="bg-white border border-[#E3EBE4] rounded-3xl p-6 shadow-sm">

                            <h3 class="text-xl font-extrabold text-[#003C2F] mb-4">
                                O que falta?
                            </h3>

                            <div class="space-y-4">

                                <div class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-xl {{ $aulasOk ? 'bg-green-100 text-green-700' : 'bg-red-50 text-red-600' }} flex items-center justify-center shrink-0">
                                        @if($aulasOk)
                                            ✓
                                        @else
                                            !
                                        @endif
                                    </div>

                                    <div>
                                        <p class="font-bold text-[#003C2F]">
                                            Assistir as aulas do curso
                                        </p>

                                        <p class="text-sm text-[#60756B]">
                                            {{ $totalAulasAssistidas }} de {{ $totalAulas }} concluídas.
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-xl {{ $posTestesOk ? 'bg-green-100 text-green-700' : 'bg-red-50 text-red-600' }} flex items-center justify-center shrink-0">
                                        @if($posTestesOk)
                                            ✓
                                        @else
                                            !
                                        @endif
                                    </div>

                                    <div>
                                        <p class="font-bold text-[#003C2F]">
                                            Concluir os pós-testes do curso
                                        </p>

                                        <p class="text-sm text-[#60756B]">
                                            {{ $totalPosTestesFeitos }} de {{ $totalPosTestes }} concluídos.
                                        </p>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </aside>

                </div>

            @else

                <!-- PROVA LIBERADA -->
                <form action="{{ route('prova.final.responder') }}" method="POST" id="formProvaFinalAluno">
                    @csrf

                    <div class="grid grid-cols-1 xl:grid-cols-12 gap-7">

                        <!-- INFORMAÇÕES -->
                        <aside class="xl:col-span-4 2xl:col-span-3">

                            <div class="bg-white rounded-3xl border border-[#E3EBE4] shadow-sm p-5 sm:p-6 sticky top-6">

                                <div class="flex items-start gap-3 mb-6">

                                    <div class="w-12 h-12 rounded-2xl bg-[#EAF5EF] text-[#004D3A] flex items-center justify-center shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="w-6 h-6"
                                             fill="none"
                                             viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="1.8"
                                                  d="M9 12h6m-6 4h6M9 8h6M5 4h14v16H5z"/>
                                        </svg>
                                    </div>

                                    <div>
                                        <h2 class="text-xl font-extrabold text-[#003C2F] leading-tight">
                                            {{ $avaliacao->titulo ?? 'Prova Final' }}
                                        </h2>

                                        <p class="text-xs text-[#60756B] mt-1">
                                            Avaliação final do curso.
                                        </p>
                                    </div>

                                </div>

                                <div class="space-y-3">

                                    <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-4 flex items-center justify-between gap-3">
                                        <span class="text-sm font-bold text-[#60756B]">
                                            Tempo limite
                                        </span>

                                        <span class="text-lg font-extrabold text-[#004D3A]">
                                            {{ $avaliacao->tempo_limite ?? 60 }} min
                                        </span>
                                    </div>

                                    <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-4 flex items-center justify-between gap-3">
                                        <span class="text-sm font-bold text-[#60756B]">
                                            Tentativas
                                        </span>

                                        <span class="text-lg font-extrabold text-[#004D3A]">
                                            {{ $tentativas }}
                                        </span>
                                    </div>

                                    <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl p-4 flex items-center justify-between gap-3">
                                        <span class="text-sm font-bold text-[#60756B]">
                                            Questões
                                        </span>

                                        <span class="text-lg font-extrabold text-[#004D3A]">
                                            {{ $avaliacao->perguntas->count() }}
                                        </span>
                                    </div>

                                </div>

                                <div class="mt-5 bg-green-50 border border-green-100 rounded-2xl p-4 text-green-800 text-xs leading-relaxed">
                                    Leia com atenção. Após finalizar, suas respostas serão enviadas para correção.
                                </div>

                            </div>

                        </aside>

                        <!-- QUESTÕES -->
                        <div class="xl:col-span-8 2xl:col-span-9">

                            <div class="space-y-5">

                                @foreach($avaliacao->perguntas as $index => $pergunta)

                                    <div class="bg-white border border-[#E3EBE4] rounded-3xl shadow-sm overflow-hidden">

                                        <div class="bg-[#F8FBF8] border-b border-[#E3EBE4] px-5 sm:px-6 py-4 flex items-center gap-3">

                                            <div class="w-10 h-10 rounded-full bg-[#004D3A] text-white flex items-center justify-center text-sm font-extrabold">
                                                {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                            </div>

                                            <div>
                                                <p class="text-[11px] uppercase tracking-widest font-extrabold text-[#60756B]">
                                                    Questão de múltipla escolha
                                                </p>

                                                <p class="text-xs text-[#8A9B92] mt-1">
                                                    Selecione uma alternativa.
                                                </p>
                                            </div>

                                        </div>

                                        <div class="p-5 sm:p-6">

                                            <div class="bg-[#F1F6F2] border border-[#DCE7DE] rounded-2xl p-4 mb-5">
                                                <p class="font-extrabold text-[#003C2F] leading-relaxed">
                                                    {{ $pergunta->pergunta }}
                                                </p>
                                            </div>

                                            <div class="space-y-3">

                                                @foreach($pergunta->respostas as $respostaIndex => $resposta)

                                                    @php
                                                        $letra = chr(65 + $respostaIndex);
                                                    @endphp

                                                    <label class="flex items-center gap-3 bg-[#F8FBF8] border border-[#E3EBE4] rounded-2xl px-4 py-3 cursor-pointer hover:bg-[#EAF5EF] transition group">

                                                        <span class="w-10 h-10 rounded-full bg-[#E8EFE9] text-[#004D3A] flex items-center justify-center font-extrabold shrink-0 group-hover:bg-[#DCE7DE]">
                                                            {{ $letra }}
                                                        </span>

                                                        <input type="radio"
                                                               name="respostas[{{ $pergunta->id }}]"
                                                               value="{{ $resposta->id }}"
                                                               required
                                                               class="hidden peer">

                                                        <span class="flex-1 text-sm text-[#003C2F]">
                                                            {{ $resposta->resposta }}
                                                        </span>

                                                        <span class="w-7 h-7 rounded-full border border-[#AFC5B5] flex items-center justify-center peer-checked:bg-[#00A63E] peer-checked:border-[#00A63E] shrink-0">
                                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                                 class="w-4 h-4 text-white hidden peer-checked:block"
                                                                 fill="none"
                                                                 viewBox="0 0 24 24"
                                                                 stroke="currentColor">
                                                                <path stroke-linecap="round"
                                                                      stroke-linejoin="round"
                                                                      stroke-width="2.5"
                                                                      d="m4.5 12.75 6 6 9-13.5"/>
                                                            </svg>
                                                        </span>

                                                    </label>

                                                @endforeach

                                            </div>

                                        </div>

                                    </div>

                                @endforeach

                            </div>

                            <!-- BARRA DE FINALIZAR -->
                            <div class="mt-8 bg-white border border-[#E3EBE4] rounded-3xl shadow-sm p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                                <div>
                                    <p class="font-extrabold text-[#003C2F]">
                                        Pronto para finalizar?
                                    </p>

                                    <p class="text-sm text-[#60756B]">
                                        Revise suas respostas antes de enviar a prova.
                                    </p>
                                </div>

                                <button type="button"
                                        onclick="confirmarEnvioProva()"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-[#004D3A] text-white px-7 py-3 rounded-2xl shadow-lg hover:bg-[#003C2F] transition text-sm font-extrabold">
                                    Finalizar Prova
                                </button>

                            </div>

                        </div>

                    </div>

                </form>

            @endif

        </section>

    </main>

</div>

<!-- MODAL ACESSO TESTE -->
<div id="modalAcessoTesteProva"
     class="fixed inset-0 hidden items-center justify-center z-[90] bg-black/50 backdrop-blur-sm px-4">

    <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl border border-[#E3EBE4] p-6 text-center">

        <div class="w-16 h-16 mx-auto rounded-full bg-yellow-100 text-yellow-700 flex items-center justify-center mb-4">
            <span class="text-2xl">🔐</span>
        </div>

        <h2 class="text-xl font-extrabold text-[#003C2F] mb-2">
            Acesso de teste
        </h2>

        <p class="text-sm text-[#60756B] mb-5">
            Digite a senha de teste para liberar a prova final temporariamente.
        </p>

        <input
            type="password"
            id="senhaTesteProva"
            placeholder="Digite a senha"
            class="w-full px-4 py-3 rounded-2xl border border-[#DCE7DE] bg-[#F8FBF8] text-[#003C2F] text-center font-bold focus:outline-none focus:ring-2 focus:ring-[#00A63E] mb-4"
        >

        <p id="erroSenhaTesteProva" class="hidden text-sm text-red-600 font-bold mb-4">
            Senha incorreta. Tente novamente.
        </p>

        <div class="flex gap-3">
            <button type="button"
                    onclick="fecharAcessoTesteProva()"
                    class="w-1/2 px-4 py-3 rounded-2xl bg-gray-100 text-gray-700 font-bold hover:bg-gray-200 transition">
                Cancelar
            </button>

            <button type="button"
                    onclick="validarAcessoTesteProva()"
                    class="w-1/2 px-4 py-3 rounded-2xl bg-[#004D3A] text-white font-bold hover:bg-[#003C2F] transition">
                Entrar
            </button>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(isset($avaliacao) && $provaLiberada)
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            icon: '{{ $acessoTeste ? 'warning' : 'question' }}',
            title: '{{ $acessoTeste ? 'Acesso de teste ativado' : 'Deseja iniciar a prova final?' }}',
            html: `
                <p style="color:#60756B; font-size:14px; line-height:1.6;">
                    {{ $acessoTeste ? 'Você está acessando a prova usando a senha de teste.' : 'A prova final está liberada porque você atingiu pelo menos 70% do curso atual.' }}
                    <br><br>
                    Você terá <strong>{{ $tentativas }} tentativa(s)</strong> e o tempo limite será de
                    <strong>{{ $avaliacao->tempo_limite ?? 60 }} minutos</strong>.
                </p>
            `,
            showCancelButton: true,
            confirmButtonText: 'Sim, iniciar',
            cancelButtonText: 'Voltar depois',
            confirmButtonColor: '#004D3A',
            cancelButtonColor: '#64748b',
            background: '#ffffff',
            color: '#003C2F'
        }).then((result) => {
            if (!result.isConfirmed) {
                window.location.href = "{{ route('dashboard.aluno') }}";
            }
        });
    });

    function confirmarEnvioProva() {
        const form = document.getElementById('formProvaFinalAluno');

        Swal.fire({
            icon: 'warning',
            title: 'Finalizar prova?',
            text: 'Após enviar, suas respostas serão registradas.',
            showCancelButton: true,
            confirmButtonText: 'Sim, finalizar',
            cancelButtonText: 'Revisar',
            confirmButtonColor: '#004D3A',
            cancelButtonColor: '#64748b'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }
</script>
@endif

<script>
    function abrirAcessoTesteProva() {
        const modal = document.getElementById('modalAcessoTesteProva');
        const input = document.getElementById('senhaTesteProva');
        const erro = document.getElementById('erroSenhaTesteProva');

        if (!modal) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        if (input) {
            input.value = '';
            setTimeout(() => input.focus(), 150);
        }

        if (erro) {
            erro.classList.add('hidden');
        }
    }

    function fecharAcessoTesteProva() {
        const modal = document.getElementById('modalAcessoTesteProva');

        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function validarAcessoTesteProva() {
        const input = document.getElementById('senhaTesteProva');
        const erro = document.getElementById('erroSenhaTesteProva');

        const senha = input ? input.value.trim() : '';

        if (senha === '123') {
            window.location.href = "{{ route('prova.final') }}?teste=123";
            return;
        }

        if (erro) {
            erro.classList.remove('hidden');
        }

        if (input) {
            input.value = '';
            input.focus();
        }
    }

    const modalAcessoTesteProva = document.getElementById('modalAcessoTesteProva');

    if (modalAcessoTesteProva) {
        modalAcessoTesteProva.addEventListener('click', function(e) {
            if (e.target === this) {
                fecharAcessoTesteProva();
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            fecharAcessoTesteProva();
        }

        if (e.key === 'Enter') {
            const modal = document.getElementById('modalAcessoTesteProva');

            if (modal && !modal.classList.contains('hidden')) {
                validarAcessoTesteProva();
            }
        }
    });
</script>

@endsection