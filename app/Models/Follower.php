<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follower extends Model
{
    use HasFactory;
    protected $fillable = ['follower_id'];

    // protected $appends = ['storedata'];


    public function stores()

    {
        return $this->belongsToMany(Store::class);
    }
    // public function getStoredataAttribute()
    // {

    // }
}
