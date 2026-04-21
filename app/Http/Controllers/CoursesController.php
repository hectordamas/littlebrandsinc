<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\{Course, Branch, LBClass, User};

class CoursesController extends Controller
{
    public function calendar()
    {
        $branches = Branch::orderBy('name')->get();

        return view('courses.calendar', [
            'branches' => $branches,
        ]);
    }

    public function calendarEvents(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => 'nullable|integer|exists:branches,id',
            'start' => 'nullable|date',
            'end' => 'nullable|date',
        ]);

        $branchId = isset($validated['branch_id']) ? (int) $validated['branch_id'] : null;

        $classes = LBClass::query()
            ->with(['course', 'branch', 'coach'])
            ->when($branchId, function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })
            ->when(isset($validated['start']), function ($query) use ($validated) {
                $query->whereDate('date', '>=', $validated['start']);
            })
            ->when(isset($validated['end']), function ($query) use ($validated) {
                $query->whereDate('date', '<=', $validated['end']);
            })
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        $events = $classes->map(function (LBClass $class) {
            $courseTitle = optional($class->course)->title ?? 'Clase';
            $branchName = optional($class->branch)->name ?? 'Sin sede';
            $coachName = optional($class->coach)->name ?? 'Sin coach';
            $start = $class->date . 'T' . $class->start_time;
            $end = $class->date . 'T' . $class->end_time;

            return [
                'id' => $class->id,
                'title' => $courseTitle,
                'start' => $start,
                'end' => $end,
                'extendedProps' => [
                    'branch' => $branchName,
                    'coach' => $coachName,
                    'time' => substr((string) $class->start_time, 0, 5) . ' - ' . substr((string) $class->end_time, 0, 5),
                ],
            ];
        })->values();

        return response()->json($events);
    }

    public function index()
    {
        $courses = Course::orderBy('id', 'desc')->get();

        return view('courses.index', [
            'courses' => $courses,
        ]);
    }

    public function create()
    {
        $branches = Branch::orderBy('id', 'desc')->get();
        $coaches = User::where('role', 'Coach')->get();

        return view('courses.create', [
            'branches' => $branches,
            'coaches' => $coaches

        ]);
    }

    public function store(Request $request)
    {
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
            $class = new LBClass();
            $class->course_id = $course->id;
            $class->branch_id = $request->branch_id;
            $class->date = $classData['date'] ?? null;
            $class->start_time = $classData['start_time'] ?? null;
            $class->end_time = $classData['end_time'] ?? null;
            $class->coach_id = $request->coach_id;
            $class->save();
        }

        return redirect()->route('courses.index')->with('success', 'Curso creado exitosamente');
    }

    public function edit($id)
    {
        $course = Course::findOrFail($id);
        $branches = Branch::orderBy('id', 'desc')->get();
        $coaches = User::where('role', 'Coach')->get();
        $classes = LBClass::where('course_id', $course->id)->get();

        return view('courses.edit', [
            'course' => $course,
            'branches' => $branches,
            'coaches' => $coaches,
            'classes' => $classes,
        ]);
    }

    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);

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

        return redirect()->route('courses.index')->with('success', 'Curso actualizado exitosamente');
    }

    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $course->delete();

        return redirect()->route('courses.index')->with('success', 'Curso eliminado exitosamente');
    }

    public function storeClass(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'branch_id' => 'required|exists:branches,id',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'coach_id' => 'nullable|exists:users,id',
        ]);

        $class = new LBClass();
        $class->course_id = $request->course_id;
        $class->branch_id = $request->branch_id;
        $class->date = $request->date;
        $class->start_time = $request->start_time;
        $class->end_time = $request->end_time;
        $class->coach_id = $request->coach_id;
        $class->save();

        return redirect()->back()->with('success', 'Clase agregada exitosamente');
    }

    public function updateClass(Request $request, $id)
    {
        $class = LBClass::findOrFail($id);
        $class->date = $request->date;
        $class->start_time = $request->start_time;
        $class->end_time = $request->end_time;

        $class->save();

        return redirect()->back()->with('success', 'Clase actualizada exitosamente');
    }

    public function destroyClass($id)
    {
        $class = LBClass::findOrFail($id);
        $class->delete();

        return redirect()->back()->with('success', 'Clase eliminada exitosamente');
    }
}
