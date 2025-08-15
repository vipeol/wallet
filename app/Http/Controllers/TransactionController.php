<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Asset $asset)
    {
        if ($asset->user_id != Auth::id()) {
            abort(403);
        }

        $transactions = $asset->transactions()->orderBy('transaction_date', 'desc')->get();
        
        return view('transactions.index', compact('transactions', 'asset'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Asset $asset)
    {
        if ($asset->user_id != Auth::id()) {
            abort(403);
        }   

        return view('transactions.create', compact('asset'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Asset $asset)
    {
        if ($asset->user_id != Auth::id()) {
            abort(403);
        }

        $validatedData = $request->validate([
            'type' => ['required', Rule::in(['buy', 'sell'])],
            'transaction_date' => ['required','date','before_or_equal:today'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'unit_price' => ['required', 'numeric', 'gt:0'],
        ]);

        $dateToSave = array_merge($validatedData, [
            'user_id' => Auth::id(),
        ]);

        $asset->transactions()->create($dateToSave);
        
        return redirect()->route('assets.transactions.index', $asset)->with('success', 'Transação registrada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id()) {
            abort(403);
        }

        return view('transactions.edit', compact('transaction'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id()) {
            abort(403);
        }

        $validatedData = $request->validate([
            'type' => ['required', Rule::in(['buy', 'sell'])],
            'transaction_date' => ['required','date','before_or_equal:today'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'unit_price' => ['required', 'numeric', 'gt:0'],
        ]);
        
        $transaction->update($validatedData);

        return redirect()->route('assets.transactions.index', $transaction->asset_id)->with('success', 'Transação atualizada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id()) {
            abort(403);
        }

        $assetId = $transaction->asset_id;
        $transaction->delete();

        return redirect()->route('assets.transactions.index', $assetId)->with('success', 'Transação excluída com sucesso.');
    }
}
