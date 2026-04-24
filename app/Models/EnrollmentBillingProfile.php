<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnrollmentBillingProfile extends Model
{
    protected $fillable = [
        'enrollment_id',
        'billing_mode',
        'auto_pay_enabled',
        'stripe_customer_id',
        'stripe_subscription_id',
        'stripe_default_payment_method_id',
        'billing_anchor_day',
        'next_billing_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'auto_pay_enabled' => 'boolean',
        'next_billing_date' => 'date',
    ];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }
}
