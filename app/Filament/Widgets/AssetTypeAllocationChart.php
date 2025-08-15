<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use Filament\Widgets\ChartWidget;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Auth;

class AssetTypeAllocationChart extends ChartWidget
{
    protected static ?string $heading = 'Alocação por Tipo de Ativo';
    protected static ?string $maxHeight = '300px'; // Ajusta a altura do gráfico
    protected static ?int $sort = 1;
    
    protected function getData(): array
    {
        // 1. Busca todos os ativos de investimento do usuário
        $assets = Auth::user()->assets()->whereNotIn('type', ['currency', 'benchmark'])->get();

        // 2. Agrupa os ativos por tipo e soma o valor de mercado de cada grupo
        $allocationData = $assets->groupBy('type')
            ->map(fn ($group) => $group->sum('market_value'));

        // 3. Prepara os dados no formato que o Chart.js (usado pelo Filament) espera
        return [
            'datasets' => [
                [
                    'label' => 'Valor de Mercado (R$)',
                    'data' => $allocationData->values()->toArray(),
                    'backgroundColor' => [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(153, 102, 255)',
                        'rgb(255, 159, 64)',
                    ],

                ],
            ],
            'labels' => $allocationData->keys()->map(fn ($type) => strtoupper($type))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie'; // Define o tipo do gráfico como pizza
    }

    /**
     * MUDANÇA #2: Adicionando este método para customizar as opções do Chart.js
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true, // Você pode manter ou remover a legenda
                    'position' => 'bottom', // Posição da legenda
                ],
            ],
            // A mágica para remover os eixos X e Y
            'scales' => [
                'x' => [
                    'display' => false,
                ],
                'y' => [
                    'display' => false,
                ],
            ],
        ];
    }

}