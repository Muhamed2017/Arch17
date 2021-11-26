<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use CloudinaryLabs\CloudinaryLaravel\MediaAlly;


class Product extends Model
{
    use HasFactory;
    use MediaAlly;


    // public function collections()
    // {
    //     return $this->morphToMany(Collection::class, 'collectionable');
    // }


    protected $fillable = [
        'store_id', 'user_id',  'business_account_id', 'kind'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public $appends = ['identity', 'options', 'description', 'files', 'gallery'];

    public function images()
    {
        return $this->morphMany('App\Models\Image', 'imageable');
    }

    public function identity()
    {
        return $this->hasOne('App\Models\ProductIdentity');
    }

    public function options()
    {
        return $this->hasMany('App\Models\ProductOptions');
    }

    public function description()
    {
        return $this->hasOne('App\Models\ProductDescription');
    }

    public function files()
    {
        return $this->hasOne('App\Models\ProductFiles');
    }

    public function gallery()
    {
        return $this->hasOne('App\Models\ProductGallery');
    }

    public function store()
    {
        return $this->belonsTo('App\Models\Store');
    }

    public function getIdentityAttribute()
    {
        return $this->identity()->get();
    }

    public function getFilesAttribute()
    {
        return $this->files()->get();
    }
    public function getOptionsAttribute()
    {
        return $this->options()->where('cover', '!=', null)->get();
    }

    public function getDescriptionAttribute()
    {
        return $this->description()->get();
    }
    public function getGalleryAttribute()
    {
        return $this->gallery()->get();
    }

    public function collections()
    {
        $this->belongsToMany("App\Models\Collection");
    }

    public static function boot()
    {
        //        schema::defaultStringLength(191);
        parent::boot();
        static::deleting(function ($product) {
            if (count($product->images) > 0) {
                foreach ($product->images as $image) {
                    $image->delete();
                }
            }
        });
    }
}
