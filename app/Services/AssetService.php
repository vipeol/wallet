<?php

namespace App\Services;

use App\Models\Asset;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AssetService
{
    /**
     * Lida com um desdobramento (split) ou grupamento (reverse split).
     * Ex: Split 2 para 1: $from = 1, $to = 2
     * Ex: Grupamento 1 para 10: $from = 10, $to = 1
     */
    public function handleSplit(Asset $asset, int $from, int $to, Carbon $date): void
    {
        DB::transaction(function () use ($asset, $from, $to, $date) {
            // Pega todas as transações ANTERIORES à data do evento
            $transactions = $asset->transactions()->where('transaction_date', '<', $date->toDateString())->get();

            foreach ($transactions as $transaction) {
                // Ajusta a quantidade e o preço unitário
                $newQuantity = ($transaction->quantity / $from) * $to;
                $newUnitPrice = ($transaction->unit_price / $to) * $from;

                // Atualiza a transação sem disparar eventos, para performance
                DB::table('transactions')->where('id', $transaction->id)->update([
                    'quantity' => $newQuantity,
                    'unit_price' => $newUnitPrice,
                ]);
            }
        });
    }

    /**
     * Lida com uma alteração de ticker.
     */
    public function handleTickerChange(Asset $asset, string $newTicker): void
    {
        $asset->update(['ticker' => $newTicker]);
    }
}