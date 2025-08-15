<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortfolioAllocationController extends Controller
{
    public function index()
    {
        $portfolios = Auth::user()->portfolios()->with('assets')->get();
    
        $portfolios->each(function ($portfolio) {
            $investableAssets = $portfolio->assets
                ->whereNotIn('type', ['currency', 'benchmark']);
            $sortedAssets = $investableAssets->sortBy('adjustment_needed');
            $portfolio->setRelation('assets', $sortedAssets);   
        });

        return view('portfolios.allocation', compact('portfolios'));
    }
}
