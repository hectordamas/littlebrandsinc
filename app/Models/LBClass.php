<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{Enrollment};

class LBClass extends Model
{
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }
}
