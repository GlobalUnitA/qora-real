<?php

namespace App\Models;

use App\Traits\TruncatesDecimals;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class IncomeTransfer extends Model
{
    use HasFactory, TruncatesDecimals;

    protected $fillable = [
        'user_id',
        'income_id',
        'type',
        'status',
        'amount',
        'tax',
        'fee',
        'actual_amount',
        'before_balance',
        'after_balance',
        'memo',
    ];

    protected $casts = [
        'amount' => 'decimal:9',
        'tax' => 'decimal:9',
        'fee' => 'decimal:9',
        'actual_amount' => 'decimal:9',
        'before_balance' => 'decimal:9',
        'after_balance' => 'decimal:9',
    ];

    protected $appends = [
        'status_text',
        'type_text',
        'waiting_period',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function income()
    {
        return $this->belongsTo(Income::class, 'income_id', 'id');
    }

    public function profit()
    {
        return $this->hasOne(TradingProfit::class, 'transfer_id', 'id');
    }

    public function subscriptionBonus()
    {
        return $this->hasOne(SubscriptionBonus::class, 'transfer_id', 'id');
    }

    public function referralBonus()
    {
        return $this->hasOne(ReferralBonus::class, 'transfer_id', 'id');
    }

    public function rankBonus()
    {
        return $this->hasOne(RankBonus::class, 'transfer_id', 'id');
    }

    public function reward()
    {
        return $this->hasOne(StakingReward::class, 'transfer_id', 'id');
    }

    public function getTypeTextAttribute()
    {
        switch ($this->type) {
            case 'deposit' :
                return __('asset.internal_transfer');
                break;
            case 'withdrawal' :
                return __('asset.external_withdrawal');
                break;
            case 'trading_profit' :
                return __('asset.trading_profit');
                break;
            case 'subscription_bonus' :
                return __('asset.subscription_bonus');
                break;
            case 'referral_bonus' :
                return __('asset.referral_bonus');
                break;
            case 'rank_bonus' :
                return __('asset.rank_bonus');
                break;
            case 'staking_reward' :
                return __('asset.staking_profit');
                break;
        }
    }

    public function getStatusTextAttribute()
    {

        $deposit_status_map = [
            'pending' => __('system.apply'),
            'waiting' => __('system.waiting'),
            'completed' => __('system.completed'),
            'canceled' => __('system.cancel'),
            'refunded' => __('system.refund'),
        ];

        $withdrawal_status_map = [
            'pending' => __('system.apply'),
            'completed' => __('system.completed'),
            'canceled' => __('system.cancel'),
        ];

        if ($this->type === 'deposit') {
            return $deposit_status_map[$this->status];
        } else if ($this->type === 'withdrawal') {
            return $withdrawal_status_map[$this->status];
        }
    }

    public function getWaitingPeriodAttribute()
    {
        $created_at = $this->created_at;
        $internal_period = AssetPolicy::first()->internal_period;

        $start_date = Carbon::parse($created_at)->startOfDay();
        $end_date = $start_date->copy()->addDays($internal_period);

        $now = Carbon::now()->startOfDay();

        if ($now->gte($end_date) || $this->status == 'canceled' || $this->status == 'refunded') {
            return 0;
        }

        $waiting_period = $now->diffInDays($end_date);

        return $waiting_period;

    }

    public static function reflectDeposit()
    {

        $internal_period = AssetPolicy::first()->internal_period;

        $cutoff = now()->subDays($internal_period)->endOfDay();

        $transfers = self::where('status', 'waiting')
            ->where('type', 'deposit')
            ->where('created_at', '<=', $cutoff)
            ->get();

        Log::channel('asset')->info('start to reflected to user income balance', ['cutoff' => $cutoff]);
        Log::channel('asset')->info('income transfer count', ['trasnfer_count' => count($transfers)]);

        foreach ($transfers as $deposit) {

            DB::beginTransaction();

            try {
                $income = Income::find($deposit->income_id);
                $asset = Asset::where('user_id', $deposit->user_id)
                    ->where('coin_id', $deposit->income->coin_id)
                    ->first();

                $before_balance = $asset->balance;
                $amount = $deposit->amount;
                $after_balance = $asset->balance + $amount;

                $asset->update(['balance' => $after_balance]);
                $deposit->update(['status' => 'completed']);

                AssetTransfer::create([
                    'user_id' => $deposit->user_id,
                    'asset_id' => $asset->id,
                    'type' => 'internal',
                    'status' => 'completed',
                    'amount' => $amount,
                    'actual_amount' => $amount,
                    'before_balance' => $before_balance,
                    'after_balance' => $after_balance,
                ]);

                Log::channel('asset')->info('Deposited amount reflected to user income balance', [
                    'user_id' => $asset->user_id,
                    'transfer_id' => $deposit->id,
                    'balance' => $amount,
                    'before_balance' => $before_balance,
                    'after_balance' => $after_balance,
                    'timestamp' => now(),
                ]);

                DB::commit();


            } catch (\Throwable $e) {

                DB::rollBack();

                Log::channel('asset')->error('Failed to reflected to user income balance', [
                    'transfer_id' => $deposit->id,
                    'error' => $e->getMessage(),
                ]);

            }
        }

        Log::channel('asset')->info('end to reflected to user income balance');
    }
}
