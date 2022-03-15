<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

use Illumminate\Notifications\Notifiable;
use App\Models\BusinessAccount;
use CloudinaryLabs\CloudinaryLaravel\MediaAlly;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements JWTSubject
{
    // use HasFactory,
    // use Notifiable,
    use MediaAlly, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     *
     */

    protected $gaurd = 'user';

    protected $fillable = [
        'displayName',  'avatar', 'email', 'phone', 'password', 'mobile', 'country', 'city', 'address', 'user_description', 'allow_to_add_project', 'facebook_user_id'
    ];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'images'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'payment_methods' => 'array',
    ];


    protected $appends = ['avatar'];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public $imgFolderPath = [
        "image" => "Users/Images/",
        "thumb" => "Users/Thumbnails/"
    ];


    public function getAvatarAttribute()
    {
        return $this->images != null ? $this->images->first() : '';
    }

    public function images()
    {
        return $this->morphMany('App\Models\Image', 'imageable');
    }


    public function businessAccount()
    {

        return $this->hasOne('App\Models\BusinessAccount');
    }


    public function stores()
    {
        return $this->hasManyThrough('App\Models\Store', 'App\Models\BusinessAccount');
    }
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'username'   => $this->username,
            'first_name' => $this->fname,
            'last_name'  => $this->lname,
            'type'     => 'user'
        ];
    }




    public static function boot()
    {

        parent::boot();
        static::deleting(function ($user) {

            if (count($user->images) > 0) {
                foreach ($user->images as $image) {
                    $image->delete();
                }
            }
        });
    }


    // relation between user and company one user has many companies
    public function companies()
    {
        return $this->belongsToMany(Company::class);
    }
    // relation between user and company one user has many companies
    public function collections()
    {
        return $this->hasMany(Collection::class, 'user_id');
    }

    public function projects()
    {
        return $this->morphMany(Project::class, 'authorable');
    }

    public function is_designer()
    {
        return $this->is_designer;
    }
}
