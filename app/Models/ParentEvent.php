<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentEvent extends Model
{
    protected $fillable = [
        'type',
        'title',
        'message',
        'event_date',
        'send_at',
        'sent_at',
    ];

    protected $casts = [
        'event_date' => 'date',
        'send_at' => 'datetime',
        'sent_at' => 'datetime',
    ];
}
