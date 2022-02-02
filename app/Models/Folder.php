<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name'];

    protected $appends = ['saved'];


    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function getSavedAttribute()
    {
        // return $this->products;
        // return  products();
        return 50;
    }
}
