<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{Attendance, Enrollment, User};

class Student extends Model
{
    protected $fillable = ['name', 'birthdate', 'medical_notes', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
