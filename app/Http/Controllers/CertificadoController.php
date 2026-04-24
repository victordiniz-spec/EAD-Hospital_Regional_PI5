<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class CertificadoController extends Controller
{
    public function gerar($id)
    {
        // 🔎 Buscar modelo do certificado
        $certificado = DB::table('certificados')->where('id', $id)->first();

        if (!$certificado) {
            abort(404, 'Certificado não encontrado');
        }

        // 👤 Dados do aluno logado
        $nomeAluno = auth()->user()->name;

        // 📅 Data formatada
        $dataConclusao = now()->format('d/m/Y');

        // 🔐 Código único do certificado
        $codigo = strtoupper(uniqid('CERT-'));

        // 📦 Dados enviados para o PDF
        $dados = [
            'nome_aluno'     => $nomeAluno,
            'curso'          => $certificado->curso,
            'carga_horaria'  => $certificado->carga_horaria,
            'responsavel'    => $certificado->responsavel,
            'cargo'          => $certificado->cargo,
            'assinatura'     => $certificado->assinatura,
            'data_conclusao' => $dataConclusao,
            'codigo'         => $codigo,
        ];

        // 📄 Gerar PDF
        $pdf = Pdf::loadView('dashboard.certificados.pdf', $dados);

        // ⬇️ Download automático
        return $pdf->download('certificado.pdf');
    }
}