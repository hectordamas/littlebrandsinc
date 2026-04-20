<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{Branch, LBClass};

class Course extends Model
{
    public function branch(){
        return $this->belongsTo(Branch::class);
    }

    public function classes(){
        return $this->hasMany(LBClass::class);
    }
}
