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
        'user_id', 'name', 'country', 'city', 'about', 'phone', 'email', 'official_website', 'product_types', 'type'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'product_types' => 'array'
    ];

    // public function businessAccont()
    // {
    //     return $this->belongsTo('App\Models\BusinessAccount');
    // }


    public function products()
    {
        return $this->hasMany('App\Models\Product');
    }

    public function projects()
    {
        return $this->morphMany(Project::class, 'authorable');
    }


    // protected $appens='logo';
    public function followers()
    {
        return $this->morphMany(Follower::class, 'followerable');
    }
}
