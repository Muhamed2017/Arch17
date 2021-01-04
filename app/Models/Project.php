<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
    protected $fillable = ['name','cover_name','category','year','country','city','types','text_description','authorable_id','authorable_type'];
    protected $casts = [
        'types' => 'array',
        'text_description' => 'array',
    ];
    public function images(){
        return $this->morphMany('App\Models\Image', 'imageable');
    }
    
    public function suppliers(){
        return $this->hasMany(ProjectSupplier::class);
    }
    public function designers(){
        return $this->hasMany(ProjectDesigner::class);
    }
    public function description()
    {
        return $this->hasOne(ProjectDescription::class);
    }
    public function collections()
    {
        return $this->morphToMany(Collection::class , 'collectionable');
    }
    public static function boot() {
    //        schema::defaultStringLength(191);
        parent::boot();
        static::deleting(function($product) {
            if (count($product->images) > 0) {
                foreach ($product->images as $image) {
                    $image->delete();
                }
            }
        });
    }

}
