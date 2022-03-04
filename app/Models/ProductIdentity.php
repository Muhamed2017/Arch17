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
        'lighting_types' => 'array',
        'bulbTypes' => 'array',
        'applied_on' => 'array',
        'installations' => 'array',
        'colorTempratures' => 'array',
    ];

    protected $fillable = [
        'product_id', 'name', 'kind', 'city', 'style', 'category', 'material', 'places_tags', 'country', 'shape', 'base', 'seats', 'is_outdoor', 'is_for_kids', 'type',
        'product_file_kind', 'preview_cover', 'preview_price', 'lighting_types', 'applied_on',  'bulbTypes', 'installations', 'colorTempratures'
    ];
    public $appends = [
        'product',
        'store_name',
        'file'
    ];
    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }

    public function getProductAttribute()
    {
        $product =  DB::table('products')->where('id', $this->product_id)->first('store_id');
        return $product;
    }

    public function getStoreNameAttribute()
    {
        $store =  DB::table('stores')->where('id', $this->product->store_id)->first();
        return collect([
            'store_name' => $store->name,
            'store_id' => $store->id,
            'store_type' => $store->type
        ]);
    }
    public function getFileAttribute()
    {
        $file =  DB::table('files')->where('product_id', $this->product_id)->get('file_type');
        return $file;
    }
}
