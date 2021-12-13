<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;


    protected $fillable = ['product_id', 'file_name', 'file_type', 'software', 'links'];

    protected $casts = ['links' => "array"];


    public function product()
    {

        return $this->belongsTo("App\Models\Product");
    }
}
