<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{Account, Branch, Course, Enrollment, Student};

class Transaction extends Model
{
    protected $fillable = [
        'enrollment_id',
        'student_id',
        'course_id',
        'branch_id',
        'account_id',
        'account_receivable_id',
        'account_payable_id',
        'amount',
        'currency',
        'type',
        'status',
        'payment_method',
        'reference',
        'description',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function receivable()
    {
        return $this->belongsTo(AccountReceivable::class, 'account_receivable_id');
    }

    public function payable()
    {
        return $this->belongsTo(AccountPayable::class, 'account_payable_id');
    }
}
