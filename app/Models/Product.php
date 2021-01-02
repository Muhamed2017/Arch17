<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;


    protected $casts = [
        'places_tags' => 'array',
    ];

    protected $fillable = [
        'store_id', 'user_id',  'business_account_id', 'name', 'kind', 'city', 'style', 'category', 'text_description', 'places_tags', 'country', 'city',

    ];


    public function images()
    {
        return $this->morphMany('App\Models\Image', 'imageable');
    }


    public function options()
    {
        return $this->hasMany('App\Models\ProductOptions');
    }
    public function description()
    {
        return $this->hasOne('App\Models\ProductDescription');
    }

    public function store()
    {
        return $this->belonsTo('App\Models\Store');
    }



    public static function boot()
    {
        //        schema::defaultStringLength(191);
        parent::boot();
        static::deleting(function ($product) {
            if (count($product->images) > 0) {
                foreach ($product->images as $image) {
                    $image->delete();
                }
            }
        });
    }
}
