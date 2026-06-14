<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BirthdayInquiry extends Model
{
    protected $fillable = [
        'representative_name',
        'phone',
        'email',
        'age_to_celebrate',
        'event_date',
        'start_time',
        'location_type',
        'event_location',
        'estimated_children',
        'guest_age_range',
        'program_interest',
        'additional_services',
        'comments',
        'read_at',
    ];

    protected $casts = [
        'additional_services' => 'array',
        'read_at' => 'datetime',
        'event_date' => 'date',
    ];

    public function getIsReadAttribute(): bool
    {
        return $this->read_at !== null;
    }
}
