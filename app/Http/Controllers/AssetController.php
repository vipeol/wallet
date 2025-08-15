<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AssetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $portfolios = Auth::user()->portfolios()->with(['assets' => function($query) {
            $query->whereNotIn('type', ['currency', 'benchmark']);
        }])->get();

        $assetsWithoutPortfolio = Auth::user()->assets()
            ->whereNull('portfolio_id')
            ->whereNotIn('type', ['currency', 'benchmark'])
            ->get();

        // Calcula os totais gerais para não sobrecarregar a view
        $allAssets = Auth::user()->assets()->whereNotIn('type', ['currency', 'benchmark'])->get();

        // FILTRA AS COLEÇÕES PARA REMOVER ATIVOS COM POSIÇÃO ZERADA
        $portfolios->each(function ($portfolio) {
            $activeAssets = $portfolio->assets->filter(fn ($asset) => $asset->current_quantity > 0.00000001);
            $portfolio->setRelation('assets', $activeAssets);
        });
        $assetsWithoutPortfolio = $assetsWithoutPortfolio->filter(fn ($asset) => $asset->current_quantity > 0.00000001);
        $allAssets = $allAssets->filter(fn ($asset) => $asset->current_quantity > 0.00000001);

        $grandTotalMarketValue = $allAssets->sum('market_value');
        $grandTotalDayProfitLoss = $allAssets->sum('day_profit_loss');
        $grandTotalUnrealizedProfitLoss = $allAssets->sum('unrealized_profit_loss');
        $grandTotalDividendsReceived = $allAssets->sum('total_dividends_received');

        return view('assets.index', compact(
            'portfolios', 
            'assetsWithoutPortfolio',
            'grandTotalMarketValue',
            'grandTotalDayProfitLoss',
            'grandTotalUnrealizedProfitLoss',
            'grandTotalDividendsReceived'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $portfolios = Auth::user()->portfolios()->get();
        return view('assets.create', compact('portfolios'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'ticker' => ['required', 'string', 'uppercase', 'max:10', Rule::unique('assets')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                }),
            ],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['stock', 'fii', 'crypto', 'fixed_income', 'currency', 'benchmark'])],
            'logo_url' => ['nullable', 'string', 'max:255'], 
            'portfolio_id' => ['nullable', 'exists:portfolios,id'],
            'currency' => ['required', Rule::in(['BRL', 'USD'])],
            'target_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'], 
        ]);
    
        Auth::user()->assets()->create($validatedData);
        
        return redirect()->route('assets.index')->with('success', 'Ativo criado com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Asset $asset)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Asset $asset)
    {
        if ($asset->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado.');
        }
        $portfolios = Auth::user()->portfolios()->get();
        return view('assets.edit', compact('asset', 'portfolios'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Asset $asset)
    {
        if ($asset->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado.');
        }
        
        $validatedData = $request->validate([
            'ticker' => ['required', 'string', 'uppercase', 'max:10', Rule::unique('assets')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                })->ignore($asset->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['stock', 'fii', 'crypto', 'fixed_income', 'currency', 'benchmark'])],
            'logo_url' => ['nullable', 'string', 'max:255'],
            'portfolio_id' => ['nullable', 'exists:portfolios,id'],
            'currency' => ['required', Rule::in(['BRL', 'USD'])],
            'target_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $asset->update($validatedData);
        
        return redirect()->route('assets.index')->with('success', 'Ativo atualizado com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Asset $asset)
    {
        if ($asset->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado.');
        }

        $asset->delete();
        
        return redirect()->route('assets.index')->with('success', 'Ativo removido com sucesso.');
    }
}
