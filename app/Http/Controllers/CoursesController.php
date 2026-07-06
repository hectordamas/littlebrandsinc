<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\{Course, Branch, LBClass, Program, User};
use Illuminate\Support\Facades\DB;

class CoursesController extends Controller
{
    /**
     * Devuelve el porcentaje de ocupación del curso.
     */
    public function occupancy($id)
    {
        $course = Course::withCount(['enrollments' => function ($q) {
            $q->where('status', '!=', 'cancelled');
        }])->findOrFail($id);
        $capacity = $course->capacity ?? 0;
        $enrolled = $course->enrollments_count;
        $percent = $capacity > 0 ? round(($enrolled / $capacity) * 100) : 0;
        return response()->json([
            'capacity' => $capacity,
            'enrolled' => $enrolled,
            'percent' => $percent,
        ]);
    }

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
            ->with([
                'course' => function ($query) {
                    $query->withCount([
                        'enrollments as active_enrollments_count' => function ($enrollmentsQuery) {
                            $enrollmentsQuery->where('status', '!=', 'cancelled');
                        },
                    ])->with(['enrollments' => function ($eq) {
                        $eq->where('status', '!=', 'cancelled')->with(['student', 'parent']);
                    }, 'program']);
                },
                'branch',
                'coach',
            ])
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
            $date = optional($class->date)->format('Y-m-d') ?: (string) $class->date;
            $start = $date . 'T' . $class->start_time;
            $end = $date . 'T' . $class->end_time;

            $enrolledStudents = optional($class->course)->enrollments ? optional($class->course)->enrollments->map(function ($enrollment) {
                return [
                    'student_name' => $enrollment->student->name ?? 'N/A',
                    'student_age' => $enrollment->student->birthdate ? \Carbon\Carbon::parse($enrollment->student->birthdate)->age : null,
                    'parent_name' => $enrollment->parent->name ?? 'N/A',
                    'parent_whatsapp' => ($enrollment->parent->dial_code ?? '') . ' ' . ($enrollment->parent->whatsapp ?? ''),
                    'parent_email' => $enrollment->parent->email ?? 'N/A',
                    'payment_status' => $enrollment->payment_status,
                    'is_free_trial' => (bool) $enrollment->is_free_trial,
                ];
            })->values()->all() : [];

            return [
                'id' => $class->id,
                'title' => $courseTitle,
                'start' => $start,
                'end' => $end,
                'extendedProps' => [
                    'course_description' => optional($class->course)->description,
                    'course_start_date' => optional($class->course)->start_date,
                    'course_end_date' => optional($class->course)->end_date,
                    'course_price' => optional(optional($class->course)->program)->enrollment_fee,
                    'course_monthly_fee' => optional($class->course)->monthly_fee,
                    'course_capacity' => optional($class->course)->capacity,
                    'enrolled_children' => (int) (optional($class->course)->active_enrollments_count ?? 0),
                    'branch' => $branchName,
                    'coach' => $coachName,
                    'time' => substr((string) $class->start_time, 0, 5) . ' - ' . substr((string) $class->end_time, 0, 5),
                    'enrolled_students' => $enrolledStudents,
                ],
            ];
        })->values();

        return response()->json($events);
    }

    public function index()
    {
        $courses = Course::with(['branch', 'program'])->orderBy('id', 'desc')->get();

        return view('courses.index', [
            'courses' => $courses,
        ]);
    }

    public function create()
    {
        $branches = Branch::orderBy('id', 'desc')->get();
        $coaches = User::where('role', 'Coach')->get();
        $programs = Program::query()->where('active', true)->orderBy('name')->get();

        return view('courses.create', [
            'branches' => $branches,
            'coaches' => $coaches,
            'programs' => $programs,

        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'program_id' => 'required|integer|exists:programs,id',
            'description' => 'nullable|string',
            'min_age' => 'nullable|numeric|min:0',
            'max_age' => 'nullable|numeric|min:0',
            'capacity' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'monthly_fee' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'branch_id' => 'required|exists:branches,id',
            'coach_ids' => 'required|array|min:1',
            'coach_ids.*' => 'integer|exists:users,id',
            'sessions' => 'nullable|array',
            'sessions.*.date' => 'required_with:sessions|date',
            'sessions.*.start_time' => 'required_with:sessions',
            'sessions.*.end_time' => 'required_with:sessions',
            'sessions.*.coach_id' => 'nullable|integer|exists:users,id',
            'recurrence_sessions' => 'nullable|array',
            'recurrence_sessions.*.date' => 'required_with:recurrence_sessions|date',
            'recurrence_sessions.*.start_time' => 'required_with:recurrence_sessions',
            'recurrence_sessions.*.end_time' => 'required_with:recurrence_sessions',
            'recurrence_sessions.*.coach_id' => 'nullable|integer|exists:users,id',
        ]);

        DB::transaction(function () use ($request): void {
            $course = new Course();
            $course->title = $request->title;
            $course->program_id = $request->program_id;
            $course->description = $request->description;
            $course->min_age = $request->min_age;
            $course->max_age = $request->max_age;
            $course->capacity = $request->capacity;
            $course->price = $request->price;
            $course->monthly_fee = $request->monthly_fee;
            $course->start_date = $request->start_date;
            $course->end_date = $request->end_date;
            $course->branch_id = $request->branch_id;
            $course->active = $request->active ?? false;
            $course->save();

            $coachIds = collect($request->input('coach_ids', []))
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            $course->coaches()->sync($coachIds);

            $sessionRows = collect($request->input('recurrence_sessions', []));
            if ($sessionRows->isEmpty()) {
                $sessionRows = collect($request->input('sessions', []));
            }

            $defaultCoachId = $coachIds[0] ?? null;

            $sessionRows
                ->filter(fn ($row) => ! empty($row['date']) && ! empty($row['start_time']) && ! empty($row['end_time']))
                ->each(function ($classData) use ($course, $request, $coachIds, $defaultCoachId): void {
                    $coachId = ! empty($classData['coach_id']) ? (int) $classData['coach_id'] : $defaultCoachId;
                    if ($coachId && ! in_array($coachId, $coachIds, true)) {
                        $coachId = $defaultCoachId;
                    }

                    $class = new LBClass();
                    $class->course_id = $course->id;
                    $class->branch_id = $request->branch_id;
                    $class->date = $classData['date'];
                    $class->start_time = $classData['start_time'];
                    $class->end_time = $classData['end_time'];
                    $class->coach_id = $coachId;
                    $class->save();
                });
        });

        return redirect()->route('courses.index')->with('success', 'Curso creado exitosamente');
    }

    public function edit($id)
    {
        $course = Course::with(['enrollments' => function ($q) {
            $q->where('status', '!=', 'cancelled')->with(['student', 'parent']);
        }])->findOrFail($id);
        $branches = Branch::orderBy('id', 'desc')->get();
        $coaches = User::where('role', 'Coach')->get();
        $classes = LBClass::where('course_id', $course->id)->get();
        $programs = Program::query()->where('active', true)->orderBy('name')->get();
        $selectedCoachIds = $course->coaches()->pluck('users.id')->all();

        return view('courses.edit', [
            'course' => $course,
            'branches' => $branches,
            'coaches' => $coaches,
            'classes' => $classes,
            'programs' => $programs,
            'selectedCoachIds' => $selectedCoachIds,
        ]);
    }

    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'program_id' => 'required|integer|exists:programs,id',
            'description' => 'nullable|string',
            'min_age' => 'nullable|numeric|min:0',
            'max_age' => 'nullable|numeric|min:0',
            'capacity' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'monthly_fee' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'branch_id' => 'required|exists:branches,id',
            'coach_ids' => 'required|array|min:1',
            'coach_ids.*' => 'integer|exists:users,id',
            'auto_extend_classes' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($course, $request): void {
            $oldStartDate = $course->start_date;
            $oldEndDate = $course->end_date;

            $course->title = $request->title;
            $course->program_id = $request->program_id;
            $course->description = $request->description;
            $course->min_age = $request->min_age;
            $course->max_age = $request->max_age;
            $course->capacity = $request->capacity;
            $course->price = $request->price;
            $course->monthly_fee = $request->monthly_fee;
            $course->start_date = $request->start_date;
            $course->end_date = $request->end_date;
            $course->branch_id = $request->branch_id;
            $course->active = $request->active ?? false;
            $course->save();

            $coachIds = collect($request->input('coach_ids', []))
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            $course->coaches()->sync($coachIds);

            // Keep branch_id of classes synchronized with the course
            LBClass::query()
                ->where('course_id', $course->id)
                ->update(['branch_id' => $course->branch_id]);

            $defaultCoachId = $coachIds[0] ?? null;
            LBClass::query()
                ->where('course_id', $course->id)
                ->whereNotNull('coach_id')
                ->whereNotIn('coach_id', $coachIds)
                ->update(['coach_id' => $defaultCoachId]);

            // If range changed, adjust classes
            if ($oldStartDate !== $course->start_date || $oldEndDate !== $course->end_date) {
                // 1. Delete class sessions outside the new range
                LBClass::query()
                    ->where('course_id', $course->id)
                    ->where(function ($query) use ($course) {
                        $query->whereDate('date', '<', $course->start_date)
                              ->orWhereDate('date', '>', $course->end_date);
                    })
                    ->delete();

                // 2. If range expanded and auto_extend is checked, generate classes based on remaining ones
                if ($request->boolean('auto_extend_classes', true)) {
                    $existingClasses = LBClass::query()
                        ->where('course_id', $course->id)
                        ->get();

                    $existingDates = $existingClasses->pluck('date')
                        ->map(fn($d) => \Carbon\Carbon::parse($d)->toDateString())
                        ->toArray();

                    $patterns = [];
                    foreach ($existingClasses as $class) {
                        $dayOfWeek = \Carbon\Carbon::parse($class->date)->dayOfWeek;
                        $startTime = \Carbon\Carbon::parse($class->start_time)->format('H:i');
                        $endTime = \Carbon\Carbon::parse($class->end_time)->format('H:i');
                        $key = "{$dayOfWeek}_{$startTime}_{$endTime}_{$class->coach_id}";
                        $patterns[$key] = [
                            'day_of_week' => $dayOfWeek,
                            'start_time' => $class->start_time,
                            'end_time' => $class->end_time,
                            'coach_id' => $class->coach_id,
                        ];
                    }

                    if (!empty($patterns)) {
                        $start = \Carbon\Carbon::parse($course->start_date);
                        $end = \Carbon\Carbon::parse($course->end_date);

                        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                            $dateStr = $date->toDateString();
                            if (in_array($dateStr, $existingDates, true)) {
                                continue;
                            }

                            $dayOfWeek = $date->dayOfWeek;
                            foreach ($patterns as $pattern) {
                                if ($pattern['day_of_week'] == $dayOfWeek) {
                                    $newClass = new LBClass();
                                    $newClass->course_id = $course->id;
                                    $newClass->branch_id = $course->branch_id;
                                    $newClass->date = $dateStr;
                                    $newClass->start_time = $pattern['start_time'];
                                    $newClass->end_time = $pattern['end_time'];
                                    $newClass->coach_id = $pattern['coach_id'];
                                    $newClass->save();
                                }
                            }
                        }
                    }
                }
            }
        });

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
        $course = Course::query()->with('coaches')->findOrFail((int) $request->input('course_id'));
        $allowedCoachIds = $course->coaches->pluck('id')->all();

        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'branch_id' => 'required|exists:branches,id',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'coach_id' => 'nullable|exists:users,id',
        ]);

        $coachId = $request->coach_id ? (int) $request->coach_id : null;
        if ($coachId && ! in_array($coachId, $allowedCoachIds, true)) {
            return redirect()->back()->withErrors([
                'coach_id' => 'El entrenador debe estar asignado al curso.',
            ]);
        }

        $class = new LBClass();
        $class->course_id = $course->id;
        $class->branch_id = $request->branch_id;
        $class->date = $request->date;
        $class->start_time = $request->start_time;
        $class->end_time = $request->end_time;
        $class->coach_id = $coachId;
        $class->save();

        return redirect()->back()->with('success', 'Clase agregada exitosamente');
    }

    public function updateClass(Request $request, $id)
    {
        $class = LBClass::findOrFail($id);

        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'coach_id' => 'nullable|exists:users,id',
        ]);

        $allowedCoachIds = $class->course
            ? $class->course->coaches()->pluck('users.id')->all()
            : [];

        $coachId = $request->coach_id ? (int) $request->coach_id : null;
        if ($coachId && ! in_array($coachId, $allowedCoachIds, true)) {
            return redirect()->back()->withErrors([
                'coach_id' => 'El entrenador debe estar asignado al curso.',
            ]);
        }

        $class->date = $request->date;
        $class->start_time = $request->start_time;
        $class->end_time = $request->end_time;
        $class->coach_id = $coachId;

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
