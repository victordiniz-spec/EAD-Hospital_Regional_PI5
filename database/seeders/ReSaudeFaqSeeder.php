<?php

namespace Database\Seeders;

use App\Models\DuvidaFrequente;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class ReSaudeFaqSeeder extends Seeder
{
    public function run(): void
    {
        // Lê o JSON que acompanha este Seeder. Não altera migrations ou tabelas.
        $arquivo = database_path('seeders/perguntas_frequentes.json');

        if (!is_file($arquivo)) {
            throw new RuntimeException("Arquivo não encontrado: {$arquivo}");
        }

        if (!Schema::hasTable('duvidas_frequentes')) {
            throw new RuntimeException('A tabela duvidas_frequentes não foi encontrada. Verifique o nome real da tabela antes de executar.');
        }

        $colunasObrigatorias = ['pergunta', 'resposta', 'categoria', 'texto_botao', 'rota_botao', 'ordem', 'ativo'];

        foreach ($colunasObrigatorias as $coluna) {
            if (!Schema::hasColumn('duvidas_frequentes', $coluna)) {
                throw new RuntimeException("A coluna {$coluna} não foi encontrada em duvidas_frequentes. Nenhum cadastro foi iniciado.");
            }
        }

        $itens = json_decode(file_get_contents($arquivo), true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($itens) || !array_is_list($itens)) {
            throw new RuntimeException('O JSON precisa conter uma lista de perguntas.');
        }

        // Valida tudo antes de começar a inserir para evitar importação parcial por dados inválidos.
        $vistas = [];
        foreach ($itens as $indice => $item) {
            if (!is_array($item) ||
                !is_string($item['pergunta'] ?? null) || trim($item['pergunta']) === '' ||
                !is_string($item['resposta'] ?? null) || trim($item['resposta']) === '' ||
                !is_string($item['categoria'] ?? null) || trim($item['categoria']) === '') {
                throw new RuntimeException('Registro inválido no JSON, posição '.($indice + 1));
            }
            $chave = mb_strtolower(trim($item['pergunta']), 'UTF-8');
            if (isset($vistas[$chave])) {
                throw new RuntimeException('Pergunta repetida no JSON, posição '.($indice + 1));
            }
            $vistas[$chave] = true;
        }

        $criadas = 0;
        $existentes = 0;

        foreach ($itens as $indice => $item) {
            // firstOrCreate NÃO modifica perguntas antigas nem cria novamente ao executar o Seeder.
            $duvida = DuvidaFrequente::firstOrCreate(
                ['pergunta' => trim($item['pergunta'])],
                [
                    'resposta' => trim($item['resposta']),
                    'categoria' => trim($item['categoria']),
                    'texto_botao' => null,
                    'rota_botao' => null,
                    'ordem' => $indice + 9, // Reserva 1 a 8 para o Seeder antigo.
                    'ativo' => true,
                ]
            );

            if ($duvida->wasRecentlyCreated) {
                $criadas++;
            } else {
                $existentes++;
            }
        }

        $this->command?->info("Perguntas novas: {$criadas}. Já existentes: {$existentes}. Total no JSON: ".count($itens).'.');
    }
}
