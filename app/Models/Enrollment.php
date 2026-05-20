<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $fillable = [
        'student_id',
        'course_id',
        'parent_id',
        'status',
        'payment_method',
        'payment_status',
        'is_free_trial',
        'terms_accepted',
        'image_consent_accepted',
        'payment_receipt_path',
        'payment_receipt_original_name',
    ];

    protected $casts = [
        'is_free_trial' => 'boolean',
        'terms_accepted' => 'boolean',
        'image_consent_accepted' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
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
}
