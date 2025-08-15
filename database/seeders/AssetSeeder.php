<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Asset;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        // Pega o primeiro usuário criado (geralmente pelo Breeze)
        $user = User::first();

        if ($user) {
            Asset::create([
                'user_id' => $user->id,
                'ticker' => 'PETR4',
                'name' => 'Petrobras PN',
                'type' => 'stock',
                'logo_url' => 'petr4.jpg', 
            ]);

            Asset::create([
                'user_id' => $user->id,
                'ticker' => 'MXRF11',
                'name' => 'Maxi Renda FII',
                'type' => 'fii',
                'logo_url' => 'mxrf11.jpg', 
            ]);

            // Ativo americano de exemplo
            Asset::create([
                'user_id' => $user->id,
                'ticker' => 'AAPL',
                'name' => 'Apple Inc.',
                'type' => 'stock',
                'currency' => 'USD', // <-- Moeda em USD
                'logo_url' => 'aapl.png',
            ]);

            // "Ativo" para guardar a cotação do dólar
            Asset::create([
                'user_id' => $user->id,
                'ticker' => 'USDBRL',
                'name' => 'Dólar Americano',
                'type' => 'currency', 
                'currency' => 'BRL',
            ]);
            Asset::create([ 
                'user_id' => $user->id, 
                'ticker' => 'IBOV', 
                'name' => 'Índice Bovespa', 
                'type' => 'benchmark', 
                'currency' => 'BRL' 
            ]);
            Asset::create([ 
                'user_id' => $user->id, 
                'ticker' => 'IFIX', 
                'name' => 'Índice de Fundos Imobiliários', 
                'type' => 'benchmark', 
                'currency' => 'BRL' 
            ]);
        }
    }
}