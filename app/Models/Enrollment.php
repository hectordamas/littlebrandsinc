<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $fillable = [
        'student_id',
        'program_id',
        'parent_id',
        'status',
        'payment_method',
        'payment_status',
        'is_free_trial',
        'terms_accepted',
        'image_consent_accepted',
        'payment_receipt_path',
        'payment_receipt_original_name',
        'custom_enrollment_fee',
    ];

    protected $casts = [
        'is_free_trial' => 'boolean',
        'terms_accepted' => 'boolean',
        'image_consent_accepted' => 'boolean',
        'custom_enrollment_fee' => 'float',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'enrollment_course')
            ->withTimestamps();
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function receivable()
    {
        return $this->hasOne(AccountReceivable::class);
    }

    public function billingProfile()
    {
        return $this->hasOne(EnrollmentBillingProfile::class);
    }

    public function installments()
    {
        return $this->hasMany(EnrollmentInstallment::class);
    }

    public function getInitialChargeAmount(): float
    {
        $this->loadMissing(['program', 'courses']);

        $enrollmentFee = ($this->custom_enrollment_fee !== null)
            ? (float) $this->custom_enrollment_fee
            : (float) (optional($this->program)->enrollment_fee ?? 50.00);

        $total = $enrollmentFee;

        foreach ($this->courses as $course) {
            $total += (float) ($course->monthly_fee ?? 0);
        }

        return $total;
    }
}
