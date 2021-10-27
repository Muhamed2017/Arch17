<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;


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
    // public function scopeTermSearch(Builder $query, $term): Builder
    // {
    //     return $query->where('kind', 'LIKE', "%" . $term . "%")
    //         // ->orWhere('color', 'LIKE', "%" . $term . "%")
    //         // ->orWhere('transmission', 'LIKE', "%" . $term . "%")
    //         // ->orWhere('engine_type', 'LIKE', "%" . $term . "%")
    //         // ->orWhere('model', 'LIKE', "%" . $term . "%")
    //         // ->orWhere('primary_damage', 'LIKE', "%" . $term . "%")
    //         ->orWhere('style', 'LIKE', "%" . $term . "%");
    // }
}
