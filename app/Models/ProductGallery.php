<?php

namespace App\Models;

use CloudinaryLabs\CloudinaryLaravel\MediaAlly;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductGallery extends Model
{
    use HasFactory, MediaAlly;

    protected $table = 'product_gallery';


    protected $fillable = [
        'product_id', 'desc_gallery_files'
    ];

    protected $casts = [
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
