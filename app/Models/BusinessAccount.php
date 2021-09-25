<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessAccount extends Model
{
    use HasFactory;

    protected $table = 'business_account';

    protected $fillable = [
        'proffession_name', 'email',  'passcode', 'phone'
    ];



    public function user()
    {
        return $this->belonsTo('App\Models\User');
    }


    //  public function stores(){
    //      return $this->hasMany('App\Models\Store');
    //  }
}
