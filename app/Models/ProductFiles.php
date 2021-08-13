<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductFiles extends Model
{
    use HasFactory;

    protected $table = 'product_files';

    protected $fillable = [
        'product_id', 'files_cad_2d', 'files_3d', 'files_pdf_cats'
    ];

    protected $casts = [
        'files_cad_2d' => 'array',
        'files_3d' => 'array',
        'files_pdf_cats' => 'array'
    ];

    protected $hidden = ['id', 'product_id'];

    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }
}
