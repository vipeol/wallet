<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\DividendController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\PortfolioAllocationController;
use App\Http\Controllers\PortfolioSnapshotController;
use App\Http\Controllers\CorporateActionController;
use App\Http\Controllers\ClosedTradeController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('assets', AssetController::class);
    Route::resource('assets.transactions', TransactionController::class)->shallow();
    Route::resource('assets.dividends', DividendController::class)->shallow();
    Route::resource('portfolios', PortfolioController::class)->shallow();
    Route::get('portfolio-allocation', [PortfolioAllocationController::class, 'index'])->name('portfolios.allocation');
    Route::get('portfolio-snapshot', [PortfolioSnapshotController::class, 'index'])->name('portfolios.snapshot');
    Route::get('assets/{asset}/corporate-actions', [CorporateActionController::class, 'index'])->name('assets.corporate-actions.index');
    Route::post('assets/{asset}/corporate-actions', [CorporateActionController::class, 'store'])->name('assets.corporate-actions.store');
    Route::get('closed-trades', [ClosedTradeController::class, 'index'])->name('trades.closed');
});

require __DIR__.'/auth.php';
