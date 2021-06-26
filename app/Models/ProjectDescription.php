<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectDescription extends Model
{
    protected $fillable = [
        'project_id', 'description_text'
    ];
    protected $casts = [
        'description_text' => 'array',
    ];
    use HasFactory;
    public function images()
    {
        return $this->morphMany('App\Models\Image', 'imageable');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    public static function findOrCreate($id)
        {
            $obj = static::find($id);
            return $obj ?: new static;
        }
}
