<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDescription extends Model
{
    use HasFactory;

    protected $table = 'product_description';

    protected $fillable = [
        'product_id', 'description_text'
    ];


    public function images()
    {
        return $this->morphMany('App\Models\Image', 'imageable');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }
}
