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

    public function getPicsAttributes()
    {
        $pics = [];
        $ids = [];
        $names = [];
        $prices = [];
        $prs = $this->products()->latest()->take(3)->get();
        foreach ($prs as $pr) {
            array_push($pics, $pr->identity[0]->preview_cover);
            array_push($names, $pr->identity[0]->name);
            array_push($prices, $pr->identity[0]->preview_price);
            array_push($ids, $pr->identity[0]->product_id);
        }
        return [
            'ids' => $ids,
            'pics' => $pics,
            'prices' => $prices,
            'names' => $names,
            'store' => Store::find($this->store_id)->name
        ];
    }
    public function getProductsAttribute()
    {
        return [
            'count' => $this->products()->count(),
            'products_info' => $this->getPicsAttributes()
        ];
    }
}
