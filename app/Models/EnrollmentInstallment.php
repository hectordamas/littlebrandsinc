<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnrollmentInstallment extends Model
{
    protected $fillable = [
        'enrollment_id',
        'account_receivable_id',
        'period_year',
        'period_month',
        'due_date',
        'amount',
        'currency',
        'status',
        'is_first_month',
        'stripe_invoice_id',
        'stripe_payment_intent_id',
        'paid_at',
        'notified_d3_at',
        'notified_d1_at',
        'notified_d0_at',
        'notified_d3_plus_at',
        'retry_count',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'is_first_month' => 'boolean',
        'paid_at' => 'datetime',
        'notified_d3_at' => 'datetime',
        'notified_d1_at' => 'datetime',
        'notified_d0_at' => 'datetime',
        'notified_d3_plus_at' => 'datetime',
    ];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function receivable()
    {
        return $this->belongsTo(AccountReceivable::class, 'account_receivable_id');
    }
}
