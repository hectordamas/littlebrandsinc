<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{Student, Course, LBClass};

class Enrollment extends Model
{
    protected $fillable = ['student_id', 'course_id', 'class_id', 'status', 'payment_method', 'payment_status'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lbclass()
    {
        return $this->belongsTo(LBClass::class);
    }
}
