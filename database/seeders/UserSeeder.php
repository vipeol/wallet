<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Cria um usuário de teste se ele não existir
        User::firstOrCreate(
            ['email' => 'teste@exemplo.com'],
            [
                'name' => 'Usuário Teste',
                'password' => Hash::make('password'),
            ]
        );
    }
}