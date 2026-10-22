<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf;

class CertificadoController extends Controller
{
    // =========================
    // TELA DO CERTIFICADO DO ALUNO
    // =========================
    public function aluno()
    {
        // A tela pode informar os requisitos pendentes; o PDF é protegido no servidor.
        return view('aluno.certificado');
    }

    // =========================
    // VERIFICAR OS REQUISITOS DA CERTIFICAÇÃO GERAL
    // =========================
    public static function verificarRequisitos($alunoId): array
    {
        $resultado = [
            'liberado' => false,
            'total_cursos' => 0,
            'cursos_concluidos' => 0,
            'total_aulas' => 0,
            'aulas_assistidas' => 0,
            'total_pos_testes' => 0,
            'pos_testes_feitos' => 0,
            'media_pos_testes' => 0.0,
            'prova_final_feita' => false,
            'nota_final' => null,
            'requisitos' => [
                'aulas' => false,
                'pos_testes' => false,
                'cursos' => false,
                'prova_final' => false,
                'aprovacao_final' => false,
            ],
            'mensagem' => 'Os requisitos de certificação ainda não foram atendidos.',
        ];

        foreach (['cursos', 'modulos', 'aulas', 'aulas_assistidas', 'avaliacoes', 'notas'] as $tabela) {
            if (!Schema::hasTable($tabela)) {
                $resultado['mensagem'] = 'Não foi possível validar a certificação: falta a tabela ' . $tabela . '.';
                return $resultado;
            }
        }

        // Até a migration do campo obrigatorio ser aplicada, todos os cursos contam.
        $queryCursos = DB::table('cursos');
        if (Schema::hasColumn('cursos', 'obrigatorio')) {
            $queryCursos->where('obrigatorio', true);
        }
        $cursoIds = $queryCursos->pluck('id')->all();
        $resultado['total_cursos'] = count($cursoIds);

        if (!$cursoIds) {
            $resultado['mensagem'] = 'Nenhum curso obrigatório foi cadastrado.';
            return $resultado;
        }

        // Busca aulas tanto pelo curso_id direto quanto pelo módulo ao qual pertencem.
        $moduloIds = DB::table('modulos')->whereIn('curso_id', $cursoIds)->pluck('id')->all();
        $queryAulas = DB::table('aulas')->where(function ($query) use ($cursoIds, $moduloIds) {
            if (Schema::hasColumn('aulas', 'curso_id')) {
                $query->whereIn('curso_id', $cursoIds);
                if ($moduloIds) {
                    $query->orWhereIn('modulo_id', $moduloIds);
                }
            } elseif ($moduloIds) {
                $query->whereIn('modulo_id', $moduloIds);
            } else {
                $query->whereRaw('1 = 0');
            }
        });
        $aulas = $queryAulas->select('id', 'curso_id', 'modulo_id')->get()->unique('id');
        $aulaIds = $aulas->pluck('id')->all();
        $resultado['total_aulas'] = count($aulaIds);

        $assistidasIds = $aulaIds ? DB::table('aulas_assistidas')
            ->where('aluno_id', $alunoId)
            ->where('assistido', true)
            ->whereIn('aula_id', $aulaIds)
            ->distinct()->pluck('aula_id')->all() : [];
        $assistidas = array_fill_keys($assistidasIds, true);
        $resultado['aulas_assistidas'] = count($assistidasIds);

        $avaliacoes = $aulaIds ? DB::table('avaliacoes')
            ->whereIn('aula_id', $aulaIds)
            ->where(function ($query) {
                $query->whereIn('tipo', ['normal', 'pos_teste', 'pós-teste'])
                    ->orWhereNull('tipo');
            })
            ->select('id', 'aula_id')->get() : collect();
        $avaliacaoIds = $avaliacoes->pluck('id')->all();
        $resultado['total_pos_testes'] = count($avaliacaoIds);

        $melhoresNotas = $avaliacaoIds ? DB::table('notas')
            ->where('aluno_id', $alunoId)
            ->whereIn('avaliacao_id', $avaliacaoIds)
            ->whereNotNull('nota')
            ->select('avaliacao_id', DB::raw('MAX(nota) AS melhor_nota'))
            ->groupBy('avaliacao_id')
            ->pluck('melhor_nota', 'avaliacao_id') : collect();
        $resultado['pos_testes_feitos'] = $melhoresNotas->count();

        $soma = 0.0;
        foreach ($avaliacaoIds as $id) {
            $soma += max(0.0, min(10.0, (float) ($melhoresNotas->get($id) ?? 0)));
        }
        $resultado['media_pos_testes'] = $avaliacaoIds
            ? round(($soma / (count($avaliacaoIds) * 10)) * 100, 2) : 0.0;

        // A conclusão de cada curso requer TODAS as aulas e TODOS os pós-testes,
        // não somente um percentual de progresso. Cursos sem aulas não são concluídos.
        $avaliacoesPorAula = $avaliacoes->groupBy('aula_id');
        $cursosConcluidos = 0;
        foreach ($cursoIds as $cursoId) {
            $modulosDoCurso = DB::table('modulos')->where('curso_id', $cursoId)->pluck('id')->all();
            $aulasDoCurso = $aulas->filter(function ($aula) use ($cursoId, $modulosDoCurso) {
                return (int) $aula->curso_id === (int) $cursoId
                    || in_array($aula->modulo_id, $modulosDoCurso);
            });
            if ($aulasDoCurso->isEmpty()) {
                continue;
            }

            $concluido = true;
            foreach ($aulasDoCurso as $aula) {
                if (!isset($assistidas[$aula->id])) {
                    $concluido = false;
                    break;
                }
                foreach ($avaliacoesPorAula->get($aula->id, collect()) as $teste) {
                    if (!$melhoresNotas->has($teste->id)) {
                        $concluido = false;
                        break 2;
                    }
                }
            }
            if ($concluido) {
                $cursosConcluidos++;
            }
        }
        $resultado['cursos_concluidos'] = $cursosConcluidos;

        $resultado['requisitos']['aulas'] = $resultado['total_aulas'] > 0
            && $resultado['aulas_assistidas'] === $resultado['total_aulas'];
        $resultado['requisitos']['pos_testes'] = $resultado['total_pos_testes'] > 0
            && $resultado['pos_testes_feitos'] === $resultado['total_pos_testes'];
        $resultado['requisitos']['cursos'] = $cursosConcluidos === $resultado['total_cursos'];

        // Mesma prova final geral selecionada pelo AvaliacaoController.
        $provaFinal = DB::table('avaliacoes')->where('tipo', 'final')
            ->orderByDesc('updated_at')->orderByDesc('id')->first();
        if ($provaFinal) {
            $nota = DB::table('notas')->where('aluno_id', $alunoId)
                ->where('avaliacao_id', $provaFinal->id)->max('nota');
            if ($nota !== null && is_numeric($nota) && (float) $nota >= 0 && (float) $nota <= 10) {
                $resultado['prova_final_feita'] = true;
                $resultado['nota_final'] = round((float) $nota * 10, 2);
            }
        }
        $resultado['requisitos']['prova_final'] = $resultado['prova_final_feita'];
        $resultado['requisitos']['aprovacao_final'] = $resultado['nota_final'] !== null
            && $resultado['nota_final'] >= 70;

        $resultado['liberado'] = !in_array(false, $resultado['requisitos'], true)
            && $resultado['media_pos_testes'] >= 70;
        $resultado['mensagem'] = $resultado['liberado']
            ? 'Certificado liberado.'
            : 'Para receber o certificado, conclua todas as aulas, todos os pós-testes e todos os cursos obrigatórios, alcance média geral mínima de 70% nos pós-testes e obtenha pelo menos 70% na prova final.';

        return $resultado;
    }

    // =========================
    // REGISTRAR CERTIFICADO EMITIDO
    // =========================
    public static function registrarEmissao($aluno, $certificado, $notaFinal = null)
    {
        if (!$aluno || !$certificado || !Schema::hasTable('certificados_emitidos')) {
            return null;
        }

        // Impede emissão mesmo por chamadas diretas, sem satisfazer os 5 requisitos.
        $situacao = self::verificarRequisitos($aluno->id);
        if (!$situacao['liberado']) {
            return null;
        }

        // Compatível com a migration original (modelo_id, nome_aluno, codigo),
        // e com instalações que usam os nomes alternativos.
        $tabela = 'certificados_emitidos';
        $colunaModelo = Schema::hasColumn($tabela, 'certificado_modelo_id')
            ? 'certificado_modelo_id'
            : (Schema::hasColumn($tabela, 'modelo_id') ? 'modelo_id' : null);
        $colunaCodigo = Schema::hasColumn($tabela, 'codigo_validacao')
            ? 'codigo_validacao'
            : (Schema::hasColumn($tabela, 'codigo') ? 'codigo' : null);

        if (!$colunaModelo || !$colunaCodigo) {
            throw new \RuntimeException('Estrutura de certificados_emitidos incompatível: faltam campos de modelo ou código.');
        }

        $existente = DB::table($tabela)
            ->where('aluno_id', $aluno->id)
            ->where($colunaModelo, $certificado->id)
            ->first();

        if ($existente) {
            return $existente;
        }

        $codigo = 'CERT-' . strtoupper(bin2hex(random_bytes(10)));
        $dados = [
            'aluno_id' => $aluno->id,
            $colunaModelo => $certificado->id,
            $colunaCodigo => $codigo,
        ];

        $camposOpcionais = [
            'nome_aluno' => $aluno->name,
            'aluno_nome' => $aluno->name,
            'email' => $aluno->email,
            'cpf' => $aluno->cpf ?? null,
            'curso' => $certificado->curso ?? null,
            'carga_horaria' => $certificado->carga_horaria ?? null,
            'nota_final' => $situacao['nota_final'],
            'data_conclusao' => now()->toDateString(),
            'data_emissao' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        foreach ($camposOpcionais as $coluna => $valor) {
            if (Schema::hasColumn($tabela, $coluna)) {
                $dados[$coluna] = $valor;
            }
        }

        // Transação e nova conferência evitam regravações em acessos simultâneos.
        return DB::transaction(function () use ($tabela, $colunaModelo, $colunaCodigo, $aluno, $certificado, $dados, $codigo) {
            $existente = DB::table($tabela)
                ->where('aluno_id', $aluno->id)
                ->where($colunaModelo, $certificado->id)
                ->lockForUpdate()
                ->first();
            if ($existente) {
                return $existente;
            }
            DB::table($tabela)->insert($dados);
            return DB::table($tabela)->where($colunaCodigo, $codigo)->first();
        });
    }

    // =========================
    // BUSCAR NOTA DA PROVA FINAL GERAL DO ALUNO
    // =========================
    private function buscarNotaFinalDoAluno($alunoId)
    {
        return self::verificarRequisitos($alunoId)['nota_final'];
    }

    // =========================
    // GERAR PDF DO CERTIFICADO
    // =========================
    public function gerar($id)
    {
        $aluno = auth()->user();
        if (!$aluno) {
            abort(403, 'Usuário não autenticado.');
        }

        $situacao = self::verificarRequisitos($aluno->id);
        if (!$situacao['liberado']) {
            return redirect()->route('certificado.aluno')
                ->with('error', $situacao['mensagem']);
        }

        $notaFinal = $this->buscarNotaFinalDoAluno($aluno->id);
        $certificado = DB::table('certificados')->where('id', $id)->first();
        if (!$certificado) {
            abort(404, 'Certificado não encontrado.');
        }

        $certificadoEmitido = self::registrarEmissao($aluno, $certificado, $notaFinal);
        if (!$certificadoEmitido) {
            abort(403, 'Não foi possível validar a emissão deste certificado.');
        }

        $dataConclusao = !empty($certificadoEmitido->data_conclusao)
            ? \Carbon\Carbon::parse($certificadoEmitido->data_conclusao)->format('d/m/Y')
            : (!empty($certificadoEmitido->data_emissao)
                ? \Carbon\Carbon::parse($certificadoEmitido->data_emissao)->format('d/m/Y')
                : now()->format('d/m/Y'));
        $codigo = $certificadoEmitido->codigo_validacao ?? $certificadoEmitido->codigo ?? null;
        $dados = [
            'nome_aluno'     => $aluno->name,
            'curso'          => $certificado->curso,
            'carga_horaria'  => $certificado->carga_horaria,
            'responsavel'    => $certificado->responsavel,
            'cargo'          => $certificado->cargo,
            'assinatura'     => $certificado->assinatura,
            'data_conclusao' => $dataConclusao,
            'codigo'         => $codigo,
            'cpf'            => $aluno->cpf,
            'aproveitamento' => number_format($notaFinal, 1, ',', '.') . '%',
        ];

        $pdf = Pdf::loadView('dashboard.certificados.pdf', $dados)
            ->setPaper('a4', 'landscape');
        return $pdf->download('certificado.pdf');
    }
}
