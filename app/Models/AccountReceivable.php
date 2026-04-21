<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountReceivable extends Model
{
    protected $fillable = [
        'branch_id',
        'enrollment_id',
        'title',
        'amount_total',
        'balance_due',
        'currency',
        'status',
        'due_date',
        'notes',
    ];

    protected $casts = [
        'amount_total' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'account_receivable_id')->orderByDesc('created_at');
    }
}
