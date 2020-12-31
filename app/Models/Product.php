<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    public function collections()
    {
        return $this->morphToMany(Collection::class , 'collectionable');
    }


    protected $casts = [
        'places_tags' => 'array',
        'material_name' => 'array',
        'text_description' => 'array',
        'price' => 'array',
        'offer_price' => 'array',
        'size' => 'array',
        'quantity' => 'array'
    ];

   protected $fillable=[
        'store_id', 'user_id',  'business_account_id', 'name', 'kind', 'style', 'category', 'places_tags', 'country', 'city',
        'text_description'
    ];


    public function images(){
        return $this->morphMany('App\Models\Image', 'imageable');
    }


    public function options(){
        return $this->hasMany('App\Models\ProductOptions');
    }

    public function store(){
        return $this->belonsTo('App\Models\Store');
    }



     public static function boot() {
//        schema::defaultStringLength(191);
        parent::boot();
        static::deleting(function($product) {
            if (count($product->images) > 0) {
                foreach ($product->images as $image) {
                    $image->delete();
                }
            }
        });
    }

}
