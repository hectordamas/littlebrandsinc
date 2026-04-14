<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{Student, LBClass};

class Enrollment extends Model
{
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function lbclass()
    {
        return $this->belongsTo(LBClass::class);
    }
}
