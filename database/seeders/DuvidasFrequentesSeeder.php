<?php

namespace Database\Seeders;

use App\Models\DuvidaFrequente;
use Illuminate\Database\Seeder;

class DuvidasFrequentesSeeder extends Seeder
{
    public function run(): void
    {
        $duvidas = [
            [
                'pergunta' => 'Como acessar minhas aulas?',
                'resposta' => 'Para acessar suas aulas, entre no menu "Videoaulas". Nessa área você poderá visualizar os módulos disponíveis, acompanhar seu progresso e continuar seus estudos.',
                'categoria' => 'Aulas',
                'texto_botao' => 'Ir para videoaulas',
                'rota_botao' => 'aluno.aulas',
                'ordem' => 1,
            ],
            [
                'pergunta' => 'Minha aula não marcou como assistida. O que devo fazer?',
                'resposta' => 'Verifique se você assistiu ao tempo mínimo exigido da aula. Caso tenha assistido corretamente, atualize a página e confira novamente. Se o problema continuar, informe a administração do curso.',
                'categoria' => 'Aulas',
                'texto_botao' => 'Ver aulas',
                'rota_botao' => 'aluno.aulas',
                'ordem' => 2,
            ],
            [
                'pergunta' => 'Como faço uma avaliação ou pós-teste?',
                'resposta' => 'As avaliações ficam disponíveis conforme o andamento das aulas e módulos. Quando houver uma avaliação pendente, ela aparecerá no painel do aluno ou junto à aula correspondente.',
                'categoria' => 'Avaliações',
                'texto_botao' => 'Ir para o painel',
                'rota_botao' => 'dashboard.aluno',
                'ordem' => 3,
            ],
            [
                'pergunta' => 'Como emitir meu certificado?',
                'resposta' => 'O certificado ficará disponível quando você cumprir os requisitos definidos para o curso, como progresso mínimo, participação nas atividades e desempenho nas avaliações.',
                'categoria' => 'Certificado',
                'texto_botao' => 'Ver certificado',
                'rota_botao' => 'certificado.aluno',
                'ordem' => 4,
            ],
            [
                'pergunta' => 'Como recuperar minha senha?',
                'resposta' => 'Na tela de login, clique em "Esqueci minha senha". Depois informe seu CPF e e-mail cadastrados para receber um código de redefinição.',
                'categoria' => 'Acesso',
                'texto_botao' => 'Ir para recuperação de senha',
                'rota_botao' => 'senha.esqueci',
                'ordem' => 5,
            ],
            [
                'pergunta' => 'Meu cadastro ainda está pendente. O que significa?',
                'resposta' => 'Após o cadastro, sua conta precisa ser aprovada pela administração. Enquanto estiver pendente, o acesso completo à plataforma pode ficar bloqueado.',
                'categoria' => 'Acesso',
                'texto_botao' => null,
                'rota_botao' => null,
                'ordem' => 6,
            ],
            [
                'pergunta' => 'Onde vejo os avisos importantes?',
                'resposta' => 'Os avisos importantes aparecem no painel inicial e na área de avisos da plataforma. Fique atento principalmente aos avisos classificados como urgentes.',
                'categoria' => 'Avisos',
                'texto_botao' => 'Ver avisos',
                'rota_botao' => 'avisos',
                'ordem' => 7,
            ],
            [
                'pergunta' => 'Quem pode me ajudar se minha dúvida não estiver aqui?',
                'resposta' => 'Caso sua dúvida não esteja entre as opções da Central de Dúvidas, entre em contato com a administração ou professor responsável pelo curso.',
                'categoria' => 'Suporte',
                'texto_botao' => null,
                'rota_botao' => null,
                'ordem' => 8,
            ],
        ];

        foreach ($duvidas as $duvida) {
            DuvidaFrequente::updateOrCreate(
                ['pergunta' => $duvida['pergunta']],
                array_merge($duvida, ['ativo' => true])
            );
        }
    }
}