<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{Branch, LBClass, Enrollment};

class Course extends Model
{
    protected $fillable = ['title', 'description', 'min_age', 'max_age', 'capacity', 'price', 'monthly_fee', 'start_date', 'end_date', 'branch_id', 'active'];

    protected $casts = [
        'price' => 'decimal:2',
        'monthly_fee' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function classes()
    {
        return $this->hasMany(LBClass::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }
}
