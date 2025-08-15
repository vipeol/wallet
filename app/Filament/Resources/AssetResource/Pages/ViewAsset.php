<?php

namespace App\Filament\Resources\AssetResource\Pages;

use App\Filament\Resources\AssetResource;
use Filament\Resources\Pages\ViewRecord; 

class ViewAsset extends ViewRecord
{
    protected static string $resource = AssetResource::class;
    protected static string $view = 'filament.resources.asset-resource.pages.view-asset';

    // A propriedade '$record' agora é gerenciada automaticamente pela classe 'ViewRecord'
    // Não precisamos mais declará-la aqui.

    // Os métodos para buscar os dados para a view continuam os mesmos
    public function getChartData(): array
    {
        $history = $this->record->priceHistories()->orderBy('date')->get();

        return [
            'labels' => $history->pluck('date')->map(fn ($date) => $date->format('d/m/y')),
            'datasets' => [
                [
                    'label' => 'Cotação (R$)',
                    'data' => $history->pluck('price'),
                    'borderColor' => '#36A2EB',
                    'tension' => 0.1,
                ]
            ],
        ];
    }

    public function getTransactions()
    {
        return $this->record->transactions()->orderBy('transaction_date', 'desc')->get();
    }

    public function getDividends()
    {
        return $this->record->dividends()->orderBy('payment_date', 'desc')->get();
    }
}