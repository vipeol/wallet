<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PortfolioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $portfolios = Auth::user()->portfolios()->orderBy('name')->get();
        
        //if ($portfolios->isEmpty()) {
        //    return redirect()->route('portfolios.create')->with('message', 'No portfolios found. Please create one.');
        //}

        return view('portfolios.index', compact('portfolios'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('portfolios.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('portfolios')->where(function ($query) {
                return $query->where('user_id', Auth::id());
            })],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        Auth::user()->portfolios()->create($validatedData);
        
        return redirect()->route('portfolios.index')->with('message', 'Carteira criada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Portfolio $portfolio)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Portfolio $portfolio)
    {
        if ($portfolio->user_id !== Auth::id()) {
            abort(403,'Acesso não autorizado.');
        }

        return view('portfolios.edit', compact('portfolio'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Portfolio $portfolio)
    {
        if ($portfolio->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado.');
        }
        
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('portfolios')->where(function ($query) {
                return $query->where('user_id', Auth::id());
            })->ignore($portfolio->id)],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $portfolio->update($validatedData);
        
        return redirect()->route('portfolios.index')->with('message', 'Carteira atualizada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Portfolio $portfolio)
    {
        if ($portfolio->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado.');
        }

        $portfolio->delete();
        
        return redirect()->route('portfolios.index')->with('message', 'Carteira excluída com sucesso.');
    }
}
