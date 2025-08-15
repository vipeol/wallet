<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class PortfolioStatsOverview extends BaseWidget
{
    protected static ?int $sort = -100;
    
    protected function getStats(): array
    {
        // 1. Busca todos os ativos de investimento do usuário de uma só vez.
        $assets = Auth::user()->assets()->whereNotIn('type', ['currency', 'benchmark'])->get();

        // 2. Calcula os totais gerais usando os accessors que já temos.
        $totalMarketValue = $assets->sum('market_value');
        $totalAcquisitionCost = $assets->sum('total_acquisition_cost');
        $totalDayProfitLoss = $assets->sum('day_profit_loss');
        $totalUnrealizedProfitLoss = $assets->sum('unrealized_profit_loss');
        
        // Calcula a variação percentual do dia
        $previousDayMarketValue = $totalMarketValue - $totalDayProfitLoss;
        $dayPercentageChange = $previousDayMarketValue > 0 
            ? ($totalDayProfitLoss / $previousDayMarketValue) * 100 
            : 0;

        // 3. Retorna um array de "Stat" cards, cada um representando um card no dashboard.
        return [
            Stat::make('Valor Total da Carteira', 'R$ ' . number_format($totalMarketValue, 2, ',', '.'))
                ->description('Valor de mercado atual de todos os ativos.')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),

            Stat::make('Variação do Dia', 'R$ ' . number_format($totalDayProfitLoss, 2, ',', '.'))
                ->description(number_format($dayPercentageChange, 2, ',', '.') . '% de variação hoje')
                ->descriptionIcon($totalDayProfitLoss >= 0 ? 'heroicon-m-arrow-up-right' : 'heroicon-m-arrow-down-right')
                ->color($totalDayProfitLoss >= 0 ? 'success' : 'danger'),
            
            Stat::make('Lucro/Prejuízo Total', 'R$ ' . number_format($totalUnrealizedProfitLoss, 2, ',', '.'))
                ->description('Lucro/prejuízo não realizado de toda a carteira.')
                ->descriptionIcon($totalUnrealizedProfitLoss >= 0 ? 'heroicon-m-chart-bar' : 'heroicon-m-chart-bar-square')
                ->color($totalUnrealizedProfitLoss >= 0 ? 'success' : 'danger'),

            Stat::make('Custo Total de Aquisição', 'R$ ' . number_format($totalAcquisitionCost, 2, ',', '.'))
                ->description('Total investido para a posição atual.')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('gray'),
        ];
    }
}