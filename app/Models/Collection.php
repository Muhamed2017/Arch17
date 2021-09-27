<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    use HasFactory;
    protected $fillable = ['collection_name', 'store_id', 'store_uid'];

    public function brand()
    {
        $this->belongsTo("App\Models\Store");
    }

    public function products()
    {
        $this->hasMany("App\Models\Product");
    }
}
