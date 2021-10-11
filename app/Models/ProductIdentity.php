<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductIdentity extends Model
{
    use HasFactory;

    protected $casts = [
        'places_tags' => 'array',
        'material' => 'array',
        'style' => 'array',
        'shape' => 'array',
        'base' => 'array',
        'seats' => 'array',
        'type' => 'array',
    ];

    protected $fillable = [
        'product_id', 'name', 'kind', 'city', 'style', 'category', 'material', 'places_tags', 'country', 'shape', 'base', 'seats', 'is_outdoor', 'is_for_kids', 'type',
        'product_file_kind', 'preview_cover', 'preview_price'
    ];

    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }
}
