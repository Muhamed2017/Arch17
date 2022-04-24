<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// Folder is for collection products
// Board is for collectiong projects
class Folder extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name'];

    protected $appends = ['pics'];

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }


    public function getPicsAttribute()
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
            'count' => $this->products()->count()
        ];
    }
}
