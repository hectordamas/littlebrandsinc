<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'enrollment_id',
        'student_id',
        'course_id',
        'branch_id',
        'account_id',
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
}
