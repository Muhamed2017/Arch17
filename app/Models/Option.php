<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Option extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'covers', 'code', 'price', 'offer_price', "size", 'quantity', 'material_name', 'material_image'];

    protected $casts = [
        'covers' => 'array'
    ];

    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }
}
