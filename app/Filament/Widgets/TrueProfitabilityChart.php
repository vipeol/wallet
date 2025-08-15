<?php

namespace App\Filament\Widgets;

use App\Models\PortfolioSnapshot;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TrueProfitabilityChart extends ChartWidget
{
    protected static ?string $heading = 'Rentabilidade Real da Carteira (Base 100)';
    protected static ?string $maxHeight = '300px';
    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $user = Auth::user();
        $startDate = now()->subYear();

        // 1. Busca os dados pré-calculados da tabela de snapshots
        $snapshots = PortfolioSnapshot::where('user_id', $user->id)
            ->where('date', '>=', $startDate)
            ->orderBy('date')
            ->get();

        // 2. Agrupa os dados por carteira para criar as linhas do gráfico
        $dataByPortfolio = $snapshots->groupBy('portfolio.name');
        
        $datasets = [];
        $colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'];
        $colorIndex = 0;
        
        foreach ($dataByPortfolio as $portfolioName => $portfolioSnapshots) {
            $datasets[] = [
                'label' => $portfolioName,
                'data' => $portfolioSnapshots->pluck('cota_value')->toArray(),
                'borderColor' => $colors[$colorIndex % count($colors)],
                'fill' => false, 'tension' => 0.1,
            ];
            $colorIndex++;
        }
        
        // Pega os labels (datas) dos snapshots
        $labels = $snapshots->unique('date')->pluck('date')->map(fn ($date) => Carbon::parse($date)->format('M/y'));

        return ['datasets' => $datasets, 'labels' => $labels];
    }

    protected function getType(): string
    {
        return 'line';
    }
    
    protected function getOptions(): array
    {
        return [
            'plugins' => [ 'legend' => [ 'display' => true, 'position' => 'top' ] ],
            'scales' => [ 
                'y' => [ 
                    'title' => [ 
                        'display' => true, 
                        'text' => 'Índice (Base 100)' 
                    ], 
                    //'ticks' => [ 'callback' => RawJs::from("(value) => Number(value).toFixed(2)") ] 
                ] 
            ],
        ];
    }
}