<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    use HasFactory;
    protected $fillable = ['collection_name', 'store_id'];

    public $appends = ['products'];

    public function brand()
    {
        return $this->belongsTo("App\Models\Store");
    }

    public function products()
    {
        return $this->belongsToMany("App\Models\Product");
    }
    // public function getProductsAttribute()
    // {
    //     return $this->products()->get();
    // }
}
