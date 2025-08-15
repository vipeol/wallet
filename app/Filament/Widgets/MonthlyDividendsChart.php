<?php

namespace App\Filament\Widgets;

use App\Models\Dividend;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MonthlyDividendsChart extends ChartWidget
{
    protected static ?string $heading = 'Proventos Recebidos (Últimos 12 Meses)';
    protected static ?string $maxHeight = '300px';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        // 1. Pega todos os proventos do usuário nos últimos 12 meses
        $data = Dividend::query()
            ->where('user_id', Auth::id())
            ->where('payment_date', '>=', now()->subYear())
            ->orderBy('payment_date')
            ->get()
            ->groupBy(fn ($dividend) => $dividend->payment_date->format('M/y')) // Agrupa por Mês/Ano
            ->map(fn ($group) => $group->sum('total_received')); // Soma o total recebido no mês

        return [
            'datasets' => [
                [
                    'label' => 'Total Recebido (R$)',
                    'data' => $data->values()->toArray(),
                    'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                    'borderColor' => 'rgb(54, 162, 235)',
                ],
            ],
            'labels' => $data->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // Define o tipo do gráfico como barras
    }

}