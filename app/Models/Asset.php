<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Collection;

class Asset extends Model
{
    use HasFactory;

    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'portfolio_id',
        'ticker',
        'name',
        'type',
        'logo_url', 
        'currency',
        'target_percentage', 
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }   

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function priceHistories() 
    {
        return $this->hasMany(PriceHistory::class);
    }

    public function dividends()
    {
        return $this->hasMany(Dividend::class);
    }

    public function portfolio()
    {
        return $this->belongsTo(Portfolio::class);
    }

    public function corporateActions()
    {
        return $this->hasMany(CorporateAction::class);
    }

    // Cache para armazenar posições calculadas e evitar consultas repetidas
    private ?array $positionsCache = null;

    private function calculatePosition(): array
    {
        if ($this->positionsCache !== null) {
            return $this->positionsCache;
        }
        
        $transactions = $this->transactions()->orderBy('transaction_date', 'asc')->get();
        $currentQuantity = 0;
        $totalAcquisitionCost = 0;

        foreach ($transactions as $transaction) {
            $costInBrl = $transaction->quantity * $transaction->unit_price;

            if ($this->currency === 'USD') {
                // Converte o custo para BRL usando a taxa do dia da transação
                $rate = PriceHistory::getUsdBrlRateOn($transaction->transaction_date);
                $costInBrl *= $rate;
            }

            if ($transaction->type === 'buy') {
                $currentQuantity += $transaction->quantity;
                $totalAcquisitionCost += $costInBrl;
            } elseif ($transaction->type === 'sell') {
                $averagePriceBeforeSell = $currentQuantity > 0 ? $totalAcquisitionCost / $currentQuantity : 0;
                $totalAcquisitionCost -= $transaction->quantity * $averagePriceBeforeSell;
                $currentQuantity -= $transaction->quantity;
                if ($currentQuantity <= 0) {
                    $totalAcquisitionCost = 0; 
                }
            }
        }

        $averagePrice = $currentQuantity > 0 ? $totalAcquisitionCost / $currentQuantity : 0;

        return $this->positionsCache = [
            'quantity' => $currentQuantity,
            'cost' => $totalAcquisitionCost,
            'price' => $averagePrice,
        ];
    }

    /** assessor para calcular a quantidade atual do ativo 
     * permite usar $asset->current_quantity na sua view
     */
    protected function currentQuantity(): Attribute
    {
        return Attribute::make(get: fn () => $this->calculatePosition()['quantity']);
    }

    /** assessor para calcular o preço médio da compra
     *  permite usar $asset->average_price na sua view
     */
    protected function averagePrice(): Attribute
    {
        return Attribute::make(get: fn () => $this->calculatePosition()['price']);
    }

    /**assessor para a cotação mais recente do ativo
     * permite usar $asset->latest_price na sua view
     */
    protected function latestPrice(): Attribute
    {
        return Attribute::make(
            get: function () {
                $latest = $this->priceHistories()->latest('date')->first();
                $latestPriceInNativeCurrency = $latest ? $latest->price : 0;

                if ($this->currency === 'USD') {
                    $rate = PriceHistory::getUsdBrlRateOn(today());
                    return $latestPriceInNativeCurrency * $rate;
                }
                return $latestPriceInNativeCurrency;
            }
        );
    }

    /** assessor para o valor de mercado atual do ativo 
     * permite usar $asset->market_value na sua view
    */
    protected function marketValue(): Attribute 
    {
        return Attribute::make(get: fn () => $this->current_quantity * $this->latest_price);
    }

    /** assessor que retorna o caminho publico do logo apenas se o arquivo existe 
     *  permite usar asset->logo_path na sua view
     */
    protected function logoPath(): Attribute 
    {
        return Attribute::make(
            get: function () {
                if ($this->logo_url)
                {   
                    $path = 'images/assets/' . $this->logo_url;
                    if (file_exists(public_path($path))) {
                        return asset($path);
                    }
                }
                return null;
            }
        );
    }

    /** assessor para o total de proventos recebidos 
     * permite usar $asset->totalDividendsReceived na sua view
     */
    protected function totalDividendsReceived(): Attribute
    {
        return Attribute::make(get: fn () => $this->dividends->sum('total_received'));
    }

    /** assessor para o custo de aquisição total
     *  permite usar $asset->total_acquisition_cost na sua view
     */
    protected function totalAcquisitionCost(): Attribute
    {
        return Attribute::make(get: fn () => $this->calculatePosition()['cost']);
    }

    /** assessor para yield on cost (proventos / custo) 
     *  permite usar $asset->yield_on_cost na sua view
    */
    protected function yieldOnCost(): Attribute
    {
        return Attribute::make(get: fn () => $this->total_acquisition_cost > 0 ? ($this->total_dividends_received / $this->total_acquisition_cost) * 100 : 0);
    }

    /** assessor para lucro/prejuizo não realizado (valor)
     * permite usar $asset->unrealized_profit_loss na sua view
     */
    protected function unrealizedProfitLoss(): Attribute
    {
        return Attribute::make(get: fn () => $this->market_value - $this->total_acquisition_cost);
    }

    /** assessor para lucro/prejuizo não realizado (percentual)
     * permite usar $asset->unrealized_profit_loss_percentage na sua view
     */
    protected function unrealizedProfitLossPercentage(): Attribute
    {
        return Attribute::make(get: fn () => $this->total_acquisition_cost > 0 ? ($this->unrealized_profit_loss / $this->total_acquisition_cost) * 100 : 0);
    }

    /**
     * Accessor para a COTAÇÃO DO DIA ANTERIOR.
     * Permite usar $asset->previous_day_price
     */
    protected function previousDayPrice(): Attribute
    {
        return Attribute::make(
            get: function () {
                $previous = $this->priceHistories()
                                ->where('date', '<', today())
                                ->latest('date')
                                ->first();
                
                $previousPriceInNativeCurrency = $previous ? $previous->price : 0;

                // Converte para BRL se for um ativo em USD
                if ($this->currency === 'USD') {
                    $rate = PriceHistory::getUsdBrlRateOn(today()->subDay());
                    return $previousPriceInNativeCurrency * $rate;
                }

                return $previousPriceInNativeCurrency;
            }
        );
    }

    /**
     * Accessor para o LUCRO/PREJUÍZO DO DIA (VALOR).
     * Permite usar $asset->day_profit_loss
     */
    protected function dayProfitLoss(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Variação do preço (hoje - ontem) multiplicada pela quantidade atual
                $priceVariation = $this->latest_price - $this->previous_day_price;
                return $priceVariation * $this->current_quantity;
            }
        );
    }

    /**
     * Accessor para o LUCRO/PREJUÍZO DO DIA (PERCENTUAL).
     * Permite usar $asset->day_profit_loss_percentage
     */
    protected function dayProfitLossPercentage(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Evita divisão por zero
                if ($this->previous_day_price > 0) {
                    return (($this->latest_price - $this->previous_day_price) / $this->previous_day_price) * 100;
                }
                return 0;
            }
        );
    }

    /**
     * Accessor para o PERCENTUAL ATUAL do ativo na carteira.
     */    
    protected function currentPortfolioPercentage(): Attribute
    {
        return Attribute::make(
            get: function () {
                $totalPortfolioValue = $this->portfolio?->total_market_value;

                if ($totalPortfolioValue > 0) {
                    return ($this->market_value / $totalPortfolioValue) * 100;
                }
                return 0;
            }
        );
    }        
    
    /**
     * Accessor para o AJUSTE NECESSÁRIO (em R$) para atingir o objetivo.
     */
    protected function adjustmentNeeded(): Attribute
    {
        return Attribute::make(
            get: function () {
                $totalPortfolioValue = $this->portfolio?->total_market_value;

                if ($totalPortfolioValue > 0) {
                    $targetValue = $totalPortfolioValue * ($this->target_percentage / 100);
                    return $targetValue - $this->market_value;
                }
                return 0;
            }
        );
    }

    /**
     * Accessor para o LUCRO/PREJUÍZO REALIZADO.
     * Permite usar $asset->realized_profit_loss
     */    
    public function realizedProfitLoss(): Attribute
    {
        return Attribute::make(
            get: function () {
                $buyTransactions = $this->transactions()->where('type', 'buy')->get();
                $sellTransactions = $this->transactions()->where('type', 'sell')->get();
                
                if ($sellTransactions->isEmpty()) {
                    return 0; // Sem vendas, sem lucro/prejuízo realizado
                }

                // Calcula o valor total recebido em BRL com as vendas.
                $totalSellValue = $sellTransactions->sum(function ($transaction) {
                    $valueInNativeCurrency = $transaction->quantity * $transaction->unit_price;
                    if ($this->currency === 'USD') {
                        // Converte o valor para BRL usando a taxa do dia da transação
                        $rate = PriceHistory::getUsdBrlRateOn($transaction->transaction_date);
                        return $valueInNativeCurrency * $rate;
                    }
                    return $valueInNativeCurrency;
                });

                // Calcula o custo total de TODAS as compras já feitas para este ativo.
                $totalBuyCost = $buyTransactions->sum(function ($transaction) {
                    $costInNativeCurrency = $transaction->quantity * $transaction->unit_price;
                    if ($this->currency === 'USD') {
                        // Converte o custo para BRL usando a taxa do dia da transação
                        $rate = PriceHistory::getUsdBrlRateOn($transaction->transaction_date);
                        return $costInNativeCurrency * $rate;
                    }
                    return $costInNativeCurrency;
                });

                // Lucro Realizado = Total de Vendas - Custo Total de Compras
                // (Isso está simplificado. A contabilidade exata usaria o preço médio no momento da venda,
                // mas para uma posição totalmente encerrada, esta é uma aproximação muito boa).
                return $totalSellValue - $totalBuyCost;
            }
        );
    }

    /**
     * Accessor para o PREÇO MÉDIO DE COMPRA HISTÓRICO (considera todas as compras).
     */
    protected function historicalAverageBuyPrice(): Attribute
    {
        return Attribute::make(
            get: function () {
                $buyTransactions = $this->transactions()->where('type', 'buy')->get();

                if ($buyTransactions->isEmpty()) {
                    return 0;
                }

                // Calcula o custo total de todas as compras em BRL
                $totalCostInBrl = $buyTransactions->sum(function ($transaction) {
                    $costInNativeCurrency = $transaction->quantity * $transaction->unit_price;
                    if ($this->currency === 'USD') {
                        $rate = PriceHistory::getUsdBrlRateOn($transaction->transaction_date);
                        return $costInNativeCurrency * $rate;
                    }
                    return $costInNativeCurrency;
                });

                $totalQuantity = $buyTransactions->sum('quantity');

                return $totalQuantity > 0 ? $totalCostInBrl / $totalQuantity : 0;
            }
        );
    }

    /**
     * Accessor para o PREÇO MÉDIO DE VENDA.
     */
    protected function averageSellPrice(): Attribute
    {
        return Attribute::make(
            get: function () {
                $sellTransactions = $this->transactions()->where('type', 'sell')->get();

                if ($sellTransactions->isEmpty()) {
                    return 0;
                }

                // Calcula o valor total recebido com as vendas em BRL
                $totalValueInBrl = $sellTransactions->sum(function ($transaction) {
                    $valueInNativeCurrency = $transaction->quantity * $transaction->unit_price;
                    if ($this->currency === 'USD') {
                        $rate = PriceHistory::getUsdBrlRateOn($transaction->transaction_date);
                        return $valueInNativeCurrency * $rate;
                    }
                    return $valueInNativeCurrency;
                });

                $totalQuantity = $sellTransactions->sum('quantity');

                return $totalQuantity > 0 ? $totalValueInBrl / $totalQuantity : 0;
            }
        );
    }

}
