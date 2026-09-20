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
            ->withPivot(['custom_amount', 'status'])
            ->withTimestamps();
    }

    public function activeCourses()
    {
        return $this->belongsToMany(Course::class, 'enrollment_course')
            ->wherePivot('status', '!=', 'cancelled')
            ->withPivot(['custom_amount', 'status'])
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

    public function getCourseAmount($course, int $courseIndex = 0): float
    {
        $courseModel = $course;
        if (is_object($course) && (!$course->pivot || $course->pivot->custom_amount === null) && $this->relationLoaded('courses')) {
            $matched = $this->courses->firstWhere('id', $course->id);
            if ($matched && $matched->pivot) {
                $courseModel = $matched;
            }
        }

        if (is_object($courseModel) && $courseModel->pivot && $courseModel->pivot->custom_amount !== null) {
            return (float) $courseModel->pivot->custom_amount;
        }

        $months = 1;
        if ($courseModel->start_date && $courseModel->end_date) {
            $start = \Carbon\Carbon::parse($courseModel->start_date)->startOfMonth();
            $end = \Carbon\Carbon::parse($courseModel->end_date)->startOfMonth();
            $months = max(1, $start->diffInMonths($end) + 1);
        }

        $amount = (float) ($courseModel->monthly_fee ?? 0) * $months;

        if ($courseIndex === 0) {
            $amount += $this->getEnrollmentFee();
        }

        return $amount;
    }

    public function getInitialChargeAmount(): float
    {
        $this->loadMissing(['program', 'courses']);

        $total = 0.0;
        foreach ($this->courses as $index => $course) {
            $total += $this->getCourseAmount($course, $index);
        }

        return $total;
    }

    public function getCourseBreakdown(): array
    {
        $this->loadMissing(['courses', 'transactions']);

        $completedTxs = $this->transactions
            ->where('status', 'completed')
            ->where('type', 'income');

        $generalPaid = (float) $completedTxs->whereNull('course_id')->sum('amount');
        $tempGeneral = $generalPaid;

        $breakdown = [];
        foreach ($this->courses as $idx => $course) {
            $pivotStatus = $course->pivot->status ?? 'active';
            $isCancelled = ($this->status === 'cancelled' || $pivotStatus === 'cancelled');

            $courseAmount = $this->getCourseAmount($course, $idx);
            $directTxs = $completedTxs->where('course_id', $course->id);
            $directPaid = (float) $directTxs->sum('amount');

            $needed = max(0.00, $courseAmount - $directPaid);
            $allocatedGeneral = min($needed, max(0.00, $tempGeneral));
            $tempGeneral = max(0.00, $tempGeneral - $allocatedGeneral);

            $coursePaid = $directPaid + $allocatedGeneral;
            $courseBalance = ($isCancelled || $this->is_free_trial) ? 0.00 : max(0.00, $courseAmount - $coursePaid);

            $breakdown[$course->id] = [
                'course' => $course,
                'index' => $idx,
                'status' => $isCancelled ? 'cancelled' : 'active',
                'is_cancelled' => $isCancelled,
                'amount' => $courseAmount,
                'direct_paid' => $directPaid,
                'allocated_general' => $allocatedGeneral,
                'paid' => $coursePaid,
                'balance' => $courseBalance,
            ];
        }

        if ($tempGeneral > 0 && !empty($breakdown)) {
            $firstActiveId = null;
            foreach ($breakdown as $cId => $info) {
                if (!$info['is_cancelled']) {
                    $firstActiveId = $cId;
                    break;
                }
            }
            $targetId = $firstActiveId ?? array_key_first($breakdown);
            $breakdown[$targetId]['allocated_general'] += $tempGeneral;
            $breakdown[$targetId]['paid'] += $tempGeneral;
            if (!$breakdown[$targetId]['is_cancelled'] && !$this->is_free_trial) {
                $breakdown[$targetId]['balance'] = max(0.00, $breakdown[$targetId]['amount'] - $breakdown[$targetId]['paid']);
            }
        }

        return $breakdown;
    }

    public function syncReceivable(): ?\App\Models\AccountReceivable
    {
        $this->load(['program', 'courses', 'installments']);

        $receivable = $this->receivable ?: \App\Models\AccountReceivable::where('enrollment_id', $this->id)->first();

        if ($receivable) {
            \App\Models\Transaction::query()
                ->where('enrollment_id', $this->id)
                ->where('type', 'income')
                ->whereNull('account_receivable_id')
                ->update(['account_receivable_id' => $receivable->id]);
        }

        $activeCourses = $this->courses->filter(function ($course) {
            return ($course->pivot->status ?? 'active') !== 'cancelled';
        });

        // If enrollment is cancelled OR all courses in enrollment are cancelled
        if ($this->status === 'cancelled' || ($this->courses->isNotEmpty() && $activeCourses->isEmpty())) {
            if ($receivable) {
                $paidAmount = (float) $receivable->transactions()->where('status', 'completed')->sum('amount');
                if ($paidAmount <= 0) {
                    $paidAmount = (float) $this->transactions()->where('status', 'completed')->where('type', 'income')->sum('amount');
                }
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

        $firstCourse = $activeCourses->first() ?: $courses->first();
        if (!$firstCourse || $firstCourse->branch_id === null) {
            return null;
        }

        $enrollmentFee = $this->getEnrollmentFee();

        // Calculate amount total as sum of active course amounts + any paid amount on cancelled courses
        $completedTxs = $this->transactions()->where('status', 'completed')->where('type', 'income')->get();
        $amountTotal = 0.0;
        foreach ($courses as $index => $course) {
            $isCourseCancelled = ($course->pivot->status ?? 'active') === 'cancelled';
            if (!$isCourseCancelled) {
                $amountTotal += $this->getCourseAmount($course, $index);
            } else {
                $coursePaid = (float) $completedTxs->where('course_id', $course->id)->sum('amount');
                $amountTotal += $coursePaid;
            }
        }

        $activeTitles = $activeCourses->pluck('title')->join(', ');
        $title = 'Inscripción + mensualidades #' . $this->id . ' - ' . ($program->name ?? 'Programa') . ($activeTitles ? ' (' . $activeTitles . ')' : '');

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
            $receivable->update([
                'branch_id' => $firstCourse->branch_id,
                'currency' => 'USD',
                'title' => $title,
                'amount_total' => $amountTotal,
            ]);
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
