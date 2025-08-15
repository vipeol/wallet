<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClosedTradeController extends Controller
{
    public function index()
    {
        // Pega todos os ativos do usuário que já tiveram transações
        $allAssets = Auth::user()->assets()->whereNotIn('type', ['currency', 'benchmark'])->has('transactions')->get();

        // Filtra para manter apenas aqueles com posição atual zerada ou negativa
        $closedAssets = $allAssets->filter(fn ($asset) => $asset->current_quantity <= 0.00000001);

        return view('trades.closed', compact('closedAssets'));
    }
}