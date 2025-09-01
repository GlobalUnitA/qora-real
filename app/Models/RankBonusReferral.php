<?php

namespace App\Models;

use App\Traits\TruncatesDecimals;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RankBonusReferral extends Model
{
    use HasFactory, TruncatesDecimals;

    protected $fillable = [
        'user_id',
        'bonus_id',
        'level',
        'self_sales',
        'group_sales',
    ];

    protected $casts = [
        'self_sales' => 'decimal:9',
        'group_sales' => 'decimal:9',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function rankBonus()
    {
        return $this->belongsTo(RankBonus::class, 'bonus_id', 'id');
    }

}
