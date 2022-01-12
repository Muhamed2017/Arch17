<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Collection extends Model
{
    use HasFactory;

    protected $fillable = ['collection_name', 'store_id'];
    protected $appends = ['products'];



    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function getProductsAttribute()
    {

        return [
            'products' => $this->products()->get(['product_id']),
            'count' => $this->products()->count(),
        ];
    }
}
