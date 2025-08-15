<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Asset; // Importe o model Asset
use App\Models\PriceHistory;
use Carbon\Carbon;

class PriceHistorySeeder extends Seeder
{
    public function run(): void
    {
        // Encontra os ativos pelo ticker, em vez de usar IDs fixos
        $petr4 = Asset::where('ticker', 'PETR4')->first();
        $mxrf11 = Asset::where('ticker', 'MXRF11')->first();

        // Só continua se os ativos foram encontrados
        if ($petr4 && $mxrf11) {
            PriceHistory::create(['asset_id' => $petr4->id, 'date' => Carbon::yesterday(), 'price' => 38.50]);
            PriceHistory::create(['asset_id' => $petr4->id, 'date' => Carbon::today(), 'price' => 39.00]);

            PriceHistory::create(['asset_id' => $mxrf11->id, 'date' => Carbon::yesterday(), 'price' => 10.15]);
            PriceHistory::create(['asset_id' => $mxrf11->id, 'date' => Carbon::today(), 'price' => 10.18]);
        }

        $aapl = Asset::where('ticker', 'AAPL')->first();
        $usdbrl = Asset::where('ticker', 'USDBRL')->first();

        if ($aapl && $usdbrl) {
            // Histórico para AAPL (em USD)
            PriceHistory::create(['asset_id' => $aapl->id, 'date' => Carbon::yesterday(), 'price' => 170.00]);
            PriceHistory::create(['asset_id' => $aapl->id, 'date' => Carbon::today(), 'price' => 172.50]);

            // Histórico para o Dólar (em BRL)
            PriceHistory::create(['asset_id' => $usdbrl->id, 'date' => Carbon::yesterday(), 'price' => 5.40]);
            PriceHistory::create(['asset_id' => $usdbrl->id, 'date' => Carbon::today(), 'price' => 5.45]);
        }      
        
        $ibov = Asset::where('ticker', 'IBOV')->first();
        $ifix = Asset::where('ticker', 'IFIX')->first();

        if ($ibov && $ifix) {
            // Dados de teste para IBOV (valores em pontos)
            PriceHistory::create(['asset_id' => $ibov->id, 'date' => now()->subMonths(2), 'price' => 120000]);
            PriceHistory::create(['asset_id' => $ibov->id, 'date' => now()->subMonths(1), 'price' => 125000]);
            PriceHistory::create(['asset_id' => $ibov->id, 'date' => now(), 'price' => 128000]);

            // Dados de teste para IFIX (valores em pontos)
            PriceHistory::create(['asset_id' => $ifix->id, 'date' => now()->subMonths(2), 'price' => 3300]);
            PriceHistory::create(['asset_id' => $ifix->id, 'date' => now()->subMonths(1), 'price' => 3350]);
            PriceHistory::create(['asset_id' => $ifix->id, 'date' => now(), 'price' => 3400]);
        }
    }
}