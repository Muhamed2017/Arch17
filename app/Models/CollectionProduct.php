<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CollectionProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'collection_id',
    ];
}
