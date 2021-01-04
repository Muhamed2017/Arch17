<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectDesigner extends Model
{
    use HasFactory;
    protected $fillable = ['name','email','country','company_id','project_id'];

}
