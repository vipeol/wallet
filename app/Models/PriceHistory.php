<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class PriceHistory extends Model
{
    use HasFactory;
    
    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'asset_id',
        'date',
        'price',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public static function getUsdBrlRateOn(Carbon $date)
    {
        // Tenta encontrar o ativo do dólar americano na data exata
        static $usdAsset = null;
        if (is_null($usdAsset)) {
            $usdAsset = Asset::where('ticker', 'USDBRL')->first();
        }

        if (!$usdAsset) {
            return 1.0; // Ativo do dólar não encontrado
        }

        $rate = self::where('asset_id', $usdAsset->id)
            ->where('date', $date->toDateString())
            ->first();

        // se não encontrar a taxa exata, tenta a data anterior mais recente
        if (!$rate) {
            $rate = self::where('asset_id', $usdAsset->id)
                ->where('date', '<', $date->toDateString())
                ->latest('date')
                ->first();
        }

        return $rate ? (float)$rate->price : 1.0; // Retorna a taxa ou 1 se não encontrar
    }

}
