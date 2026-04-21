<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Transaction;

class Account extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'type',
        'currency',
        'active',
        'meta',
    ];

    protected $casts = [
        'active' => 'boolean',
        'meta' => 'array',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function receivableTransactions()
    {
        return $this->hasMany(Transaction::class)->whereNotNull('account_receivable_id');
    }

    public function payableTransactions()
    {
        return $this->hasMany(Transaction::class)->whereNotNull('account_payable_id');
    }
}
