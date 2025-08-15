<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PortfolioSnapshotController extends Controller
{
    public function index(Request $request)
    {
        // Valida a data recebida do formulário, se não houver, usa o último dia do ano anterior
        $validated = $request->validate([
            'snapshot_date' => 'nullable|date',
        ]);
        $snapshotDate = isset($validated['snapshot_date']) ? Carbon::parse($validated['snapshot_date']) : Carbon::now()->subYear()->endOfYear();

        // Pega todos os ativos de investimento do usuário
        $assets = Auth::user()->assets()->whereNotIn('type', ['currency', 'benchmark'])->get();

        // Array para guardar os dados calculados do snapshot
        $snapshotData = [];
        $grandTotal = 0;

        foreach ($assets as $asset) {
            $quantityOnDate = $asset->getQuantityOn($snapshotDate);

            // Só exibe o ativo se o usuário tinha alguma posição nele na data
            if ($quantityOnDate > 0) {
                $marketValueOnDate = $asset->getMarketValueOn($snapshotDate);
                $grandTotal += $marketValueOnDate;

                $snapshotData[] = [
                    'ticker' => $asset->ticker,
                    'logo_path' => $asset->logo_path,
                    'quantity' => $quantityOnDate,
                    'average_price' => $asset->getAveragePriceOn($snapshotDate),
                    'latest_price' => $asset->getLatestPriceOn($snapshotDate),
                    'market_value' => $marketValueOnDate,
                ];
            }
        }

        // Agrupa os dados por carteira para exibição
        // (Esta parte é uma simplificação, idealmente seria feita com uma query mais complexa)
        $portfolios = Auth::user()->portfolios()->with('assets')->get();

        return view('portfolios.snapshot', [
            'snapshotData' => collect($snapshotData)->sortBy('ticker')->groupBy(function($item) use ($portfolios) {
                $asset = $portfolios->pluck('assets')->flatten()->firstWhere('ticker', $item['ticker']);
                return $asset->portfolio?->name ?? 'Sem Carteira';
            }),
            'snapshotDate' => $snapshotDate,
            'grandTotal' => $grandTotal,
        ]);
    }
}