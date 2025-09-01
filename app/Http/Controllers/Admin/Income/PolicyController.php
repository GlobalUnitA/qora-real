<?php

namespace App\Http\Controllers\Admin\Income;

use App\Models\UserGrade;
use App\Models\SubscriptionPolicy;
use App\Models\ReferralPolicy;
use App\Models\RankPolicy;
use App\Models\PolicyModifyLog;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class PolicyController extends Controller
{

    public function index(Request $request)
    {
        switch ($request->mode) {
            case 'subscription' :

                $policies = SubscriptionPolicy::all();

                $modify_logs = PolicyModifyLog::join('subscription_policies', 'subscription_policies.id', '=', 'policy_modify_logs.policy_id')
                    ->join('user_grades', 'user_grades.id', '=', 'subscription_policies.grade_id')
                    ->join('admins', 'admins.id', '=', 'policy_modify_logs.admin_id')
                    ->select('user_grades.name as grade_name', 'admins.name', 'policy_modify_logs.*')
                    ->where('policy_modify_logs.policy_type', 'subscription_policies')
                    ->orderBy('policy_modify_logs.created_at', 'desc')
                    ->get();

                return view('admin.income.policy.subscription', compact('policies', 'modify_logs'));

            break;

            case 'referral' :

                $policies = ReferralPolicy::all();

                $modify_logs = PolicyModifyLog::join('referral_policies', 'referral_policies.id', '=', 'policy_modify_logs.policy_id')
                    ->join('user_grades', 'user_grades.id', '=', 'referral_policies.grade_id')
                    ->join('admins', 'admins.id', '=', 'policy_modify_logs.admin_id')
                    ->select('user_grades.name as grade_name', 'admins.name', 'policy_modify_logs.*')
                    ->where('policy_modify_logs.policy_type', 'referral_policies')
                    ->orderBy('policy_modify_logs.created_at', 'desc')
                    ->get();

                return view('admin.income.policy.referral', compact('policies', 'modify_logs'));

            break;

            case 'rank' :

                $policies = RankPolicy::all();

                $user_grades = UserGrade::all();

                $modify_logs = PolicyModifyLog::join('rank_policies', 'rank_policies.id', '=', 'policy_modify_logs.policy_id')
                    ->join('user_grades', 'user_grades.id', '=', 'rank_policies.grade_id')
                    ->join('admins', 'admins.id', '=', 'policy_modify_logs.admin_id')
                    ->select('user_grades.name as grade_name', 'admins.name', 'policy_modify_logs.*')
                    ->where('policy_modify_logs.policy_type', 'rank_policies')
                    ->orderBy('policy_modify_logs.created_at', 'desc')
                    ->get();

                return view('admin.income.policy.rank', compact('policies', 'user_grades', 'modify_logs'));

            break;
        }
    }

    public function store(Request $request)
    {
        if (RankPolicy::where('grade_id', $request->grade_id)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => '이미 해당 등급에 대한 정책이 존재합니다.',
            ]);
        }

        DB::beginTransaction();

        try {
        
            RankPolicy::create([
                'grade_id' => $request->grade_id,
                'bonus' => $request->bonus,
                'conditions' => $request->conditions,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => '정책이 추가되었습니다.',
                'url' => route('admin.income.policy', ['mode' => 'rank']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Failed to create rank policy', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => 'error',
                'message' => '예기치 못한 오류가 발생했습니다.',
            ]);
        }

    }

    public function update(Request $request)
    {

        DB::beginTransaction();

        try {
            switch ($request->mode) {
                case 'subscription' :

                    $subscriptionPolicy = SubscriptionPolicy::findOrFail($request->id);
                    $subscriptionPolicy->update($request->all());

                break;

                case 'referral' :

                    $referralPolicy = ReferralPolicy::findOrFail($request->id);
                    $referralPolicy->update($request->all());

                break;

                case 'rank' :

                    $rankPolicy = RankPolicy::findOrFail($request->id);

                    $data = $request->all();
                    $data['conditions'] = array_values($request->conditions ?? []);

                    if (is_null($data['conditions'][0]['min_level']) || is_null($data['conditions'][0]['max_level']) || is_null($data['conditions'][0]['referral_count'])) {
                        $data['conditions'] = null;
                    }
                    $rankPolicy->update($data);

                break;
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => '정책이 수정되었습니다.',
                'url' => route('admin.income.policy', ['mode' => $request->mode]),
            ]);


        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Failed to update '.$request->mode.' policy', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => 'error',
                'message' => '예기치 못한 오류가 발생했습니다.',
            ]);
        }
    }
}
