<?php

namespace App\Filament\Pages;

use App\Models\Dividend;
use App\Models\Portfolio;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Form;

class DividendReport extends Page implements HasForms
{
    use InteractsWithForms;

    // ... Configurações da página (icon, view, title, etc.) ...
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    protected static string $view = 'filament.pages.dividend-report';
    protected static ?string $title = 'Relatório de Proventos';
    protected static ?string $navigationGroup = 'Relatórios';
    protected static ?int $navigationSort = 1;


    // Propriedades para guardar o estado dos filtros
    public ?int $portfolioId = null;
    public ?int $year = null; // <-- NOVA PROPRIEDADE PARA O ANO

    // Propriedades para os dados da tabela
    public array $pivotedData = [];
    public array $columnMonths = [];
    public array $monthlyTotals = [];

    public function mount(): void
    {
        // Define o ano atual como padrão ao carregar a página
        $this->year = now()->year;
        
        // Preenche o formulário com os valores iniciais
        $this->form->fill([
            'year' => $this->year,
        ]);
        
        $this->runReport();
    }
    
    // Define o formulário dos filtros
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Organiza os filtros lado a lado
                \Filament\Forms\Components\Grid::make(2)
                    ->schema([
                        Select::make('portfolioId')
                            ->label('Filtrar por Carteira')
                            ->options(Auth::user()->portfolios()->pluck('name', 'id'))
                            ->placeholder('Todas as Carteiras')
                            ->live()
                            ->afterStateUpdated(fn () => $this->runReport()),
                        
                        // NOVO FILTRO DE ANO
                        Select::make('year')
                            ->label('Filtrar por Ano')
                            ->options(function () {
                                // Busca dinamicamente todos os anos que têm proventos registrados
                                return Dividend::where('user_id', Auth::id())
                                    ->selectRaw('YEAR(payment_date) as year')
                                    ->distinct()
                                    ->orderBy('year', 'desc')
                                    ->pluck('year', 'year');
                            })
                            ->placeholder('Selecionar Ano')
                            ->default(now()->year)
                            ->live()
                            ->afterStateUpdated(fn () => $this->runReport()),
                    ])
            ]);
    }

    protected function runReport(): void
    {
        // 1. Inicia a busca de proventos
        $dividendsQuery = Dividend::query()
            ->where('user_id', Auth::id())
            ->with('asset');

        // 2. APLICA OS FILTROS SE ELES ESTIVEREM SELECIONADOS
        if ($this->portfolioId) {
            $dividendsQuery->whereHas('asset', function ($query) {
                $query->where('portfolio_id', $this->portfolioId);
            });
        }
        if ($this->year) {
            $dividendsQuery->whereYear('payment_date', $this->year);
        }

        $dividends = $dividendsQuery->get();

        // 3. O resto da lógica para pivotar os dados continua a mesma
        $pivoted = $dividends->groupBy('asset.ticker')
            ->map(function ($assetDividends) {
                return $assetDividends->groupBy(fn ($dividend) => $dividend->payment_date->format('Y-m'))
                    ->map(fn ($monthlyDividends) => $monthlyDividends->sum('total_received'));
            });

        $this->pivotedData = $pivoted->toArray();
        
        $this->columnMonths = $dividends
            ->map(fn ($dividend) => $dividend->payment_date->format('Y-m'))
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        $this->monthlyTotals = [];
        foreach ($this->columnMonths as $month) {
            $this->monthlyTotals[$month] = $pivoted->sum(fn ($assetData) => $assetData->get($month, 0));
        }
    }
}