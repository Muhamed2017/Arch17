<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cover extends Model
{
    use HasFactory;

    protected $table = 'covers';

    protected $fillable = [
        'crop_data', 'option_id', 'original', 'cropped', 'width', 'height', 'thumb', 'size'
    ];

    public function option()
    {
        return $this->belongsTo(Option::class);
    }
}
