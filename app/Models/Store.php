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
        'user_id', 'name', 'country', 'city', 'about', 'logo', 'cover', 'phone', 'email', 'official_website', 'type', 'phone_code'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'product_types' => 'array'
    ];
    public $appends = ['products', 'collections', 'followers', 'box'];

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

    public function types()
    {
        return $this->hasMany(Type::class);
    }


    public function getBoxAttribute()
    {
        $pics = [];
        $ids = [];
        $names = [];
        $prices = [];
        $prs = $this->products()->latest()->take(3)->get();

        foreach ($prs as $pr) {
            array_push($pics, $pr->identity[0]->preview_cover);
            array_push($names, $pr->identity[0]->name);
            array_push($prices, $pr->identity[0]->preview_price);
            array_push($ids, $pr->identity[0]->product_id);
        }
        return [
            'ids' => $ids,
            'pics' => $pics,
            'prices' => $prices,
            'names' => $names,
        ];
    }

    public function projects()
    {
        return $this->morphMany(Project::class, 'ownerable');
    }

    public function projectRole()
    {
        return $this->belongsToMany(Project::class);
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
