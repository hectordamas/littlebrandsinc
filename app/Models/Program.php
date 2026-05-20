<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'active',
    ];

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function contactMessages()
    {
        return $this->hasMany(ContactMessage::class);
    }
}
