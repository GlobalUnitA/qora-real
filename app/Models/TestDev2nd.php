<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestDev2nd extends Model
{
    use HasFactory;

    // 실제 테이블명 지정
    protected $table = 'test_dev_2nd';

    protected $fillable = ['amount', 'depth', 'count', 'condition'];

}
