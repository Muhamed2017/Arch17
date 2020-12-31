<?php

namespace App\Models;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\Console\RetryBatchCommand;

class Company extends Model
{
    use HasFactory;
    use Sluggable;

    protected $fillable  = ['name','email','types','country','city','about','website','phone','is_active'];
    protected $appends = ['avatar'];

    protected $casts = [
        'types' => 'array',
        'is_active'=>'boolean'
    ];


    public $imgFolderPath = [
        "image" => "Companies/Images/",
        "thumb" => "Companies/Thumbnails/"
    ];


    public static function boot() {

        parent::boot();
        static::deleting(function($company) {

            if (count($company->images) > 0) {
                foreach ($company->images as $image) {
                    $company->delete();
                }
            }
        });
    }
     public function getAvatarAttribute()
    {
        return $this->images != null ? $this->images->first() : '';
    }

    public function images()
    {
        return $this->morphMany('App\Models\Image', 'imageable');
    }

    public function owner()
    {
        return $this->belongsToMany(User::class);
    }



    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'fullname',
                'onUpdate'=> 'true'
            ]
        ];
    }

    public function getFullnameAttribute() {
        return $this->id . ' ' . $this->name;
    }
    public function followers()
    {
        return $this->morphMany(Follower::class , 'followerable');
    }
}
