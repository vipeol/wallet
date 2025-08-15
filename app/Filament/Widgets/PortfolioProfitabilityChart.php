<?php

namespace App\Filament\Widgets;

use App\Models\PortfolioSnapshot;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PortfolioProfitabilityChart extends ChartWidget
{
    protected static ?string $heading = 'Rentabilidade (Lucro/Prejuízo Não Realizado)';
    protected static ?string $maxHeight = '300px';
    protected static ?int $sort = 3; // Ajuste a ordem se necessário

    protected function getData(): array
    {
        $user = Auth::user();
        $startDate = now()->subYear();

        $snapshots = PortfolioSnapshot::where('user_id', $user->id)
            ->where('date', '>=', $startDate)
            ->orderBy('date')
            ->get();

        $dataByPortfolio = $snapshots->groupBy('portfolio.name');
        
        $datasets = [];
        $colors = ['#4BC0C0', '#9966FF', '#FF9F40', '#FF6384', '#36A2EB', '#FFCE56'];
        $colorIndex = 0;
        
        foreach ($dataByPortfolio as $portfolioName => $portfolioSnapshots) {
            $datasets[] = [
                'label' => $portfolioName . ' (L/P em R$)',
                // A ÚNICA MUDANÇA: lê a coluna 'unrealized_profit_loss'
                'data' => $portfolioSnapshots->pluck('unrealized_profit_loss')->toArray(),
                'borderColor' => $colors[$colorIndex % count($colors)],
                'fill' => false, 'tension' => 0.1
            ];
            $colorIndex++;
        }
        
        $labels = $snapshots->unique('date')->pluck('date')->map(fn ($date) => Carbon::parse($date)->format('M/y'));

        return ['datasets' => $datasets, 'labels' => $labels];
    }

    protected function getType(): string
    {
        return 'line';
    }
    
}