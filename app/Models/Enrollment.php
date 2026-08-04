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

    public function transactions()
    {
        return $this->hasMany(Transaction::class)->orderByDesc('created_at');
    }

    public function getEnrollmentFee(): float
    {
        if ($this->custom_enrollment_fee !== null) {
            return (float) $this->custom_enrollment_fee;
        }

        $query = self::where('student_id', $this->student_id)
            ->where('program_id', $this->program_id)
            ->where('status', '!=', 'cancelled')
            ->where('is_free_trial', false);

        if ($this->exists) {
            $query->where('id', '<', $this->id);
        }

        if ($query->exists()) {
            return 0.0;
        }

        return (float) (optional($this->program)->enrollment_fee ?? 50.00);
    }

    public function getInitialChargeAmount(): float
    {
        $this->loadMissing(['program', 'courses']);

        $enrollmentFee = $this->getEnrollmentFee();

        $total = $enrollmentFee;

        foreach ($this->courses as $course) {
            $total += (float) ($course->monthly_fee ?? 0);
        }

        return $total;
    }

    public function syncReceivable(): ?\App\Models\AccountReceivable
    {
        $this->loadMissing(['program', 'courses', 'installments']);

        $receivable = $this->receivable ?: \App\Models\AccountReceivable::where('enrollment_id', $this->id)->first();

        if ($this->status === 'cancelled') {
            if ($receivable) {
                $paidAmount = (float) $receivable->transactions()->where('status', 'completed')->sum('amount');
                if ($paidAmount <= 0) {
                    $receivable->delete();
                    $this->setRelation('receivable', null);
                    return null;
                } else {
                    $receivable->update([
                        'amount_total' => $paidAmount,
                        'balance_due' => 0.0,
                        'status' => 'paid',
                    ]);
                    $this->setRelation('receivable', $receivable);
                    return $receivable;
                }
            }
            $this->installments()->where('status', 'pending')->delete();
            $this->setRelation('receivable', null);
            return null;
        }

        if ($this->is_free_trial) {
            if ($receivable) {
                $receivable->delete();
                $this->setRelation('receivable', null);
            }
            return null;
        }

        $program = $this->program;
        $courses = $this->courses;
        if (!$program || $courses->isEmpty()) {
            return null;
        }

        $firstCourse = $courses->first();
        if (!$firstCourse || $firstCourse->branch_id === null) {
            return null;
        }

        $enrollmentFee = $this->getEnrollmentFee();

        // Calculate amount total
        if ($receivable && $receivable->is_custom_amount) {
            $amountTotal = (float) $receivable->amount_total;
        } else {
            $amountTotal = $enrollmentFee;
            foreach ($courses as $course) {
                $months = 1;
                if ($course->start_date && $course->end_date) {
                    $start = \Carbon\Carbon::parse($course->start_date)->startOfMonth();
                    $end = \Carbon\Carbon::parse($course->end_date)->startOfMonth();
                    $months = max(1, $start->diffInMonths($end) + 1);
                }
                $amountTotal += (float) ($course->monthly_fee ?? 0) * $months;
            }
        }

        $courseTitles = $courses->pluck('title')->join(', ');
        $title = 'Inscripción + mensualidades #' . $this->id . ' - ' . ($program->name ?? 'Programa') . ' (' . $courseTitles . ')';

        if ($this->payment_status === 'pending') {
            if (!$receivable) {
                $receivable = \App\Models\AccountReceivable::create([
                    'branch_id' => $firstCourse->branch_id,
                    'enrollment_id' => $this->id,
                    'title' => $title,
                    'amount_total' => $amountTotal,
                    'balance_due' => $amountTotal,
                    'currency' => 'USD',
                    'status' => 'pending',
                ]);
            } else {
                $updateData = [
                    'branch_id' => $firstCourse->branch_id,
                    'currency' => 'USD',
                    'status' => 'pending',
                ];
                if (!$receivable->is_custom_amount) {
                    $updateData['title'] = $title;
                    $updateData['amount_total'] = $amountTotal;
                    $updateData['balance_due'] = $amountTotal;
                }
                $receivable->update($updateData);
            }
        } else {
            // payment_status is paid
            if (!$receivable) {
                $receivable = \App\Models\AccountReceivable::create([
                    'branch_id' => $firstCourse->branch_id,
                    'enrollment_id' => $this->id,
                    'title' => $title,
                    'amount_total' => $amountTotal,
                    'balance_due' => $amountTotal,
                    'currency' => 'USD',
                    'status' => 'pending',
                ]);
            } else {
                $updateData = [
                    'branch_id' => $firstCourse->branch_id,
                    'currency' => 'USD',
                ];
                if (!$receivable->is_custom_amount) {
                    $updateData['title'] = $title;
                    $updateData['amount_total'] = $amountTotal;
                }
                $receivable->update($updateData);
            }
        }

        // Link transactions if not done yet
        $hasLinked = $receivable->transactions()->exists();
        if (!$hasLinked) {
            \App\Models\Transaction::query()
                ->where('enrollment_id', $this->id)
                ->where('type', 'income')
                ->whereNull('account_receivable_id')
                ->update(['account_receivable_id' => $receivable->id]);
            
            $receivable->load('transactions');
        }

        // Now calculate balance
        $paidAmount = (float) $receivable->transactions()->where('status', 'completed')->sum('amount');
        $balance = max(0.0, (float) $amountTotal - $paidAmount);

        $status = 'pending';
        if ($balance <= 0) {
            $status = 'paid';
        } elseif ($paidAmount > 0) {
            $status = 'partial';
        }

        $receivable->update([
            'balance_due' => $balance,
            'status' => $status,
        ]);

        // Sync installments status
        $remainingPaid = max(0.0, $paidAmount - $enrollmentFee);
        $installments = $this->installments()->orderBy('due_date')->get();

        foreach ($installments as $installment) {
            $installmentAmount = (float) $installment->amount;
            if ($remainingPaid >= $installmentAmount) {
                $installment->update([
                    'status' => 'paid',
                    'paid_at' => $installment->paid_at ?? now(),
                ]);
                $remainingPaid -= $installmentAmount;
            } elseif ($remainingPaid > 0) {
                $installment->update([
                    'status' => 'pending',
                ]);
                $remainingPaid = 0.0;
            } else {
                if ($installment->status === 'paid') {
                    $installment->update([
                        'status' => 'pending',
                        'paid_at' => null,
                    ]);
                }
            }
        }

        $this->setRelation('receivable', $receivable);
        return $receivable;
    }
}
