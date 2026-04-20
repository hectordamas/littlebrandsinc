<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Course, Branch, LBClass};

class CoursesController extends Controller
{
    public function index(){
        $courses = Course::with(['branch', 'lbClass'])->get();
        return view('courses.index', [
            'courses' => $courses
        ]);
    }   

    public function create(){
        $branches = Branch::all();

        return view('courses.create', [
            'branches' => $branches,
        ]);
    }

    public function store(Request $request){
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'min_age' => 'nullable|integer|min:0',
            'max_age' => 'nullable|integer|min:0',
            'capacity' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
           //'branch_id' => 'required|exists:branches,id',
        ]);

        dd($request->all());

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
