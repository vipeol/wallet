<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Dividend extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'asset_id',
        'payment_date',
        'amount_per_share',
        'record_date', // Data Com
        'ex_date',     // Data Ex
        'type',        // Tipo (Dividendo, Juros S/ Capital Próprio, Rendimentos)
    ];

    protected $casts = [
        'payment_date' => 'date',
        'record_date' => 'date',
        'ex_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    /** Assessor para calcular a posição que usuario tinha na data-ex  */
    protected function positionOnExDate(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Aqui você pode implementar a lógica para calcular a posição do usuário na data-ex
                // Por exemplo, buscar as transações do ativo até a data-ex e calcular a quantidade
                $transactionsBeforeExDate = $this->asset->transactions()
                    ->where('transaction_date', '<', $this->ex_date)
                    ->get();

                $buys = $transactionsBeforeExDate->where('type', 'buy')->sum('quantity');
                $sells = $transactionsBeforeExDate->where('type', 'sell')->sum('quantity');
                return $buys - $sells;
            }
        );
    }

    protected function totalReceived(): Attribute
    {
        return Attribute::make(
            get: function () {
                $totalInNativeCurrency = $this->position_on_ex_date * $this->amount_per_share;

                if ($this->asset->currency === 'USD') {
                    // Se o ativo for em USD, converte para BRL usando a taxa do dia do pagamento
                    $rate = PriceHistory::getUsdBrlRateOn($this->payment_date);
                    return $totalInNativeCurrency * $rate;
                }
                
                // Calcula o total recebido com base na quantidade de ações e no valor por ação
                return $totalInNativeCurrency;
            }
        );
    }

}
