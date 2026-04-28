<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{LBClass, Student};

class Attendance extends Model
{
    protected $fillable = [
        'student_id',
        'class_id',
        'date',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function class()
    {
        return $this->belongsTo(LBClass::class, 'class_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
