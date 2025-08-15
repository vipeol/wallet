<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class Portfolio extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function snapshots()
    {
        return $this->hasMany(PortfolioSnapshot::class);
    }
    /**
     * Accessor para o VALOR DE MERCADO TOTAL da carteira.
     * Permite usar $portfolio->total_market_value
     */
    protected function totalMarketValue(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Soma o valor de mercado de todos os ativos associados, ignorando as "moedas"
                return $this->assets
                            ->whereNotIn('type', ['currency', 'benchmark'])
                            ->sum('market_value');
            }
        );
    }

}
