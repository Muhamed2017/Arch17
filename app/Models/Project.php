<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
    public function collections()
    {
        return $this->morphToMany(Collection::class , 'collectionable');
    }
}
