<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

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
    public $appends = ['product', 'materials', 'types', 'seatss', 'styles'];
    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }

    public function getProductAttribute()
    {
        $product =  DB::table('products')->where('id', $this->product_id)->first();

        return $product;
    }

    public function getMaterialsAttribute()
    {
        $collection = collect($this->material);
        $selected_materials = $collection->map(function ($item) {
            return  ['label' => $item, 'value' => $item];
        });
        return $selected_materials->all();
    }

    public function getTypesAttribute()
    {
        $collection = collect($this->type);
        $selected_types = $collection->map(function ($item) {
            return  ['label' => $item, 'value' => $item];
        });
        return $selected_types->all();
    }

    public function getStylesAttribute()
    {
        $collection = collect($this->style);
        $selected = $collection->map(function ($item) {
            return  ['label' => $item, 'value' => $item];
        });
        return $selected->all();
    }

    public function getSeatssAttribute()
    {
        $collection = collect($this->seats);
        $selected = $collection->map(function ($item) {
            return  ['label' => $item, 'value' => $item];
        });
        return $selected->all();
    }
}
