<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Services\AssetService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CorporateActionController extends Controller
{
    public function index(Asset $asset)
    {
        if ($asset->user_id !== Auth::id()) {
            abort(403);
        }
        $history = $asset->corporateActions()->orderBy('action_date', 'desc')->get();

        return view('corporate-actions.index', compact('asset', 'history'));
    }

    public function store(Request $request, Asset $asset, AssetService $assetService)
    {
        if ($asset->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'type' => 'required|in:split,reverse_split,ticker_change',
            'action_date' => 'required|date',
            'split_from' => 'required_if:type,split,reverse_split|integer|min:1',
            'split_to' => 'required_if:type,split,reverse_split|integer|min:1',
            'new_ticker' => 'required_if:type,ticker_change|string|uppercase|max:10',
        ]);

        $date = Carbon::parse($validated['action_date']);

        switch ($validated['type']) {
            case 'split':
            case 'reverse_split':
                $assetService->handleSplit($asset, $validated['split_from'], $validated['split_to'], $date);
                $details = ['from' => $validated['split_from'], 'to' => $validated['split_to']];
                break;
            case 'ticker_change':
                $details = ['from' => $asset->ticker, 'to' => $validated['new_ticker']];
                $assetService->handleTickerChange($asset, $validated['new_ticker']);
                break;
        }

        $asset->corporateActions()->create([
            'user_id' => Auth::id(),
            'type' => $validated['type'],
            'action_date' => $date,
            'details' => $details,
        ]);

        return back()->with('success', 'Evento corporativo registrado e aplicado com sucesso!');
    }
}