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
        'name', 'cover', 'title',  'category', 'kind',  'year', 'country', 'city', 'types',
        'content', 'ownerable_id', 'ownerable_type', 'dhome'
    ];
    protected $casts = [
        'types' => 'array',
        'dhome' => 'boolean'
    ];

    public function ownerable()
    {
        return $this->morphTo();
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
