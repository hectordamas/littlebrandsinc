<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Enrollment, Student, User, Course};

class EnrollmentController extends Controller
{
    public function index(){
        $enrollments = Enrollment::orderBY('id', 'desc')->get();
        $students = Student::orderBy('id', 'desc')->get();
        $courses = Course::orderBy('id', 'desc')->get();

        $parents = User::where('role', 'Padre')->orderBy('id', 'desc')->get();

        return view('enrollments.index', [
            'enrollments' => $enrollments, 
            'students' => $students,
            'parents' => $parents,
            'courses' => $courses
        ]);
    }

    public function store(Request $request){

    }
}
