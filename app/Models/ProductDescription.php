<?php

namespace App\Models;

use CloudinaryLabs\CloudinaryLaravel\MediaAlly;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDescription extends Model
{
    use HasFactory, MediaAlly;

    protected $table = 'product_description';


    protected $fillable = [
        'product_id', 'desc_overview_img', 'desc_mat_desc_img', 'desc_dimension_img', 'desc_gallery_files'
    ];

    protected $casts = [
        'desc_overview_img' => 'array',
        'desc_mat_desc_img' => 'array',
        'desc_dimension_img' => 'array',
        'desc_gallery_files' => 'array',
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
