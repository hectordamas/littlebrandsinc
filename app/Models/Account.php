<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
