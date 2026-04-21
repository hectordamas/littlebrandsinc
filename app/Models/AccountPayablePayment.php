<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountPayablePayment extends Model
{
    protected $fillable = [
        'account_payable_id',
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

    public function payable()
    {
        return $this->belongsTo(AccountPayable::class, 'account_payable_id');
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
