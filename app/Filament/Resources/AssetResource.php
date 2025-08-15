<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssetResource\Pages;
use App\Models\Asset;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Builder; 
use Filament\Tables\Columns\Summarizers\Sum; 
use Filament\Tables\Grouping\Group;  
use Filament\Tables\Columns\Summarizers\Summarizer; 
use Illuminate\Database\Query\Builder as QueryBuilder; 


class AssetResource extends Resource
{
    protected static ?string $model = Asset::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Meus Ativos';
    protected static ?string $modelLabel = 'Ativo';
    protected static ?int $navigationSort = 1;

    // Define o formulário de criação e edição
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('portfolio_id')
                    ->relationship('portfolio', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Carteira'),
                Forms\Components\TextInput::make('ticker')
                    ->required()->maxLength(10),
                Forms\Components\TextInput::make('name')
                    ->required()->maxLength(255)->label('Nome'),
                Forms\Components\Select::make('type')
                    ->required()
                    ->options([
                        'stock' => 'Ação (Stock)',
                        'fii' => 'Fundo Imobiliário (FII)',
                        'crypto' => 'Criptomoeda',
                        'fixed_income' => 'Renda Fixa',
                        'currency' => 'Moeda',
                        'benchmark' => 'Índice/Benchmark',
                    ]),
                Forms\Components\Select::make('currency')
                    ->required()
                    ->options(['BRL' => 'Real (BRL)', 'USD' => 'Dólar (USD)'])
                    ->default('BRL'),
                Forms\Components\TextInput::make('logo_url')
                    ->label('Nome do Arquivo do Logo'),
                Forms\Components\TextInput::make('target_percentage')
                    ->label('Percentual Objetivo (%)')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->maxValue(100),
            ]);
    }

    // Define a tabela de listagem
    public static function table(Table $table): Table
    {
        return $table
            // Garante que a tabela só mostre os ativos do usuário logado
            ->modifyQueryUsing(fn (Builder $query) => $query->where('user_id', Auth::id())->whereNotIn('type', ['currency', 'benchmark']))
            ->columns([
                Tables\Columns\ImageColumn::make('logo_path')->label('')->circular(),
                Tables\Columns\TextColumn::make('ticker')->label('Ticker')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('current_quantity')->label('Posição')->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.')->alignEnd()->sortable(),
                
                // COLUNAS QUE ESTAVAM FALTANDO
                Tables\Columns\TextColumn::make('average_price')->label('Preço Médio')->money('BRL')->alignEnd()->sortable(),
                Tables\Columns\TextColumn::make('latest_price')->label('Cotação Atual')->money('BRL')->alignEnd()->sortable(),
                Tables\Columns\TextColumn::make('market_value')->label('Valor Mercado')->money('BRL')->alignEnd()->sortable()
                    ->summarize(Summarizer::make()
                        ->label('Total Mercado')
                        ->using(function (QueryBuilder $query): float {
                            $ids = $query->pluck('id');
                            return Asset::whereIn('id', $ids)->get()->sum('market_value');
                            })
                        ->money('BRL')  
                    ),
                Tables\Columns\TextColumn::make('total_acquisition_cost')->label('Custo Total')->money('BRL')->alignEnd()->sortable(),

                ColumnGroup::make('Var. Dia', [
                    Tables\Columns\TextColumn::make('day_profit_loss')->label('R$')->money('BRL')->alignEnd()->sortable()
                    ->summarize(Summarizer::make()
                        ->label('Total Dia')
                        // Usa o apelido QueryBuilder aqui
                        ->using(function (QueryBuilder $query): string {
                            $total = Asset::whereIn('id', $query->pluck('id'))->get()->sum('day_profit_loss');
                            return number_format($total, 2, ',', '.');
                        }))
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger'),
                    Tables\Columns\TextColumn::make('day_profit_loss_percentage')->label('%')->numeric(2, ',', '.')->suffix('%')->alignEnd()->sortable()->color(fn ($state) => $state >= 0 ? 'success' : 'danger'),
                ])->alignment(Alignment::Center),

                // Colunas de Lucro/Prejuízo (já existentes)
                ColumnGroup::make('Lucro/Prejuízo', [
                    Tables\Columns\TextColumn::make('unrealized_profit_loss')->label('L/P (R$)')->money('BRL')->alignEnd()->sortable()
                    ->summarize(Summarizer::make()
                        ->label('Total L/P')
                        // Usa o apelido QueryBuilder aqui
                        ->using(function (QueryBuilder $query): string {
                            $total = Asset::whereIn('id', $query->pluck('id'))->get()->sum('unrealized_profit_loss');
                            return number_format($total, 2, ',', '.');
                        }))
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger'),
                    Tables\Columns\TextColumn::make('unrealized_profit_loss_percentage')->label('L/P (%)')->numeric(2, ',', '.')->suffix('%')->alignEnd()->sortable()->color(fn ($state) => $state >= 0 ? 'success' : 'danger'),
                ])->alignment(Alignment::Center),

                // COLUNAS DE PROVENTOS QUE ESTAVAM FALTANDO
                ColumnGroup::make('Proventos', [
                    Tables\Columns\TextColumn::make('total_dividends_received')->label('R$')->money('BRL')->alignEnd()->sortable()
                    ->summarize(Summarizer::make()
                        ->label('Total Dividendos')
                        // Usa o apelido QueryBuilder aqui
                        ->using(function (QueryBuilder $query): string {
                            $total = Asset::whereIn('id', $query->pluck('id'))->get()->sum('total_dividends_received');
                            return number_format($total, 2, ',', '.');
                        }))
                    ->color('primary'),
                    Tables\Columns\TextColumn::make('yield_on_cost')->label('YoC (%)')->numeric(2, ',', '.')->suffix('%')->alignEnd()->sortable()->color('primary'),
                ])->alignment(Alignment::Center),
            ])
            ->filters([
                // Filtros podem ser adicionados aqui no futuro
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Action::make('Proventos')
                        ->url(fn (Asset $record): string => route('assets.dividends.index', $record))
                        ->icon('heroicon-o-banknotes'),
                    Action::make('Transações')
                        ->url(fn (Asset $record): string => route('assets.transactions.index', $record))
                        ->icon('heroicon-o-arrows-right-left'),
                    Action::make('Eventos')
                        ->url(fn (Asset $record): string => route('assets.corporate-actions.index', $record))
                        ->icon('heroicon-o-cog-6-tooth'),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            // O AGRUPAMENTO EXPANSÍVEL
            ->defaultGroup('portfolio.name');

    }
    
    public static function getRelations(): array
    {
        return [
            // Relações (como uma tabela de transações na página de edição) podem ser adicionadas aqui
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssets::route('/'),
            'create' => Pages\CreateAsset::route('/create'),
            'edit' => Pages\EditAsset::route('/{record}/edit'),
            'view' => Pages\ViewAsset::route('/{record}'),
        ];
    }    
}