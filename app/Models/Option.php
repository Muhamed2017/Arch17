<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Casts\AsCollection;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Option extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'covers', 'code', 'price', 'offer_price', "size_l", 'size_w', 'size_h', 'quantity', 'material_name', 'material_image'];

    protected $casts = [
        'covers' => 'array'
    ];


    protected $appends = ['material', 'size'];
    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }

    public function getSizeAtttribute()
    {
        $size = [
            'w' => $this->size_l,
            'l' => $this->size_w,
            'h' => $this->size_h,
        ];
        return $size;
    }
    public function getMaterialAtttribute()
    {
        $material = [
            'name' => $this->material_name,
            'image' => $this->material_image,
        ];
        return $material;
    }
}
