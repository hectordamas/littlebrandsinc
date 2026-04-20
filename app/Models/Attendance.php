<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{LBClass, Student};

class Attendance extends Model
{

    public function class()
    {
        return $this->belongsToMany(LBClass::class, 'class_id');
    }

    public function student()
    {
        return $this->belongsToMany(Student::class, 'student_id');
    }
}
