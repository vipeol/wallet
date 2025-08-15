<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use illuminate\Database\Eloquent\Factories\HasFactory;

class CorporateAction extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'asset_id',
        'type',
        'action_date',
        'details',
    ];

    protected $casts = [
        'action_date' => 'date',
        'details' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
