<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TestDev2nd;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TestDev2ndController extends Controller
{
    public function apiTest()
    {
        Route::get('/token',function(){
            $token = Str::uuid()->toString();
            Cache::put('request_token:' . $token, true, 300); //5분간 유효

            return response()->json(['token' => $token]);
        });

        Route::post('/submit', function (Request $request) {
            $token = $request->input('token');

            if (!$token) {
                return response()->json(['error' => 'Missing token'], 400);
            }

            if (!Cache::pull('request_token:' . $token)) {
                return response()->json(['error' => 'Duplicate or invalid token'], 409);
            }

            // 실제 처리 로직
            return response()->json(['message' => 'Success']);
        });
    }

    /*
    public function insertData()
    {
        $data = [];
        for($i=0;$i<=100;$i++){
            $data[] = [
               'amount' => $i * 10 + time(),
               'depth' => rand($i,10),
               'count' => rand($i,10),
               'condition' => $i % 2 === 0 ? 'and' : 'or',
               'created_at' => now(),
               'updated_at' => now(),
           ];
        }

        TestDev2nd::insert($data);

            protected $table = 'test_dev_2nd';

            protected $fillable = ['amount', 'depth', 'count', 'condition'];

        return "데이터 삽입 존내 완료";
    }
*/
}
