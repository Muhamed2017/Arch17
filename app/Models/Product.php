<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use CloudinaryLabs\CloudinaryLaravel\MediaAlly;
use Illuminate\Support\Facades\DB;


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

    public $appends = ['identity', 'options', 'description', 'files', 'gallery', 'stores'];

    public function images()
    {
        return $this->morphMany('App\Models\Image', 'imageable');
    }

    public function identity()
    {
        return $this->hasOne('App\Models\ProductIdentity');
    }


    // new option model
    public function options()
    {
        return $this->hasMany('App\Models\Option');
    }


    public function description()
    {
        return $this->hasOne('App\Models\ProductDescription');
    }

    // public function files()
    // {
    //     return $this->hasOne('App\Models\ProductFiles');
    // }

    public function files()
    {
        return $this->hasMany('App\Models\File');
    }

    public function gallery()
    {
        return $this->hasOne('App\Models\ProductGallery');
    }

    public function store()
    {
        return $this->belonsTo(Store::class);
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
        return $this->options()->get();
    }

    public function getDescriptionAttribute()
    {
        return $this->description()->get();
    }
    public function getGalleryAttribute()
    {
        return $this->gallery()->get();
    }

    public function getStoresAttribute()
    {
        // $store =  DB::table('stores')->where('id', $this->store_id)->first();
        return  $this->store;
    }

    public function collections()
    {
        $this->belongsToMany(Collection::class);
    }
    public function folders()
    {
        return $this->belongsToMany(Folder::class);
    }


    public static function boot()
    {
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
