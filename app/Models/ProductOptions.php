<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOptions extends Model
{
    use HasFactory;

    protected $table='product_options';


    protected $casts = [
        'material_name' => 'array',
        'price' => 'array',
        'offer_price' => 'array',
        'size' => 'array',
        'quantity' => 'array'
    ];

    protected $fillable=[
        'product_id', 'material_name', 'price', 'size', 'quantity', 'offer_price'
    ];


    public function images(){
        return $this->morphMany('App\Models\Image', 'imageable');
    }

    public function product(){
        return $this->belongsTo('App\Models\Product');
    }
}
