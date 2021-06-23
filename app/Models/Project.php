<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Project extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['name','cover_name','category','year','country','city','types','authorable_id','authorable_type','state'];
    protected $casts = [
        'types' => 'array'
    ];
    public function images(){
        return $this->morphMany('App\Models\Image', 'imageable');
    }
    
    public function suppliers(){
        return $this->hasMany(ProjectSupplier::class);
    }   
    public function designers(){
        return $this->morphMany(ProjectDesigner::class, 'designerable');
    }
    public function products()
    {
        return $this->hasManyThrough(Projects_product::class, Product::class);
    }
    public function description()
    {
        return $this->hasMany(ProjectDescription::class);
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
