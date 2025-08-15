<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // A ordem é crucial!
        // 1. Garante que um usuário exista
        $this->call(UserSeeder::class);

        // 2. Cria os Ativos para o usuário
        $this->call(AssetSeeder::class);

        // 3. Cria as Transações para os ativos existentes
        $this->call(TransactionSeeder::class);

        // 4. Cria o Histórico de Preços para os ativos existentes
        $this->call(PriceHistorySeeder::class);
    }
}
