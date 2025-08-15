<?php

namespace App\Filament\Widgets;

use App\Models\PortfolioSnapshot;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PortfolioYieldChart extends ChartWidget
{
    protected static ?string $heading = 'Proventos Mensais vs. Dividend Yield';
    protected static ?string $maxHeight = '300px';
    protected static ?int $sort = 5; // Ajuste a ordem conforme preferir

    protected function getData(): array
    {
        $user = Auth::user();
        $startDate = now()->subMonths(11)->startOfMonth();

        // 1. Busca todos os proventos do usuário no período
        $dividendsInPeriod = $user->dividends()
            ->where('payment_date', '>=', $startDate)
            ->get()
            ->groupBy(fn ($dividend) => $dividend->payment_date->format('Y-m'));

        // 2. Busca todos os snapshots do usuário no período de uma vez
        $snapshots = PortfolioSnapshot::where('user_id', $user->id)
            ->where('date', '>=', $startDate->copy()->subMonth()) // Pega um mês a mais para o cálculo do yield
            ->orderBy('date')
            ->get()
            ->keyBy(fn($s) => $s->date->format('Y-m'));
            
        // 3. Prepara os dados para o gráfico
        $period = Carbon::parse($startDate)->toPeriod(now()->endOfMonth(), '1 month');
        $labels = [];
        $totalDividendsData = [];
        $monthlyYieldData = [];

        foreach ($period as $date) {
            $monthKey = $date->format('Y-m');
            $labels[] = $date->format('M/y');

            // Soma os proventos do mês
            $totalReceivedInMonth = $dividendsInPeriod->get($monthKey, collect())->sum('total_received');
            $totalDividendsData[] = $totalReceivedInMonth;
            
            // Pega o valor da carteira no INÍCIO do mês (final do mês anterior)
            $startOfMonthKey = $date->copy()->subMonth()->format('Y-m');
            $portfolioValueAtStartOfMonth = $snapshots->get($startOfMonthKey)?->market_value ?? 0;
            
            // Calcula o Dividend Yield do mês
            if ($portfolioValueAtStartOfMonth > 0) {
                $monthlyYield = ($totalReceivedInMonth / $portfolioValueAtStartOfMonth) * 100;
                $monthlyYieldData[] = $monthlyYield;
            } else {
                $monthlyYieldData[] = 0;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Proventos Recebidos (R$)',
                    'data' => $totalDividendsData,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                    'borderColor' => 'rgb(54, 162, 235)',
                    'yAxisID' => 'y',
                    'type' => 'bar',
                ],
                [
                    'label' => 'Dividend Yield Mensal (%)',
                    'data' => $monthlyYieldData,
                    'borderColor' => 'rgb(255, 99, 132)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.5)',
                    'yAxisID' => 'y1',
                    'type' => 'line',
                    'tension' => 0.1
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [ 'legend' => [ 'display' => true, 'position' => 'top' ] ],
            'scales' => [
                'y' => [
                    'type' => 'linear', 'display' => true, 'position' => 'left',
                    'title' => [ 'display' => true, 'text' => 'Valor (R$)' ],
                    //'ticks' => [ 'callback' => RawJs::from("(value) => 'R$ ' + Number(value).toFixed(2).replace('.', ',')") ],
                ],
                'y1' => [
                    'type' => 'linear', 'display' => true, 'position' => 'right',
                    'title' => [ 'display' => true, 'text' => 'Percentual (%)' ],
                    'grid' => [ 'drawOnChartArea' => false ],
                    //'ticks' => [ 'callback' => RawJs::from("(value) => Number(value).toFixed(2).replace('.', ',') + '%'") ],
                ],
            ],
        ];
    }
}