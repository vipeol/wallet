<?php

namespace App\Filament\Pages;

use App\Models\PortfolioSnapshot;
use App\Models\Portfolio;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ProfitabilityMatrixReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';
    protected static string $view = 'filament.pages.profitability-matrix-report';
    protected static ?string $title = 'Matriz de Rentabilidade';
    protected static ?string $navigationGroup = 'Relatórios';
    protected static ?int $navigationSort = 3;

    public ?int $portfolioId = null;
    public array $reportData = [];

    public function mount(): void
    {
        // Se não houver filtro, seleciona a primeira carteira como padrão
        if (is_null($this->portfolioId)) {
            $this->portfolioId = Auth::user()->portfolios()->first()?->id;
        }
        $this->form->fill(['portfolioId' => $this->portfolioId]);
        $this->runReport();
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('portfolioId')
                    ->label('Filtrar por Carteira')
                    ->options(Auth::user()->portfolios()->pluck('name', 'id'))
                    // ->placeholder('Consolidado') // Consolidado fica para um próximo passo
                    ->live()
                    ->afterStateUpdated(fn () => $this->runReport()),
            ]);
    }

    protected function runReport(): void
    {
        $user = Auth::user();
        
        $query = PortfolioSnapshot::query()->where('user_id', $user->id);

        if ($this->portfolioId) {
            $query->where('portfolio_id', $this->portfolioId);
            $portfolioName = $user->portfolios()->find($this->portfolioId)->name;
        } else {
            $firstPortfolio = $user->portfolios()->first();
            if (!$firstPortfolio) {
                $this->reportData = [];
                return;
            }
            $query->where('portfolio_id', $firstPortfolio->id);
            $portfolioName = $firstPortfolio->name;
            $this->form->fill(['portfolioId' => $firstPortfolio->id]);
        }
        
        $snapshots = $query->orderBy('date')->get();

        if ($snapshots->isEmpty()) {
            $this->reportData = [];
            return;
        }

        // Agrupa os snapshots por ano e depois por número do mês, usando os objetos Carbon
        $groupedByYear = $snapshots->groupBy(fn ($snapshot) => $snapshot->date->year)
            ->map(function ($yearlySnapshots) {
                // Pega o último snapshot de cada mês
                return $yearlySnapshots->groupBy(fn($s) => $s->date->month)
                                    ->map(fn($monthlySnaps) => $monthlySnaps->last());
            });

        $yearlyData = [];
        foreach ($groupedByYear as $year => $monthlySnapshots) {
            $monthlyReturns = array_fill(1, 12, null);
            
            // Pega o valor da cota do último snapshot do ano ANTERIOR
            $lastKnownCota = $groupedByYear->get($year - 1)?->last()->cota_value ?? 100;
            
            // Itera sobre os meses que TÊM dados para aquele ano
            foreach ($monthlySnapshots->sortKeys() as $monthNumber => $snapshot) {
                $currentCota = $snapshot->cota_value;
                
                if ($lastKnownCota > 0) {
                    $monthlyReturns[$monthNumber] = (($currentCota / $lastKnownCota) - 1) * 100;
                } else {
                    $monthlyReturns[$monthNumber] = 0;
                }
                $lastKnownCota = $currentCota;
            }
            
            $startOfYearCota = $groupedByYear->get($year - 1)?->last()->cota_value ?? 100;
            $endOfYearCota = $monthlySnapshots->last()->cota_value;
            
            $yearlyData[$year] = [
                'months' => $monthlyReturns,
                'total_year' => $startOfYearCota > 0 ? (($endOfYearCota / $startOfYearCota) - 1) * 100 : 0,
                'accumulated' => (($endOfYearCota / 100) - 1) * 100,
            ];
        }
        
        $this->reportData = [$portfolioName => $yearlyData];
    }

    // Função auxiliar para calcular o snapshot consolidado
    private function calculateConsolidatedSnapshots(Collection $allSnapshots): Collection
    {
        return $allSnapshots->groupBy(fn($s) => $s->date->toDateString())
            ->map(function ($dailySnapshots) {
                $first = $dailySnapshots->first();
                if(!$first) return null;
                $consolidated = new PortfolioSnapshot([
                    'date' => $first->date,
                    'user_id' => $first->user_id,
                ]);
                
                $totalMarketValue = $dailySnapshots->sum('market_value');
                $totalInitialInvestment = $dailySnapshots->sum('total_cotas') * 100; // Aproximação do capital investido
                
                if ($totalInitialInvestment > 0) {
                    $consolidated->cota_value = ($totalMarketValue / $totalInitialInvestment) * 100;
                } else {
                    $consolidated->cota_value = 100;
                }

                return $consolidated;
            })->filter()->values();
    }
}