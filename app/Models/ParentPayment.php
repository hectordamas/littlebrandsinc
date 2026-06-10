<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentPayment extends Model
{
    protected $fillable = [
        'user_id',
        'account_receivable_id',
        'amount',
        'reference',
        'receipt_path',
        'receipt_original_name',
        'status',
        'approved_by',
        'approved_at',
        'rejected_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function receivable()
    {
        return $this->belongsTo(AccountReceivable::class, 'account_receivable_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
