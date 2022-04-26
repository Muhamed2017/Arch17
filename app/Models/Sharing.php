<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sharing extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'sharer_id', 'collection_id', 'board_id',
    ];



    public function boards()
    {
        return $this->hasOne(Board::class);
    }

    public function collections()
    {
        return $this->hasOne(Collection::class);
    }
}
