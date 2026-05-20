<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'representative_name',
        'child_name',
        'child_age',
        'program_id',
        'branch_id',
        'phone',
        'email',
        'comment',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function getIsReadAttribute(): bool
    {
        return $this->read_at !== null;
    }
}
