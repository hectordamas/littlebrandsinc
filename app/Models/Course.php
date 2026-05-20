<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{Branch, LBClass, Enrollment, Program, User};

class Course extends Model
{
    protected $fillable = ['title', 'description', 'program_id', 'min_age', 'max_age', 'capacity', 'price', 'monthly_fee', 'start_date', 'end_date', 'branch_id', 'active'];

    protected $casts = [
        'price' => 'decimal:2',
        'monthly_fee' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function classes()
    {
        return $this->hasMany(LBClass::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function coaches()
    {
        return $this->belongsToMany(User::class, 'course_coach', 'course_id', 'coach_id')
            ->withTimestamps();
    }
}
