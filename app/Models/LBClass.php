<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{Enrollment, Course, User};

class LBClass extends Model
{
    protected $table = 'classes';

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function coach()
    {
        return $this->belongsTo(User::class, 'coach_id');
    }
}
