<?php

namespace App\Models;

use CloudinaryLabs\CloudinaryLaravel\MediaAlly;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;
    use MediaAlly;

    protected $table = 'stores';

    protected $fillable = [
        'user_id', 'name', 'country', 'city', 'about', 'logo', 'cover', 'phone', 'email', 'official_website', 'product_types', 'type', 'phone_code'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'product_types' => 'array'
    ];
    public $appends = ['products', 'collections', 'followers'];

    public function collections()
    {
        return $this->hasMany('App\Models\Collection');
    }

    public function products()
    {
        return $this->hasMany('App\Models\Product');
    }

    public function getProductsAttribute()
    {
        return $this->products()->get();
    }

    public function getCollectionsAttribute()
    {
        return $this->collections()->get();
    }
    public function projects()
    {
        return $this->morphMany(Project::class, 'authorable');
    }


    public function followers()
    {
        return $this->belongsToMany(Follower::class);
    }

    public function getFollowersAttribute()
    {
        $user_ids = [];
        $followers = $this->followers()->get();
        foreach ($followers as $follower) {
            array_push($user_ids, $follower->follower_id);
        }
        return $user_ids;
    }
    public static function boot()
    {
        parent::boot();
        static::deleting(function ($store) {

            $store->products()->delete();
            $store->collections()->delete();
        });
    }
}
