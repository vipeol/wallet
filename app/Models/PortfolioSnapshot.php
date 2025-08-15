<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PortfolioSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'portfolio_id',
        'user_id',
        'date',
        'market_value',
        'total_cotas',
        'cota_value',
        'total_acquisition_cost',
        'unrealized_profit_loss',
    ];

    /**
     * ADICIONE ESTA PROPRIEDADE
     * Converte os atributos do banco para os tipos corretos.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'date', // Converte a coluna 'date' para um objeto Carbon
    ];    

    public function user() {
        return $this->belongsTo(User::class);
    }   

    public function portfolio() {
        return $this->belongsTo(Portfolio::class);
    }
}
