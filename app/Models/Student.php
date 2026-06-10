<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{Attendance, Enrollment, User};

class Student extends Model
{
    protected $fillable = ['name', 'birthdate', 'medical_notes', 'comment', 'level', 'active', 'user_id'];

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

    public function waitlistEntries()
    {
        return $this->hasMany(Waitlist::class);
    }

    /**
     * Accede a todos los cursos del estudiante a través de sus inscripciones.
     * Requiere eager-load 'enrollments.courses' para evitar N+1.
     */
    public function getCoursesAttribute()
    {
        return $this->enrollments
            ->flatMap(fn ($e) => $e->courses)
            ->unique('id')
            ->values();
    }
}
