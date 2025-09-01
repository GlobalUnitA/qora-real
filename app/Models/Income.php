<?php

namespace App\Models;

use App\Traits\TruncatesDecimals;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Vinkla\Hashids\Facades\Hashids;
use Carbon\Carbon;

class Income extends Model
{
    use HasFactory, TruncatesDecimals;

    protected $fillable = [
        'user_id',
        'coin_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:9',
    ];

     protected $appends = [
        'encrypted_id', 
        'fee_rate', 
        'tax_rate',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function coin()
    {
        return $this->belongsTo(Coin::class, 'coin_id', 'id');
    }

    public function transfers()
    {
        return $this->hasMany(IncomeTransfer::class, 'income_id', 'id');
    }

    public function getEncryptedIdAttribute()
    {
        return Hashids::encode($this->id);
    }

    public function getFeeRateAttribute()
    {
        $policy = AssetPolicy::first();

        if (!$policy) {
            return 0;
        }
        
        return $policy->fee_rate;
    }
    
    public function getTaxRateAttribute()
    {
        $policy = AssetPolicy::first();

        if (!$policy) {
            return 0;
        }

        return $policy->tax_rate;
    }

    public function getIncomeInfo()
    {
        $user_profile = UserProfile::where('user_id', $this->user_id)->first();

        $incomeTransfers = IncomeTransfer::where('income_id', $this->id)->get();

        $deposits =  $incomeTransfers->where('type', 'deposit')->where('status', 'completed');
        $deposit_total = $deposits->sum('amount');
        
        $withdrawals = $incomeTransfers->where('type', 'withdrawal')->where('status', 'completed');
        $withdrawal_total = $withdrawals->sum('amount');

        $self_profits = $incomeTransfers->where('type', 'trading_profit')->where('status', 'completed');
        $self_total = $self_profits->sum('amount');

        $subscription_bonus = $incomeTransfers->where('type', 'subscription_bonus')->where('status', 'completed');
        $subscription_bonus_total = $subscription_bonus->sum('amount');

        $referral_bonus = $incomeTransfers->where('type', 'referral_bonus')->where('status', 'completed');
        $referral_bonus_total = $referral_bonus->sum('amount');

        $rank_bonus = $incomeTransfers->where('type', 'rank_bonus')->where('status', 'completed');
        $rank_bonus_total = $rank_bonus->sum('amount');

        $rewards = $incomeTransfers->where('type', 'staking_reward')->where('status', 'completed');
        $reward_total = $rewards->sum('amount');

        return [
            'encrypted_id' => $this->encrypted_id,
            'coin_name' => $this->coin->name,
            'balance' => $this->balance,
            'profit' => $self_total,
            'subscription_bonus' => $subscription_bonus_total,
            'referral_bonus' => $referral_bonus_total,
            'rank_bonus' => $rank_bonus_total,
            'reward' => $reward_total,
            'deposit_total' => $deposit_total,
            'withdrawal_total' => $withdrawal_total,
        ];
    }
    
}
