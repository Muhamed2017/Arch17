<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'name', 'cover', 'title',  'article_type', 'kind',  'year', 'country', 'city', 'type',
        'content', 'ownerable_id', 'ownerable_type', 'dhome', 'images'
    ];
    protected $casts = [
        'images' => 'array',
        'dhome' => 'boolean'
    ];

    // protected $appends = ['similars'];

    public function ownerable()
    {
        return $this->morphTo();
    }

    public function designerRoles()
    {
        return $this->belongsToMany(User::class);
    }

    public function productsTagged()
    {
        return $this->belongsToMany(ProductIdentity::class, 'project_product', 'project_id', 'identity_id');
    }

    public function brandRoles()
    {
        return $this->belongsToMany(Store::class);
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
