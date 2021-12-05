<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cover extends Model
{
    use HasFactory;

    protected $table = 'covers';

    protected $fillable = [
        'src', 'cropping_data', 'option_id',
    ];

    public function option()
    {
        return $this->belongsTo('App\Models\ProductOptions');
    }
}
