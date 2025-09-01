<?php

namespace App\Http\Controllers\Income;

use App\Models\UserProfile;
use App\Models\Income;
use App\Models\IncomeTransfer;
use App\Models\TradingProfit;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Carbon\Carbon;

class IncomeController extends Controller
{
    public function __construct()
    {

    }


    public function index(Request $request)
    {

        $income_id = Hashids::decode($request->id);
        $income = Income::findOrFail($income_id[0]);

        if ($income->user_id != Auth()->id()) {
             return redirect()->route('home');
        }

        $data = $income->getIncomeInfo();

        $limit = 5;
        $list = IncomeTransfer::where('user_id', Auth()->id())
            ->where('income_id', $income->id)
            ->when($request->filled('type'), function ($query) use ($request) {
                return $query->where('type', $request->type);
            })
            ->where('status', 'completed')
            ->latest()
            ->take($limit)
            ->get();

        $total_count = IncomeTransfer::where('user_id', auth()->id())
            ->where('income_id', $income->id)
            ->when($request->filled('type'), function ($query) use ($request) {
                return $query->where('type', $request->type);
            })
            ->where('status', 'completed')
            ->count();

        $has_more = $total_count > $limit;

        return view('income.income', compact('data', 'list', 'has_more', 'limit'));
    }

    public function list(Request $request)
    {
        $income_id = Hashids::decode($request->id);
        $income = Income::findOrFail($income_id[0]);

        if ($income->user_id != Auth()->id()) {
             return redirect()->route('home');
        }

        $limit = 10;

        $list = IncomeTransfer::where('user_id', Auth()->id())
            ->when($request->filled('type'), function ($query) use ($request) {
                return $query->where('type', $request->type);
            })
            ->where('status', 'completed')
            ->latest()
            ->take($limit)
            ->get();
        
        $total_count = IncomeTransfer::where('user_id', auth()->id())
            ->when($request->filled('type'), function ($query) use ($request) {
                return $query->where('type', $request->type);
            })
            ->where('status', 'completed')
            ->count();

        $has_more = $total_count > $limit;

        return view('income.list', compact('list', 'has_more', 'limit'));
    }

    public function loadMore(Request $request)
    {
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);

        $query = IncomeTransfer::where('user_id', auth()->id())
            ->when($request->filled('type'), function ($query) use ($request) {
                return $query->where('type', $request->type);
            })
            ->where('status', 'completed')
            ->latest();

        $items = $query->skip($offset)->take($limit + 1)->get();

        $hasMore = $items->count() > $limit;

        $items = $items->take($limit)->map(function ($item) {

            return [
                'created_at' => $item->created_at->format('Y-m-d'),
                'amount' => $item->amount,
                 'trading_profit' => optional(optional($item->profit)->trading)->profit_rate !== null
                    ? optional(optional($item->profit)->trading)->profit_rate.'%'
                    : null,
                'referrer_id' => match ($item->type) {
                    'subscription_bonus' => optional($item->subscriptionBonus)->referrer_id,
                    'referral_bonus' => optional($item->referralBonus)->referrer_id,
                    default => null,
                },
                'type_text' => $item->type_text,
            ];
        });

        return response()->json([
            'items' => $items,
            'hasMore' => $hasMore,
        ]);
    }
}
