<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follower extends Model
{
    use HasFactory;
    protected $fillable = ['follower_id', 'stores'];

    public function stores()

    {
        $this->belongsToMany(Store::class);
    }

    // public function getStoresAttribute()
    // {
    //     return $this->stores();
    // }
}
