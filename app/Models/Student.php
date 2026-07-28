<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{Attendance, Enrollment, User};

class Student extends Model
{
    protected $fillable = ['name', 'birthdate', 'medical_notes', 'comment', 'level', 'active', 'user_id'];

    protected $casts = [
        'active' => 'boolean',
        'birthdate' => 'date',
    ];

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
     * Devuelve el estado de consentimiento de imagen a través de sus inscripciones.
     */
    public function getImageConsentAttribute(): bool
    {
        $activeEnrollment = $this->enrollments
            ->where('status', '!=', 'cancelled')
            ->first();

        if ($activeEnrollment) {
            return (bool) $activeEnrollment->image_consent_accepted;
        }

        $anyEnrollment = $this->enrollments->first();
        return $anyEnrollment ? (bool) $anyEnrollment->image_consent_accepted : true;
    }

    /**
     * Accede a todos los cursos del estudiante a través de sus inscripciones.
     * Requiere eager-load 'enrollments.courses' para evitar N+1.
     */
    public function getCoursesAttribute()
    {
        return $this->enrollments
            ->where('status', '!=', 'cancelled')
            ->flatMap(fn ($e) => $e->courses)
            ->unique('id')
            ->values();
    }
}

