<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $table = 'stores';

    protected $fillable = [
        'business_account_id', 'user_id', 'name', 'country', 'city', 'about', 'phone', 'email', 'official_website'
    ];

     protected $dates = [
        'created_at',
        'updated_at'
    ];


    public function businessAccont(){
        return $this->belongsTo('App\Models\BusinessAccount');
    }


    // protected $appens='logo';
}
