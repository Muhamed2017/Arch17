<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'name'];

    protected $appends = ['pics'];

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }


    public function sharings()
    {
        return $this->belongsToMany(Sharing::class);
    }


    public function getPicsAttribute()
    {
        $pics = [];
        $ids = [];
        $names = [];
        $prs = $this->projects()->latest()->take(3)->get();
        foreach ($prs as $pr) {
            array_push($pics, $pr->cover);
            array_push($names, $pr->name);
            array_push($ids, $pr->id);
        }
        return [
            'ids' => $ids,
            'pics' => $pics,
            'names' => $names,
            'count' => $this->projects()->count()
        ];
    }
}
