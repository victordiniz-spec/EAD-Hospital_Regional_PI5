<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CodigoCadastroMail extends Mailable
{
    use SerializesModels;

    public string $nome;
    public string $codigo;

    public function __construct(string $nome, string $codigo)
    {
        $this->nome = $nome;
        $this->codigo = $codigo;
    }

    public function build()
    {
        return $this->subject('Código de verificação - Integrar ReSaúde')
            ->view('emails.codigo-cadastro');
    }
}