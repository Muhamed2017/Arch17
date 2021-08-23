<?php

namespace App\Models;

use CloudinaryLabs\CloudinaryLaravel\MediaAlly;
// use App\Support\Services\Medially;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOptions extends Model
{
    use HasFactory;
    use MediaAlly;
    protected $table = 'product_options';

    protected $fillable = [
        'product_id', 'cover', 'material_name', 'material_image', 'price', 'size', 'quantity', 'code', 'offer_price'
    ];

    protected $casts = [
        'cover' => 'array',
    ];

    protected $hidden = [
        'id', 'product_id'
    ];


    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }
}
