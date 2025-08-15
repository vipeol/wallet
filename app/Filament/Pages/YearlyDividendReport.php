<?php

namespace App\Filament\Pages;

use App\Models\Dividend;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Form;

class YearlyDividendReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static string $view = 'filament.pages.yearly-dividend-report';
    protected static ?string $title = 'Relatório de Proventos Anual';
    protected static ?string $navigationGroup = 'Relatórios';
    protected static ?int $navigationSort = 2;

    public ?int $portfolioId = null;
    public array $pivotedData = [];

    public function mount(): void
    {
        $this->form->fill();
        $this->runReport();
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('portfolioId')
                    ->label('Filtrar por Carteira')
                    ->options(Auth::user()->portfolios()->pluck('name', 'id'))
                    ->placeholder('Todas as Carteiras')
                    ->live()
                    ->afterStateUpdated(fn () => $this->runReport()),
            ]);
    }

    protected function runReport(): void
    {
        $dividendsQuery = Dividend::query()
            ->where('user_id', Auth::id())
            ->with('asset');

        if ($this->portfolioId) {
            $dividendsQuery->whereHas('asset', function ($query) {
                $query->where('portfolio_id', $this->portfolioId);
            });
        }

        $dividends = $dividendsQuery->orderBy('payment_date', 'desc')->get();

        // Pivota os dados, primeiro por ano, depois por mês
        $this->pivotedData = $dividends
            ->groupBy(fn ($dividend) => $dividend->payment_date->format('Y'))
            ->map(function ($yearlyDividends) {
                return $yearlyDividends->groupBy(fn ($dividend) => $dividend->payment_date->format('m'))
                    ->map(fn ($monthlyDividends) => $monthlyDividends->sum('total_received'));
            })
            ->toArray();
    }
}