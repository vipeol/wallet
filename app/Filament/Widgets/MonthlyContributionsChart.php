<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class MonthlyContributionsChart extends ChartWidget
{
    protected static ?string $heading = 'Aportes Mensais vs. Acumulado';
    protected static ?string $maxHeight = '300px';
    protected static ?string $pollingInterval = null;
    protected static ?int $sort = 7;
    
    protected function getData(): array
    {
        // Usa cache para performance
        return Cache::remember('monthly_contributions_chart_' . Auth::id(), 180, function () {
            
            // 1. Busca todas as transações de COMPRA do usuário
            $buyTransactions = Auth::user()->transactions()
                ->where('type', 'buy')
                ->orderBy('transaction_date', 'asc')
                ->get();

            // Se não houver transações, retorna um gráfico vazio
            if ($buyTransactions->isEmpty()) {
                return [
                    'datasets' => [],
                    'labels' => [],
                ];
            }

            // 2. Agrupa as transações por mês e soma o custo total de cada mês
            $monthlyBuys = $buyTransactions->groupBy(function ($transaction) {
                return $transaction->transaction_date->format('Y-m');
            })->map(function ($monthlyTransactions) {
                return $monthlyTransactions->sum(function ($transaction) {
                    // Lógica de conversão de moeda que já temos
                    $costInNativeCurrency = $transaction->quantity * $transaction->unit_price;
                    if ($transaction->asset->currency === 'USD') {
                        $rate = \App\Models\PriceHistory::getUsdBrlRateOn($transaction->transaction_date);
                        return $costInNativeCurrency * $rate;
                    }
                    return $costInNativeCurrency;
                });
            });

            // 3. Prepara os dados para o gráfico, garantindo que todos os meses no período apareçam
            $startDate = $buyTransactions->first()->transaction_date->startOfMonth();
            $endDate = now()->endOfMonth();
            $period = Carbon::parse($startDate)->toPeriod($endDate, '1 month');
            
            $labels = [];
            $monthlyData = [];
            $cumulativeData = [];
            $cumulativeTotal = 0;

            foreach ($period as $date) {
                $monthKey = $date->format('Y-m');
                $labels[] = $date->format('M/y');

                $monthlyValue = $monthlyBuys->get($monthKey, 0);
                $monthlyData[] = $monthlyValue;
                
                $cumulativeTotal += $monthlyValue;
                $cumulativeData[] = $cumulativeTotal;
            }

            // 4. Retorna os datasets formatados
            return [
                'datasets' => [
                    [
                        'label' => 'Aporte Mensal (R$)',
                        'data' => $monthlyData,
                        'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                        'borderColor' => 'rgb(54, 162, 235)',
                        'yAxisID' => 'y',
                        'type' => 'bar',
                    ],
                    [
                        'label' => 'Total Acumulado (R$)',
                        'data' => $cumulativeData,
                        'borderColor' => 'rgb(255, 99, 132)',
                        'backgroundColor' => 'rgba(255, 99, 132, 0.5)',
                        'yAxisID' => 'y1',
                        'type' => 'line',
                        'tension' => 0.1
                    ],
                ],
                'labels' => $labels,
            ];
        });
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [ 'display' => true, 'position' => 'bottom' ],
            ],
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [ 'display' => true, 'text' => 'Valor Mensal (R$)' ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [ 'display' => true, 'text' => 'Valor Acumulado (R$)' ],
                    'grid' => [ 'drawOnChartArea' => false ],
                ],
            ],
        ];
    }
}