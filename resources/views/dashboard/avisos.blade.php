@extends('layout.app')

@section('title', 'Avisos')

@section('content')

@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;
    use App\Models\User;
    use Carbon\Carbon;

    /*
    |--------------------------------------------------------------------------
    | ABAS DA SIDEBAR
    |--------------------------------------------------------------------------
    | A sidebar abre:
    | /avisos?aba=criar  => Criar aviso + histórico de avisos enviados
    | /avisos?aba=meus   => Meus avisos do sistema/notificações da navbar
    */
    $abaAtualAvisos = request('aba', 'meus');

    $avisosHistorico = collect($avisos ?? [])
        ->sortByDesc(function ($aviso) {
            return (int) ($aviso->favorito ?? 0);
        })
        ->values();

    $totalFavoritos = $avisosHistorico->filter(function ($aviso) {
        return (bool) ($aviso->favorito ?? false);
    })->count();

    /*
    |--------------------------------------------------------------------------
    | MEUS AVISOS DO SISTEMA
    |--------------------------------------------------------------------------
    | Estes são os avisos automáticos do professor, os mesmos tipos de aviso
    | que fazem sentido aparecer na navbar: aluno pendente, aluno parado,
    | baixo progresso, pós-teste pendente, média baixa e curso concluído.
    */
    $alertasSistemaProfessor = collect();

    $tiposAluno = ['residente', 'preceptor'];

    $totalAulasSistema = Schema::hasTable('aulas')
        ? DB::table('aulas')->count()
        : 0;

    $totalAvaliacoesSistema = Schema::hasTable('avaliacoes')
        ? DB::table('avaliacoes')
            ->where(function ($query) {
                $query->where('tipo', 'normal')
                    ->orWhere('tipo', 'pos_teste')
                    ->orWhere('tipo', 'pós-teste')
                    ->orWhereNull('tipo');
            })
            ->count()
        : 0;

    $usuariosPendentes = Schema::hasTable('users')
        ? User::whereIn('tipo', $tiposAluno)
            ->where('status', 'pendente')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get()
        : collect();

    foreach ($usuariosPendentes as $usuarioPendente) {
        $alertasSistemaProfessor->push((object) [
            'tipo' => 'usuario_pendente',
            'titulo' => 'Usuário aguardando aprovação',
            'mensagem' => $usuarioPendente->name . ' realizou cadastro e precisa ser aprovado para acessar o sistema.',
            'detalhe' => $usuarioPendente->email,
            'categoria' => 'urgente',
            'icone' => '👤',
            'rota' => route('controle.usuarios'),
            'botao' => 'Ver usuários',
            'data' => $usuarioPendente->created_at,
        ]);
    }

    $alunosSistema = Schema::hasTable('users')
        ? User::whereIn('tipo', $tiposAluno)
            ->where('status', 'aprovado')
            ->where('tipo', '!=', 'super_admin')
            ->orderBy('name')
            ->get()
        : collect();

    foreach ($alunosSistema as $alunoSistema) {
        $aulasAssistidasAluno = 0;
        $avaliacoesFeitasAluno = 0;
        $mediaAluno = 0;
        $ultimaAtividadeAluno = null;

        if (Schema::hasTable('aulas_assistidas')) {
            $aulasAssistidasAluno = DB::table('aulas_assistidas')
                ->where('aluno_id', $alunoSistema->id)
                ->where('assistido', true)
                ->distinct('aula_id')
                ->count('aula_id');

            $ultimaAtividadeAluno = DB::table('aulas_assistidas')
                ->where('aluno_id', $alunoSistema->id)
                ->max('updated_at');
        }

        if (Schema::hasTable('notas')) {
            $avaliacoesFeitasAluno = DB::table('notas')
                ->where('aluno_id', $alunoSistema->id)
                ->distinct('avaliacao_id')
                ->count('avaliacao_id');

            $mediaAluno = DB::table('notas')
                ->where('aluno_id', $alunoSistema->id)
                ->avg('nota') ?? 0;

            $ultimaNota = DB::table('notas')
                ->where('aluno_id', $alunoSistema->id)
                ->max('created_at');

            if (!$ultimaAtividadeAluno || ($ultimaNota && Carbon::parse($ultimaNota)->gt(Carbon::parse($ultimaAtividadeAluno)))) {
                $ultimaAtividadeAluno = $ultimaNota;
            }
        }

        $progressoAluno = $totalAulasSistema > 0
            ? (int) round(($aulasAssistidasAluno / $totalAulasSistema) * 100)
            : 0;

        $postestesPendentesAluno = max($totalAvaliacoesSistema - $avaliacoesFeitasAluno, 0);

        $diasSemProgressoAluno = null;

        if ($ultimaAtividadeAluno) {
            try {
                $diasSemProgressoAluno = (int) floor(
                    Carbon::parse($ultimaAtividadeAluno)
                        ->timezone('America/Sao_Paulo')
                        ->startOfDay()
                        ->diffInDays(now()->timezone('America/Sao_Paulo')->startOfDay())
                );
            } catch (\Throwable $e) {
                $diasSemProgressoAluno = null;
            }
        }

        if ($totalAulasSistema > 0 && $progressoAluno >= 100) {
            $alertasSistemaProfessor->push((object) [
                'tipo' => 'curso_concluido',
                'titulo' => 'Aluno concluiu todas as aulas',
                'mensagem' => $alunoSistema->name . ' concluiu 100% das videoaulas cadastradas.',
                'detalhe' => $aulasAssistidasAluno . ' de ' . $totalAulasSistema . ' aula(s) concluída(s).',
                'categoria' => 'sucesso',
                'icone' => '🏆',
                'rota' => route('acompanhamento.residentes'),
                'botao' => 'Ver acompanhamento',
                'data' => $ultimaAtividadeAluno,
            ]);
        } elseif ($totalAulasSistema > 0 && $progressoAluno >= 70) {
            $alertasSistemaProfessor->push((object) [
                'tipo' => 'quase_certificado',
                'titulo' => 'Aluno próximo do certificado',
                'mensagem' => $alunoSistema->name . ' já atingiu ' . $progressoAluno . '% de progresso no curso.',
                'detalhe' => $aulasAssistidasAluno . ' de ' . $totalAulasSistema . ' aula(s) concluída(s).',
                'categoria' => 'sucesso',
                'icone' => '🎓',
                'rota' => route('acompanhamento.residentes'),
                'botao' => 'Ver acompanhamento',
                'data' => $ultimaAtividadeAluno,
            ]);
        }

        if ($totalAulasSistema > 0 && $progressoAluno < 50) {
            $alertasSistemaProfessor->push((object) [
                'tipo' => 'baixo_progresso',
                'titulo' => 'Baixo progresso no curso',
                'mensagem' => $alunoSistema->name . ' está com apenas ' . $progressoAluno . '% de progresso.',
                'detalhe' => $aulasAssistidasAluno . ' de ' . $totalAulasSistema . ' aula(s) concluída(s).',
                'categoria' => 'atencao',
                'icone' => '📉',
                'rota' => route('acompanhamento.residentes'),
                'botao' => 'Ver acompanhamento',
                'data' => $ultimaAtividadeAluno,
            ]);
        }

        if ($postestesPendentesAluno > 0) {
            $alertasSistemaProfessor->push((object) [
                'tipo' => 'posteste_pendente',
                'titulo' => 'Pós-teste pendente',
                'mensagem' => $alunoSistema->name . ' possui ' . $postestesPendentesAluno . ' pós-teste(s) pendente(s).',
                'detalhe' => 'Pós-testes feitos: ' . $avaliacoesFeitasAluno . ' de ' . $totalAvaliacoesSistema . '.',
                'categoria' => 'info',
                'icone' => '📝',
                'rota' => route('acompanhamento.residentes'),
                'botao' => 'Ver acompanhamento',
                'data' => $ultimaAtividadeAluno,
            ]);
        }

        if ($avaliacoesFeitasAluno > 0 && $mediaAluno < 7) {
            $alertasSistemaProfessor->push((object) [
                'tipo' => 'media_baixa',
                'titulo' => 'Média abaixo do esperado',
                'mensagem' => $alunoSistema->name . ' está com média ' . number_format($mediaAluno, 1, ',', '.') . '.',
                'detalhe' => 'Pode precisar de acompanhamento do professor.',
                'categoria' => 'urgente',
                'icone' => '⚠️',
                'rota' => route('acompanhamento.residentes'),
                'botao' => 'Ver acompanhamento',
                'data' => $ultimaAtividadeAluno,
            ]);
        }

        if ($diasSemProgressoAluno !== null && $diasSemProgressoAluno >= 7) {
            $alertasSistemaProfessor->push((object) [
                'tipo' => 'sem_progresso',
                'titulo' => 'Aluno sem progresso há alguns dias',
                'mensagem' => $alunoSistema->name . ' está há ' . $diasSemProgressoAluno . ' dia(s) sem progresso registrado.',
                'detalhe' => 'Última atividade: ' . Carbon::parse($ultimaAtividadeAluno)->timezone('America/Sao_Paulo')->format('d/m/Y H:i') . '.',
                'categoria' => 'atencao',
                'icone' => '⏱️',
                'rota' => route('acompanhamento.residentes'),
                'botao' => 'Ver acompanhamento',
                'data' => $ultimaAtividadeAluno,
            ]);
        }
    }

    function alertaProfessorAindaDentroDoPrazo($data, $dias = 7) {
        if (empty($data)) {
            return true;
        }

        try {
            return Carbon::parse($data)
                ->timezone('America/Sao_Paulo')
                ->greaterThanOrEqualTo(now()->timezone('America/Sao_Paulo')->subDays($dias));
        } catch (\Throwable $e) {
            return true;
        }
    }

    $alertasSistemaProfessor = $alertasSistemaProfessor
        ->filter(function ($alerta) {
            return alertaProfessorAindaDentroDoPrazo($alerta->data ?? null, 7);
        })
        ->values();

    $alertasSistemaProfessor = $alertasSistemaProfessor
        ->sortByDesc(function ($alerta) {
            $peso = match ($alerta->categoria) {
                'urgente' => 4,
                'atencao' => 3,
                'info' => 2,
                'sucesso' => 1,
                default => 0,
            };

            $data = $alerta->data ? Carbon::parse($alerta->data)->timestamp : 0;

            return ($peso * 10000000000) + $data;
        })
        ->values();

    function classeAlertaProfessor($categoria) {
        return match ($categoria) {
            'urgente' => [
                'card' => 'border-red-200 bg-red-50',
                'lateral' => 'border-l-red-500',
                'badge' => 'bg-red-100 text-red-700',
                'icone' => 'bg-red-100 text-red-700',
            ],
            'atencao' => [
                'card' => 'border-yellow-200 bg-yellow-50',
                'lateral' => 'border-l-yellow-400',
                'badge' => 'bg-yellow-100 text-yellow-700',
                'icone' => 'bg-yellow-100 text-yellow-700',
            ],
            'sucesso' => [
                'card' => 'border-green-200 bg-green-50',
                'lateral' => 'border-l-[#00A63E]',
                'badge' => 'bg-green-100 text-green-700',
                'icone' => 'bg-green-100 text-green-700',
            ],
            default => [
                'card' => 'border-blue-200 bg-blue-50',
                'lateral' => 'border-l-blue-500',
                'badge' => 'bg-blue-100 text-blue-700',
                'icone' => 'bg-blue-100 text-blue-700',
            ],
        };
    }

    function labelAlertaProfessor($categoria) {
        return match ($categoria) {
            'urgente' => 'Urgente',
            'atencao' => 'Atenção',
            'sucesso' => 'Conquista',
            default => 'Info',
        };
    }
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

    @include('partials.sidebar-professor')

    <main class="flex-1 min-w-0 w-full bg-[#F3F7F3] overflow-x-hidden">

        @include('partials.navbar')

        <section class="p-4 sm:p-6 lg:p-8">

            <!-- CABEÇALHO -->
            <div class="mb-7 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-5">

                <div>
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-[#003C2F] tracking-tight">
                        {{ $abaAtualAvisos === 'criar' ? 'Criar aviso' : 'Meus avisos' }}
                    </h1>

                    <p class="text-sm text-[#60756B] mt-2 max-w-2xl">
                        @if($abaAtualAvisos === 'criar')
                            Crie avisos para os alunos e reutilize mensagens que já foram enviadas anteriormente.
                        @else
                            Aqui aparecem os avisos automáticos do sistema sobre alunos, usuários, progresso, pós-testes e certificados. Eles ficam disponíveis por até 7 dias.
                        @endif
                    </p>
                </div>

                <div class="bg-white border border-[#E3EBE4] rounded-3xl px-5 py-4 shadow-sm">
                    <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                        {{ $abaAtualAvisos === 'criar' ? 'Histórico de avisos' : 'Avisos do sistema' }}
                    </p>

                    <p class="text-2xl font-extrabold text-[#004D3A] mt-1">
                        {{ $abaAtualAvisos === 'criar' ? $avisosHistorico->count() : $alertasSistemaProfessor->count() }}
                    </p>
                </div>

            </div>

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

            @if($errors->any())
                <div class="mb-5 bg-red-100 text-red-700 px-4 py-3 rounded-2xl border border-red-200 shadow-sm">
                    <p class="font-extrabold mb-2">Corrija os campos abaixo:</p>
                    <ul class="list-disc pl-5 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($abaAtualAvisos === 'criar')

                <div class="grid grid-cols-1 xl:grid-cols-12 gap-7">

                    <!-- FORMULÁRIO DE CRIAR AVISO -->
                    <div class="xl:col-span-5">
                        <div class="bg-white border border-[#E3EBE4] rounded-3xl shadow-sm p-5 sm:p-6">

                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-12 h-12 rounded-2xl bg-[#EAF5EF] text-[#004D3A] flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="w-6 h-6"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="1.8"
                                              d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H5.25A2.25 2.25 0 0 1 3 13.5v-6A2.25 2.25 0 0 1 5.25 5.25h3c.704 0 1.402-.03 2.09-.09m0 10.68c1.08.094 2.102.31 3.04.63 1.38.47 2.62 1.17 3.67 2.03.517.424 1.28.067 1.28-.602V3.102c0-.669-.763-1.026-1.28-.602a14.8 14.8 0 0 1-3.67 2.03c-.938.32-1.96.536-3.04.63m0 10.68V5.16"/>
                                    </svg>
                                </div>

                                <div>
                                    <h2 class="text-xl font-extrabold text-[#003C2F] leading-tight">
                                        Criar novo aviso
                                    </h2>

                                    <p class="text-xs text-[#60756B] mt-1">
                                        Esse aviso será exibido para os alunos.
                                    </p>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('avisos.store') }}" id="formCriarAviso">
                                @csrf

                                <div class="space-y-5">

                                    <div>
                                        <label class="block text-[11px] uppercase tracking-widest font-extrabold text-[#60756B] mb-2">
                                            Título
                                        </label>

                                        <input type="text"
                                               name="titulo"
                                               id="tituloAviso"
                                               value="{{ old('titulo') }}"
                                               placeholder="Ex: Novo módulo disponível"
                                               class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-bold placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition"
                                               required>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] uppercase tracking-widest font-extrabold text-[#60756B] mb-2">
                                            Categoria
                                        </label>

                                        <select name="categoria"
                                                id="categoriaAviso"
                                                class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-bold focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition"
                                                required>
                                            <option value="urgente" {{ old('categoria') === 'urgente' ? 'selected' : '' }}>
                                                Urgente
                                            </option>

                                            <option value="importante" {{ old('categoria', 'importante') === 'importante' ? 'selected' : '' }}>
                                                Importante
                                            </option>
                                        </select>

                                        <p class="text-xs text-[#60756B] mt-2">
                                            Aviso urgente aparece primeiro e abre em popup na dashboard do aluno.
                                        </p>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] uppercase tracking-widest font-extrabold text-[#60756B] mb-2">
                                            Tempo visível na dashboard do aluno
                                        </label>

                                        <div class="grid grid-cols-2 gap-3">
                                            <input type="number"
                                                   name="tempo_exibicao"
                                                   id="tempoExibicaoAviso"
                                                   min="1"
                                                   value="{{ old('tempo_exibicao', 24) }}"
                                                   class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-bold focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition"
                                                   required>

                                            <select name="unidade_tempo"
                                                    id="unidadeTempoAviso"
                                                    class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-bold focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition"
                                                    required>
                                                <option value="minutos" {{ old('unidade_tempo') === 'minutos' ? 'selected' : '' }}>
                                                    Minutos
                                                </option>

                                                <option value="horas" {{ old('unidade_tempo', 'horas') === 'horas' ? 'selected' : '' }}>
                                                    Horas
                                                </option>

                                                <option value="dias" {{ old('unidade_tempo') === 'dias' ? 'selected' : '' }}>
                                                    Dias
                                                </option>
                                            </select>
                                        </div>

                                        <p class="text-xs text-[#60756B] mt-2">
                                            Depois desse prazo, o aviso não aparecerá mais para o aluno.
                                        </p>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] uppercase tracking-widest font-extrabold text-[#60756B] mb-2">
                                            Mensagem
                                        </label>

                                        <textarea name="mensagem"
                                                  id="mensagemAviso"
                                                  rows="6"
                                                  placeholder="Digite a mensagem do aviso..."
                                                  class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-medium placeholder-[#8A9B92] focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition resize-none"
                                                  required>{{ old('mensagem') }}</textarea>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="publicar_agora" class="sr-only peer" checked>
                                            <div class="w-11 h-6 bg-gray-200 rounded-full peer
                                                peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#00A63E]
                                                peer-checked:bg-[#00A63E]
                                                after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                                after:bg-white after:border after:border-gray-300 after:rounded-full
                                                after:h-5 after:w-5 after:transition-all
                                                peer-checked:after:translate-x-full peer-checked:after:border-white">
                                            </div>
                                        </label>

                                        <span class="text-sm font-bold text-[#003C2F]">
                                            Publicar agora
                                        </span>
                                    </div>

                                    <label class="flex items-start gap-3 bg-[#F8FBF8] border border-[#DCE7DE] rounded-2xl px-4 py-3 cursor-pointer hover:bg-[#EAF5EF] transition">
                                        <input type="checkbox"
                                               name="favorito"
                                               id="favoritoAviso"
                                               value="1"
                                               class="mt-1 w-4 h-4 accent-[#004D3A]">

                                        <span>
                                            <span class="block text-sm font-extrabold text-[#003C2F]">
                                                Marcar como favorito
                                            </span>
                                            <span class="block text-xs text-[#60756B] mt-1">
                                                Favoritos aparecem no topo do histórico para reutilizar mais rápido.
                                            </span>
                                        </span>
                                    </label>

                                    <button type="submit"
                                            class="w-full bg-[#004D3A] hover:bg-[#003C2F] text-white px-6 py-4 rounded-2xl font-extrabold transition shadow-lg flex items-center justify-center gap-2">
                                        Salvar aviso
                                    </button>

                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- HISTÓRICO PARA REUTILIZAR -->
                    <div class="xl:col-span-7">
                        <div class="bg-white border border-[#E3EBE4] rounded-3xl shadow-sm overflow-hidden">
                            <div class="p-5 sm:p-6 border-b border-[#E3EBE4] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                        Histórico de avisos enviados
                                    </p>

                                    <h2 class="text-xl font-extrabold text-[#003C2F] mt-1">
                                        Reutilizar aviso pronto
                                    </h2>

                                    <p class="text-xs text-[#60756B] mt-1">
                                        Aqui ficam os avisos já enviados para os alunos. Use para criar outro aviso mais rápido.
                                    </p>
                                </div>

                                <span class="shrink-0 bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-[11px] font-extrabold">
                                    {{ $totalFavoritos }} favorito(s)
                                </span>
                            </div>

                            <div class="p-4 sm:p-6 space-y-4 max-h-[760px] overflow-y-auto">
                                @forelse($avisosHistorico as $aviso)
                                    @php
                                        $categoria = strtolower($aviso->categoria ?? $aviso->tipo ?? 'importante');
                                        $urgente = $categoria === 'urgente';
                                        $expirado = isset($aviso->expires_at) && $aviso->expires_at && Carbon::parse($aviso->expires_at)->isPast();
                                        $mensagemAviso = $aviso->mensagem ?? $aviso->descricao ?? '';
                                        $avisoFavorito = (bool) ($aviso->favorito ?? false);
                                    @endphp

                                    <div class="bg-[#F8FBF8] border rounded-3xl p-5 hover:shadow-md transition
                                        {{ $avisoFavorito ? 'border-yellow-200 ring-1 ring-yellow-100' : 'border-[#E3EBE4]' }}">

                                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                                            <div class="min-w-0 flex-1">
                                                <div class="flex flex-wrap items-center gap-2 mb-3">
                                                    @if($avisoFavorito)
                                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-extrabold bg-yellow-100 text-yellow-700">
                                                            ★ FAVORITO
                                                        </span>
                                                    @endif

                                                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] font-extrabold
                                                        {{ $urgente ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                                        {{ $urgente ? 'URGENTE' : 'IMPORTANTE' }}
                                                    </span>

                                                    @if($expirado)
                                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-extrabold bg-gray-200 text-gray-600">
                                                            EXPIRADO
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-extrabold bg-blue-50 text-blue-700">
                                                            ATIVO
                                                        </span>
                                                    @endif

                                                    <span class="text-xs text-[#8A9B92] font-semibold">
                                                        Criado em {{ Carbon::parse($aviso->created_at)->timezone('America/Sao_Paulo')->format('d/m/Y H:i') }}
                                                    </span>
                                                </div>

                                                <h3 class="text-lg font-extrabold text-[#003C2F] leading-tight break-words">
                                                    {{ $aviso->titulo }}
                                                </h3>

                                                <p class="text-sm text-[#60756B] mt-2 leading-relaxed break-words">
                                                    {{ $mensagemAviso }}
                                                </p>
                                            </div>

                                            <div class="flex items-center gap-2 shrink-0">
                                                <form method="POST" action="{{ route('avisos.toggle-favorito', $aviso->id) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                            class="w-10 h-10 rounded-xl border transition flex items-center justify-center
                                                                {{ $avisoFavorito ? 'bg-yellow-100 border-yellow-200 text-yellow-600' : 'bg-white border-[#DCE7DE] text-[#8A9B92] hover:bg-yellow-50 hover:text-yellow-600' }}"
                                                            title="{{ $avisoFavorito ? 'Remover dos favoritos' : 'Marcar como favorito' }}">
                                                        ★
                                                    </button>
                                                </form>

                                                <button type="button"
                                                        onclick='usarAvisoDoHistorico(
                                                            @json($aviso->titulo),
                                                            @json($mensagemAviso),
                                                            @json($categoria),
                                                            @json($avisoFavorito)
                                                        )'
                                                        class="px-4 h-10 rounded-xl bg-[#004D3A] text-white text-xs font-extrabold hover:bg-[#003C2F] transition"
                                                        title="Reutilizar aviso">
                                                    Usar
                                                </button>

                                                <button type="button"
                                                        onclick='abrirModalEditarAviso(
                                                            @json($aviso->id),
                                                            @json($aviso->titulo),
                                                            @json($mensagemAviso),
                                                            @json($categoria)
                                                        )'
                                                        class="w-10 h-10 rounded-xl bg-white border border-[#DCE7DE] hover:bg-[#EAF5EF] text-[#004D3A] transition flex items-center justify-center"
                                                        title="Editar aviso">
                                                    ✎
                                                </button>

                                                <button type="button"
                                                        onclick="abrirModalExcluirAviso({{ $aviso->id }})"
                                                        class="w-10 h-10 rounded-xl bg-white border border-red-100 hover:bg-red-50 text-red-600 transition flex items-center justify-center"
                                                        title="Excluir aviso">
                                                    🗑
                                                </button>

                                                <form id="formExcluirAviso{{ $aviso->id }}"
                                                      method="POST"
                                                      action="{{ route('avisos.destroy', $aviso->id) }}"
                                                      class="hidden">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="bg-[#F8FBF8] border border-[#E3EBE4] rounded-3xl p-10 text-center">
                                        <h3 class="text-xl font-extrabold text-[#003C2F]">
                                            Nenhum aviso enviado ainda
                                        </h3>

                                        <p class="text-sm text-[#60756B] mt-2">
                                            Depois que você criar avisos, eles aparecerão aqui para reutilizar.
                                        </p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                </div>

            @else

                <!-- MEUS AVISOS DO SISTEMA -->
                <div class="bg-white border border-[#E3EBE4] rounded-3xl shadow-sm overflow-hidden">
                    <div class="p-5 sm:p-6 border-b border-[#E3EBE4] flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div>
                            <p class="text-[11px] uppercase tracking-widest text-[#60756B] font-extrabold">
                                Avisos automáticos
                            </p>

                            <h2 class="text-xl font-extrabold text-[#003C2F] mt-1">
                                Meus avisos do sistema
                            </h2>

                            <p class="text-xs text-[#60756B] mt-1">
                                Informações importantes sobre alunos, progresso, pós-testes, aprovações e certificados. Alertas antigos somem automaticamente depois de 7 dias.
                            </p>
                        </div>

                        <a href="{{ route('acompanhamento.residentes') }}"
                           class="inline-flex items-center justify-center bg-[#004D3A] text-white px-5 py-3 rounded-2xl text-sm font-extrabold hover:bg-[#003C2F] transition">
                            Abrir acompanhamento
                        </a>
                    </div>

                    <div class="p-4 sm:p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            @forelse($alertasSistemaProfessor as $alerta)
                                @php
                                    $classe = classeAlertaProfessor($alerta->categoria);
                                @endphp

                                <div class="border {{ $classe['card'] }} {{ $classe['lateral'] }} border-l-4 rounded-3xl p-5 hover:shadow-md transition">
                                    <div class="flex items-start gap-4">
                                        <div class="w-12 h-12 rounded-2xl {{ $classe['icone'] }} flex items-center justify-center text-xl shrink-0">
                                            {{ $alerta->icone }}
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                                <h3 class="font-extrabold text-[#003C2F] break-words">
                                                    {{ $alerta->titulo }}
                                                </h3>

                                                <span class="px-3 py-1 rounded-full text-[11px] font-extrabold {{ $classe['badge'] }}">
                                                    {{ labelAlertaProfessor($alerta->categoria) }}
                                                </span>
                                            </div>

                                            <p class="text-sm text-[#60756B] leading-relaxed break-words">
                                                {{ $alerta->mensagem }}
                                            </p>

                                            @if(!empty($alerta->detalhe))
                                                <p class="text-xs text-[#3F5D51] mt-3 font-bold break-words">
                                                    {{ $alerta->detalhe }}
                                                </p>
                                            @endif

                                            <a href="{{ $alerta->rota }}"
                                               class="inline-flex mt-4 bg-[#004D3A] text-white px-4 py-2.5 rounded-xl text-xs font-extrabold hover:bg-[#003C2F] transition">
                                                {{ $alerta->botao }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="lg:col-span-2 bg-[#F8FBF8] border border-[#E3EBE4] rounded-3xl p-10 text-center">
                                    <div class="w-16 h-16 mx-auto rounded-full bg-[#EAF5EF] text-[#004D3A] flex items-center justify-center text-2xl mb-4">
                                        ✅
                                    </div>

                                    <h3 class="text-xl font-extrabold text-[#003C2F]">
                                        Nenhum aviso importante no momento
                                    </h3>

                                    <p class="text-sm text-[#60756B] mt-2">
                                        Quando houver alunos pendentes, baixo progresso, pós-testes pendentes ou conclusão de curso, aparecerá aqui.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            @endif

        </section>

    </main>

</div>

<!-- MODAL EDITAR AVISO -->
<div id="modalEditarAviso"
     class="fixed inset-0 hidden items-center justify-center bg-black/50 backdrop-blur-sm z-[80] px-4">

    <div class="bg-white w-full max-w-lg p-6 rounded-3xl border border-[#E3EBE4] shadow-2xl">

        <div class="flex items-start justify-between mb-6">
            <div>
                <h2 class="text-2xl font-extrabold text-[#003C2F]">
                    Editar aviso
                </h2>

                <p class="text-sm text-[#60756B] mt-1">
                    Atualize as informações e redefina o tempo de exibição.
                </p>
            </div>

            <button type="button"
                    onclick="fecharModalEditarAviso()"
                    class="bg-[#F1F6F2] hover:bg-[#E6EFE8] text-[#003C2F] p-2 rounded-xl transition">
                ✕
            </button>
        </div>

        <form id="formEditarAviso" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-5">
                <div>
                    <label class="block text-[11px] uppercase tracking-widest font-extrabold text-[#60756B] mb-2">
                        Título
                    </label>

                    <input type="text"
                           name="titulo"
                           id="editarTituloAviso"
                           class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-bold focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition"
                           required>
                </div>

                <div>
                    <label class="block text-[11px] uppercase tracking-widest font-extrabold text-[#60756B] mb-2">
                        Categoria
                    </label>

                    <select name="categoria"
                            id="editarCategoriaAviso"
                            class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-bold focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition"
                            required>
                        <option value="urgente">Urgente</option>
                        <option value="importante">Importante</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] uppercase tracking-widest font-extrabold text-[#60756B] mb-2">
                        Novo tempo de exibição
                    </label>

                    <div class="grid grid-cols-2 gap-3">
                        <input type="number"
                               name="tempo_exibicao"
                               min="1"
                               value="24"
                               class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-bold focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition">

                        <select name="unidade_tempo"
                                class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-bold focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition">
                            <option value="minutos">Minutos</option>
                            <option value="horas" selected>Horas</option>
                            <option value="dias">Dias</option>
                        </select>
                    </div>

                    <p class="text-xs text-[#60756B] mt-2">
                        Ao salvar, o tempo será renovado a partir de agora.
                    </p>
                </div>

                <div>
                    <label class="block text-[11px] uppercase tracking-widest font-extrabold text-[#60756B] mb-2">
                        Mensagem
                    </label>

                    <textarea name="mensagem"
                              id="editarMensagemAviso"
                              rows="5"
                              class="w-full px-4 py-3 rounded-2xl bg-[#F8FBF8] border border-[#DCE7DE] text-[#003C2F] text-sm font-medium focus:outline-none focus:ring-2 focus:ring-[#00A63E] focus:border-transparent transition resize-none"
                              required></textarea>
                </div>

                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="publicar_agora" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer
                            peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#00A63E]
                            peer-checked:bg-[#00A63E]
                            after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                            after:bg-white after:border after:border-gray-300 after:rounded-full
                            after:h-5 after:w-5 after:transition-all
                            peer-checked:after:translate-x-full peer-checked:after:border-white">
                        </div>
                    </label>

                    <span class="text-sm font-bold text-[#003C2F]">
                        Publicar agora
                    </span>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-3 mt-7">
                <button type="button"
                        onclick="fecharModalEditarAviso()"
                        class="px-5 py-3 rounded-2xl bg-[#F1F6F2] text-[#60756B] font-bold hover:bg-[#E6EFE8] transition">
                    Cancelar
                </button>

                <button type="submit"
                        class="px-5 py-3 rounded-2xl bg-[#004D3A] text-white font-bold hover:bg-[#003C2F] transition shadow-sm">
                    Salvar alterações
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EXCLUIR AVISO -->
<div id="modalExcluirAviso"
     class="fixed inset-0 hidden items-center justify-center bg-black/50 backdrop-blur-sm z-[85] px-4">

    <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl p-6 text-center border border-[#E3EBE4]">
        <div class="w-16 h-16 mx-auto rounded-full bg-red-100 flex items-center justify-center mb-4">
            <span class="text-2xl text-red-600">!</span>
        </div>

        <h2 class="text-xl font-extrabold text-[#003C2F] mb-2">
            Excluir aviso?
        </h2>

        <p class="text-sm text-[#60756B] mb-6">
            Essa ação não poderá ser desfeita. O aviso será removido da plataforma.
        </p>

        <input type="hidden" id="idAvisoExcluir">

        <div class="flex gap-3">
            <button type="button"
                    onclick="fecharModalExcluirAviso()"
                    class="w-1/2 px-4 py-3 rounded-2xl bg-gray-100 text-gray-700 font-bold hover:bg-gray-200 transition">
                Cancelar
            </button>

            <button type="button"
                    onclick="confirmarExcluirAviso()"
                    class="w-1/2 px-4 py-3 rounded-2xl bg-red-600 text-white font-bold hover:bg-red-700 transition">
                Excluir
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function usarAvisoDoHistorico(titulo, mensagem, categoria, favorito) {
        const tituloInput = document.getElementById('tituloAviso');
        const mensagemInput = document.getElementById('mensagemAviso');
        const categoriaInput = document.getElementById('categoriaAviso');
        const tempoInput = document.getElementById('tempoExibicaoAviso');
        const unidadeInput = document.getElementById('unidadeTempoAviso');
        const favoritoInput = document.getElementById('favoritoAviso');
        const form = document.getElementById('formCriarAviso');

        if (tituloInput) tituloInput.value = titulo ?? '';
        if (mensagemInput) mensagemInput.value = mensagem ?? '';

        if (categoriaInput) {
            const categoriaFinal = categoria === 'informativo' ? 'importante' : (categoria ?? 'importante');
            categoriaInput.value = categoriaFinal;
        }

        if (tempoInput && !tempoInput.value) tempoInput.value = 24;
        if (unidadeInput && !unidadeInput.value) unidadeInput.value = 'horas';
        if (favoritoInput) favoritoInput.checked = !!favorito;

        if (form) {
            form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Aviso carregado!',
                text: 'O aviso do histórico foi colocado no formulário. Agora é só revisar e salvar.',
                confirmButtonColor: '#004D3A'
            });
        }
    }

    function abrirModalEditarAviso(id, titulo, mensagem, categoria) {
        const modal = document.getElementById('modalEditarAviso');
        const form = document.getElementById('formEditarAviso');

        if (!modal || !form) return;

        document.getElementById('editarTituloAviso').value = titulo ?? '';
        document.getElementById('editarMensagemAviso').value = mensagem ?? '';

        const categoriaFinal = categoria === 'informativo' ? 'importante' : (categoria ?? 'importante');
        document.getElementById('editarCategoriaAviso').value = categoriaFinal;

        form.action = "/avisos/" + id;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function fecharModalEditarAviso() {
        const modal = document.getElementById('modalEditarAviso');

        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function abrirModalExcluirAviso(id) {
        const modal = document.getElementById('modalExcluirAviso');
        const input = document.getElementById('idAvisoExcluir');

        if (!modal || !input) return;

        input.value = id;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function fecharModalExcluirAviso() {
        const modal = document.getElementById('modalExcluirAviso');

        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function confirmarExcluirAviso() {
        const id = document.getElementById('idAvisoExcluir')?.value;
        const form = document.getElementById('formExcluirAviso' + id);

        if (form) {
            form.submit();
        }
    }

    const modalEditarAviso = document.getElementById('modalEditarAviso');
    const modalExcluirAviso = document.getElementById('modalExcluirAviso');

    if (modalEditarAviso) {
        modalEditarAviso.addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalEditarAviso();
            }
        });
    }

    if (modalExcluirAviso) {
        modalExcluirAviso.addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalExcluirAviso();
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            fecharModalEditarAviso();
            fecharModalExcluirAviso();
        }
    });
</script>

@endsection
