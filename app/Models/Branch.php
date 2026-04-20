<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{Enrollment, Course};    

class Branch extends Model
{
    public function courses(){
        return $this->hasMany(Course::class);
    }   

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }  
}
