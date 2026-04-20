<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{Enrollment, Course};

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
}
