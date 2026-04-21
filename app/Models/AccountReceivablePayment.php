<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountReceivablePayment extends Model
{
    protected $fillable = [
        'account_receivable_id',
        'branch_id',
        'account_id',
        'transaction_id',
        'amount',
        'currency',
        'payment_date',
        'reference',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function receivable()
    {
        return $this->belongsTo(AccountReceivable::class, 'account_receivable_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
