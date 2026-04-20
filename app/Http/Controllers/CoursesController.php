<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Course, Branch, LBClass};

class CoursesController extends Controller
{
    public function index(){
        $courses = Course::with(['branch', 'lbClass'])->orderBy('id', 'desc')->get();
        return view('courses.index', [
            'courses' => $courses
        ]);
    }   

    public function create(){
        $branches = Branch::orderBy('id', 'desc')->get();

        return view('courses.create', [
            'branches' => $branches,
        ]);
    }

    public function store(Request $request){
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'min_age' => 'nullable|integer|min:0',
            'max_age' => 'nullable|integer|min:0',
            'capacity' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
           'branch_id' => 'required|exists:branches,id',
        ]);

        $course = new Course();
        $course->title = $request->title;
        $course->description = $request->description;
        $course->min_age = $request->min_age;
        $course->max_age = $request->max_age;
        $course->capacity = $request->capacity;
        $course->price = $request->price;
        $course->start_date = $request->start_date;
        $course->end_date = $request->end_date;
        $course->branch_id = $request->branch_id;
        $course->active = $request->active ?? false;
        $course->save();

        foreach ($request->sessions as $classData) {
            LBClass::create([
                'name' => $classData['name'] ?? null,
                'day' => $classData['day'] ?? null,
                'start_time' => $classData['start_time'] ?? null,
                'end_time' => $classData['end_time'] ?? null,
                'teacher_id' => $classData['teacher_id'] ?? null,
            ]);
        }

        return redirect()->route('courses.index')->with('success', 'Curso creado exitosamente');
    }
}
