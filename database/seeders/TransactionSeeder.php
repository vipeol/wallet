<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Asset;
use App\Models\Transaction;
use Carbon\Carbon;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $petr4 = Asset::where('ticker', 'PETR4')->first();
        $mxrf11 = Asset::where('ticker', 'MXRF11')->first();

        if ($user && $petr4 && $mxrf11) {
            // Transações para PETR4
            Transaction::create([
                'user_id' => $user->id,
                'asset_id' => $petr4->id,
                'type' => 'buy',
                'transaction_date' => Carbon::parse('2024-05-10'),
                'quantity' => 100,
                'unit_price' => 35.50,
            ]);

            // Transações para MXRF11
            Transaction::create([
                'user_id' => $user->id,
                'asset_id' => $mxrf11->id,
                'type' => 'buy',
                'transaction_date' => Carbon::parse('2024-06-15'),
                'quantity' => 50,
                'unit_price' => 10.20,
            ]);

            Transaction::create([
                'user_id' => $user->id,
                'asset_id' => $mxrf11->id,
                'type' => 'buy',
                'transaction_date' => Carbon::parse('2024-07-20'),
                'quantity' => 50,
                'unit_price' => 10.40,
            ]);
        }
    }
}