<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\Dividend;
use App\Models\Portfolio;
use App\Models\PortfolioSnapshot;
use App\Models\PriceHistory;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GeneratePortfolioSnapshots extends Command
{
    protected $signature = 'snapshots:generate';
    protected $description = 'Gera o histórico de snapshots de rentabilidade para todas as carteiras.';

    public function handle()
    {
        $this->info('Iniciando a geração de snapshots...');
        $users = User::with('portfolios.assets')->get();

        foreach ($users as $user) {
            $this->info("\nProcessando usuário: {$user->name}");
            
            $allUserAssets = $user->assets()->get();
            $priceHistories = PriceHistory::whereIn('asset_id', $allUserAssets->pluck('id'))
                ->orderBy('date', 'asc')->get()->groupBy('asset_id')
                ->map(fn($history) => $history->keyBy(fn($p) => $p->date->toDateString()));

            foreach ($user->portfolios as $portfolio) {
                $this->processPortfolio($portfolio, $allUserAssets, $priceHistories);
            }
        }
        $this->info("\nProcesso de geração de snapshots concluído com sucesso!");
        return 0;
    }

    private function processPortfolio(Portfolio $portfolio, Collection $allUserAssets, Collection $priceHistories)
    {
        $this->line(" -> Carteira: {$portfolio->name}");
        $portfolio->snapshots()->delete();

        $events = $this->getEventsForPortfolio($portfolio);
        if ($events->isEmpty()) {
            $this->warn("    Nenhuma transação/provento encontrado para esta carteira. Pulando.");
            return;
        }

        $startDate = $events->first()->transaction_date ?? $events->first()->payment_date;
        $period = CarbonPeriod::create($startDate, '1 day', now());
        $eventsByDate = $events->groupBy(fn($e) => ($e->transaction_date ?? $e->payment_date)->toDateString());

        // Inicializa o estado completo
        $cotaValue = 100.0;
        $totalCotas = 0.0;
        $lastMarketValue = 0.0;
        $currentPositions = collect();
        $currentTotalCost = 0.0; // <-- Variável de estado para o custo
        
        $progressBar = $this->output->createProgressBar($period->count());
        $progressBar->start();

        foreach ($period as $date) {
            $dateString = $date->toDateString();
            
            $marketValueBeforeFlows = $this->getMarketValue($currentPositions, $date, $priceHistories, $allUserAssets);
            if ($lastMarketValue > 0 && $marketValueBeforeFlows > 0) {
                $cotaValue *= ($marketValueBeforeFlows / $lastMarketValue);
            }

            $dailyCashFlow = 0;
            if ($eventsByDate->has($dateString)) {
                foreach ($eventsByDate->get($dateString) as $event) {
                    $cashFlow = $this->getCashFlowFromEvent($event, $date, $priceHistories, $allUserAssets);
                    $dailyCashFlow += $cashFlow;
                    
                    if ($cotaValue > 0 && abs($cashFlow) > 0.000001) {
                        $totalCotas += $cashFlow / $cotaValue;
                    }

                    if ($event instanceof \App\Models\Transaction) {
                        $asset = $allUserAssets->firstWhere('id', $event->asset_id);
                        $costInBrl = $this->getTransactionCostInBrl($event, $date, $priceHistories, $allUserAssets);

                        if ($event->type === 'buy') {
                            $currentTotalCost += $costInBrl;
                            $currentPositions[$event->asset_id] = ($currentPositions[$event->asset_id] ?? 0) + $event->quantity;
                        } else { // Venda
                            $positionBeforeSell = ($currentPositions[$event->asset_id] ?? 0);
                            $avgPrice = $positionBeforeSell > 0 ? $currentTotalCost / $positionBeforeSell : 0;
                            $costOfSoldShares = $event->quantity * $avgPrice;
                            $currentTotalCost -= $costOfSoldShares;
                            $currentPositions[$event->asset_id] = $positionBeforeSell - $event->quantity;
                        }
                    }
                }
            }
            
            $lastMarketValue = $marketValueBeforeFlows + $dailyCashFlow;
            $unrealizedProfitLoss = $lastMarketValue - $currentTotalCost;

            PortfolioSnapshot::updateOrCreate(
                ['portfolio_id' => $portfolio->id, 'user_id' => $portfolio->user_id, 'date' => $dateString],
                [
                    'market_value' => $lastMarketValue,
                    'cota_value' => $cotaValue,
                    'total_cotas' => $totalCotas,
                    'total_acquisition_cost' => $currentTotalCost, // <-- Salva o custo
                    'unrealized_profit_loss' => $unrealizedProfitLoss, // <-- Salva o lucro/prejuízo
                ]
            );
            $progressBar->advance();
        }
        $progressBar->finish();
    }

    private function getTransactionCostInBrl(Transaction $transaction, Carbon $date, Collection $priceHistories, Collection $allUserAssets): float
    {
        $costInNativeCurrency = $transaction->quantity * $transaction->unit_price;
        if ($transaction->asset->currency === 'USD') {
            $usdAsset = $allUserAssets->firstWhere('ticker', 'USDBRL');
            if ($usdAsset) {
                $rate = $this->getPriceFromMemory($priceHistories, $usdAsset->id, $date);
                return $costInNativeCurrency * $rate;
            }
        }
        return $costInNativeCurrency;
    }

    private function getEventsForPortfolio(Portfolio $portfolio) {
        $assetIds = $portfolio->assets->pluck('id');
        $transactions = Transaction::whereIn('asset_id', $assetIds)->get();
        $dividends = Dividend::whereIn('asset_id', $assetIds)->get();
        return collect()->merge($transactions)->merge($dividends)->sortBy(fn($e) => $e->transaction_date ?? $e->payment_date);
    }
    
    private function getMarketValue(Collection $positions, Carbon $date, Collection $priceHistories, Collection $allUserAssets): float {
        $totalValue = 0;
        $usdAsset = $allUserAssets->firstWhere('ticker', 'USDBRL');
        foreach ($positions as $assetId => $quantity) {
            if ($quantity <= 0.00000001) continue;
            $price = $this->getPriceFromMemory($priceHistories, $assetId, $date);
            $asset = $allUserAssets->firstWhere('id', $assetId);
            if ($asset && $asset->currency === 'USD' && $usdAsset) {
                $rate = $this->getPriceFromMemory($priceHistories, $usdAsset->id, $date);
                $price *= $rate;
            }
            $totalValue += $quantity * $price;
        }
        return $totalValue;
    }

    private function getCashFlowFromEvent($event, Carbon $date, Collection $priceHistories, Collection $allUserAssets): float {
        $cashFlow = 0;
        $asset = $event->asset;
        if (!$asset) return 0;
        if ($event instanceof \App\Models\Transaction) {
            $valueInNativeCurrency = $event->quantity * $event->unit_price;
            // Para TWRR, 'buy' é um fluxo de caixa POSITIVO (dinheiro entrando na carteira)
            // e 'sell' é NEGATIVO (dinheiro saindo).
            $cashFlow = $event->type === 'buy' ? $valueInNativeCurrency : -$valueInNativeCurrency;
            if ($asset->currency === 'USD') {
                $usdAsset = $allUserAssets->firstWhere('ticker','USDBRL');
                if ($usdAsset) {
                    $rate = $this->getPriceFromMemory($priceHistories, $usdAsset->id, $date);
                    $cashFlow *= $rate;
                }
            }
        } elseif ($event instanceof \App\Models\Dividend) {
            // Dividendo é um fluxo de caixa POSITIVO.
            $cashFlow = $event->total_received;
        }
        return $cashFlow;
    }
    
    private function getPriceFromMemory(Collection $priceHistories, int $assetId, Carbon $date): float {
        $dateString = $date->toDateString();
        $historyForAsset = $priceHistories->get($assetId);

        if (is_null($historyForAsset)) return 0;
        
        // Acesso direto à chave (rápido)
        if ($historyForAsset->has($dateString)) {
            return (float) $historyForAsset->get($dateString)->price;
        }
        
        // Fallback (lento, mas necessário para dias sem cotação, como fins de semana)
        $record = $historyForAsset->last(fn ($p) => $p->date->lte($date));
        return $record ? (float)$record->price : 0;
    }
}