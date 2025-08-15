<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\PriceHistory;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchAssetPrices extends Command
{
    /**
     * O nome e a assinatura do comando no console.
     * É assim que chamamos o comando: php artisan prices:fetch
     */
    protected $signature = 'prices:fetch';

    /**
     * A descrição do comando.
     */
    protected $description = 'Busca as cotações diárias de uma fonte externa e atualiza o banco de dados.';

    /**
     * Executa a lógica do comando.
     */
    public function handle()
    {
        $this->info('Iniciando a busca por novas cotações...');

        // A URL que você forneceu
        $url = 'https://docs.google.com/spreadsheets/d/e/2PACX-1vSg5I4C8qv-jrzQRPtbfGuEvUeEol7XBC2oudTLa-im4RBLMxWnbmEFUmPzr7ZM8cLgBDteuCZtOe2j/pub?gid=0&single=true&output=tsv';

        try {
            // 1. Faz a requisição HTTP para a URL
            $response = Http::get($url);

            if ($response->failed()) {
                $this->error('Falha ao buscar os dados da URL.');
                Log::error('Falha na API de cotações: ' . $response->body());
                return 1; // Retorna um código de erro
            }

            // 2. Processa o arquivo TSV
            $data = $response->body();
            $lines = explode("\n", trim($data));
            //$header = array_shift($lines); // Remove a linha do cabeçalho
            
            $progressBar = $this->output->createProgressBar(count($lines));
            $progressBar->start();

            $updatedCount = 0;
            $createdCount = 0;

            foreach ($lines as $line) {
                // Separa as colunas por Tabulação (TSV)
                $columns = explode("\t", trim($line));
                
                // Garante que a linha tem o formato esperado [ticker, price, date]
                if (count($columns) < 3) continue;

                $ticker = trim($columns[0]);
                $price = (float) str_replace(',', '.', trim($columns[1])); // Converte vírgula para ponto
                try {
                    $date = Carbon::createFromFormat('d/m/Y', trim($columns[2]));
                } catch (\Exception $e) {
                    Log::warning("Data inválida para o ticker {$ticker}: " . $columns[2]);
                    continue; // Pula para a próxima linha se a data for inválida
                }

                // 3. Encontra o ativo no nosso banco de dados
                $asset = Asset::where('ticker', $ticker)->first();

                if ($asset) {
                    // 4. Salva ou atualiza o histórico de preço
                    // updateOrCreate é perfeito: ele atualiza se já existir um preço
                    // para este ativo nesta data, ou cria um novo se não existir.
                    $priceHistory = PriceHistory::updateOrCreate(
                        ['asset_id' => $asset->id, 'date' => $date->toDateString()],
                        ['price' => $price]
                    );

                    if ($priceHistory->wasRecentlyCreated) {
                        $createdCount++;
                    } else {
                        $updatedCount++;
                    }
                }
                $progressBar->advance();
            }

            $progressBar->finish();
            $this->info("\nProcesso concluído!");
            $this->info("{$createdCount} cotações novas inseridas.");
            $this->info("{$updatedCount} cotações existentes atualizadas.");

            return 0; // Retorna um código de sucesso

        } catch (\Exception $e) {
            $this->error('Ocorreu um erro inesperado: ' . $e->getMessage());
            Log::error('Erro ao executar prices:fetch: ' . $e->getMessage());
            return 1;
        }
    }
}