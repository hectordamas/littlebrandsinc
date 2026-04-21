<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountPayable extends Model
{
    protected $fillable = [
        'branch_id',
        'vendor_name',
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

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'account_payable_id')->orderByDesc('created_at');
    }
}
