<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follower extends Model
{
    use HasFactory;
    protected $fillable = ['follower_id'];

    protected $appends = ['storedata'];


    public function stores()

    {
        return $this->belongsToMany(Store::class);
    }
    public function getStoredataAttribute()
    {
        // return $this->stores;
        $pics = [];
        $ids = [];
        $names = [];
        $prices = [];
        // $prs = $this->stores->products()->latest()->take(3)->get();

        return $this->stores()->products;
        // foreach ($prs as $pr) {
        //     array_push($pics, $pr->identity[0]->preview_cover);
        //     array_push($names, $pr->identity[0]->name);
        //     array_push($prices, $pr->identity[0]->preview_price);
        //     array_push($ids, $pr->identity[0]->product_id);
        // }
        // return [
        //     'ids' => $ids,
        //     'pics' => $pics,
        //     'prices' => $prices,
        //     'names' => $names,
        //     // 'store' => Store::find($this->store_id)->name
        // ];
    }
}
