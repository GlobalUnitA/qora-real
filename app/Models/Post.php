<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'admin_id',
        'board_id',
        'subject',
        'content',
        'image_urls',
        'is_popup',
        'is_banner',
    ];

    protected $casts = [
        'image_urls' => 'array',
    ];

    public function board()
    {
        return $this->belongsTo(Board::class, 'board_id', 'id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function admin() {
        return $this->belongsTo(Admin::class, 'admin_id', 'id');
    }
}
