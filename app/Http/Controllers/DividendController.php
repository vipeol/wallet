<?php

namespace App\Http\Controllers;

use App\Models\Dividend;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DividendController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Asset $asset)
    {
        if ($asset->user_id !== Auth::id()) {
            abort(403);
        }

        $dividends = $asset->dividends()->orderBy('payment_date', 'desc')->get();
        
        return view('dividends.index', compact('asset', 'dividends'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Asset $asset)
    {
        if ($asset->user_id !== Auth::id()) {
            abort(403);
        }   

        return view('dividends.create', compact('asset'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Asset $asset)
    {
        if ($asset->user_id !== Auth::id()) {
            abort(403);
        }

        $validatedData = $request->validate([
            'payment_date' => 'required|date|after_or_equal:ex_date',
            'ex_date' => 'required|date|after_or_equal:record_date',
            'record_date' => 'required|date',
            'type' => ['required', Rule::in(['DIV', 'JSCP', 'REN'])],
            'amount_per_share' => 'required|numeric|gt:0',
        ]);

        $dataToSave = array_merge($validatedData, ['user_id' => Auth::id()]);
        
        $asset->dividends()->create($dataToSave);
        return redirect()->route('assets.dividends.index', $asset)->with('success', 'Provento cadastrado com sucesso!');        
    }

    /**
     * Display the specified resource.
     */
    public function show(Dividend $dividend)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Dividend $dividend)
    {
        if ($dividend->user_id !== Auth::id()) {
            abort(403);
        }

        return view('dividends.edit', compact('dividend'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Dividend $dividend)
    {
        if ($dividend->user_id !== Auth::id()) {
            abort(403);
        }

        $validatedData = $request->validate([
            'payment_date' => 'required|date|after_or_equal:ex_date',
            'ex_date' => 'required|date|after_or_equal:record_date',
            'record_date' => 'required|date',
            'type' => ['required', Rule::in(['DIV', 'JSCP', 'REN'])],
            'amount_per_share' => 'required|numeric|gt:0',
        ]);

        $dividend->update($validatedData);
        return redirect()->route('assets.dividends.index', $dividend->asset_id)->with('success', 'Provento atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dividend $dividend)
    {
        if ($dividend->user_id !== Auth::id()) {
            abort(403);
        }

        $assetId = $dividend->asset_id;
        $dividend->delete();
        return redirect()->route('assets.dividends.index', $assetId)->with('success', 'Provento excluído com sucesso!');
    }
}
