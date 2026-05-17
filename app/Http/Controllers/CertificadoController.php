<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class CertificadoController extends Controller
{
    // =========================
    // TELA DO CERTIFICADO DO ALUNO
    // =========================
    public function aluno()
    {
        return view('aluno.certificado');
    }

    // =========================
    // REGISTRAR CERTIFICADO EMITIDO
    // =========================
    public static function registrarEmissao($aluno, $certificado, $notaFinal = null)
    {
        if (!$aluno || !$certificado) {
            return null;
        }

        // Evita duplicar emissão para o mesmo aluno e mesmo modelo de certificado
        $certificadoJaEmitido = DB::table('certificados_emitidos')
            ->where('aluno_id', $aluno->id)
            ->where('certificado_modelo_id', $certificado->id)
            ->first();

        if ($certificadoJaEmitido) {
            return $certificadoJaEmitido;
        }

        $codigo = strtoupper(uniqid('CERT-'));

        DB::table('certificados_emitidos')->insert([
            'aluno_id' => $aluno->id,
            'aluno_nome' => $aluno->name,
            'email' => $aluno->email,
            'cpf' => $aluno->cpf,
            'certificado_modelo_id' => $certificado->id,
            'curso' => $certificado->curso,
            'carga_horaria' => $certificado->carga_horaria,
            'nota_final' => $notaFinal,
            'codigo_validacao' => $codigo,
            'data_emissao' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('certificados_emitidos')
            ->where('codigo_validacao', $codigo)
            ->first();
    }

    // =========================
    // BUSCAR NOTA FINAL DO ALUNO
    // =========================
    private function buscarNotaFinalDoAluno($alunoId)
    {
        $provaFinal = DB::table('avaliacoes')
            ->where('tipo', 'final')
            ->first();

        if (!$provaFinal) {
            return null;
        }

        $resultadoFinal = DB::table('notas')
            ->where('aluno_id', $alunoId)
            ->where('avaliacao_id', $provaFinal->id)
            ->orderByDesc('created_at')
            ->first();

        if (!$resultadoFinal) {
            return null;
        }

        if (isset($resultadoFinal->porcentagem)) {
            return $resultadoFinal->porcentagem;
        }

        if (isset($resultadoFinal->nota)) {
            return $resultadoFinal->nota;
        }

        if (isset($resultadoFinal->pontuacao)) {
            return $resultadoFinal->pontuacao;
        }

        return null;
    }

    // =========================
    // GERAR PDF DO CERTIFICADO
    // =========================
    public function gerar($id)
    {
        // 🔎 Buscar modelo do certificado
        $certificado = DB::table('certificados')->where('id', $id)->first();

        if (!$certificado) {
            abort(404, 'Certificado não encontrado');
        }

        // 👤 Dados do aluno logado
        $aluno = auth()->user();

        if (!$aluno) {
            abort(403, 'Usuário não autenticado.');
        }

        // 📊 Nota final do aluno
        $notaFinal = $this->buscarNotaFinalDoAluno($aluno->id);

        // 🧾 Registrar emissão no histórico
        $certificadoEmitido = self::registrarEmissao($aluno, $certificado, $notaFinal);

        // 📅 Data formatada
        $dataConclusao = now()->format('d/m/Y');

        // 🔐 Código de validação
        $codigo = $certificadoEmitido->codigo_validacao ?? strtoupper(uniqid('CERT-'));

        // 📦 Dados enviados para o PDF
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
            'aproveitamento' => $notaFinal !== null ? $notaFinal . '%' : '70%',
        ];

        // 📄 Gerar PDF em paisagem
        $pdf = Pdf::loadView('dashboard.certificados.pdf', $dados)
            ->setPaper('a4', 'landscape');

        // ⬇️ Download automático
        return $pdf->download('certificado.pdf');
    }
}