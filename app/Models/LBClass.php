<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{Attendance, Branch, Course, Enrollment, User};

class LBClass extends Model
{
    protected $table = 'classes';

    protected $fillable = [
        'course_id',
        'branch_id',
        'date',
        'start_time',
        'end_time',
        'coach_id',
        'observations',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function coach()
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    public function getCoachNameAttribute()
    {
        if ($this->coach) {
            return $this->coach->name;
        }

        if ($this->course && $this->course->coaches->isNotEmpty()) {
            return $this->course->coaches->pluck('name')->join(', ');
        }

        return null;
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'class_id');
    }
}
