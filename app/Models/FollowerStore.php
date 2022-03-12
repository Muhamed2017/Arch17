<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowerStore extends Model
{
    use HasFactory;

    protected $fillable = [
        'follower_id', 'store_id',
    ];
}
