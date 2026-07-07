<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LBClass;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CoachPortalController extends Controller
{
    public function calendar()
    {
        return view('coach.calendar');
    }

    public function events(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start' => 'nullable|date',
            'end' => 'nullable|date',
        ]);

        $coachId = (int) Auth::id();

        $classes = LBClass::query()
            ->with([
                'course.enrollments.student',
                'course.coaches',
                'branch',
                'attendances',
            ])
            ->where(function ($query) use ($coachId) {
                $query->where('coach_id', $coachId)
                    ->orWhereHas('course.coaches', function ($coachQuery) use ($coachId) {
                        $coachQuery->where('users.id', $coachId);
                    });
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
            $course = $class->course;
            $students = optional($course)->enrollments
                ? $course->enrollments
                    ->where('status', '!=', 'cancelled')
                    ->map(function ($enrollment) use ($class) {
                        $student = $enrollment->student;
                        $attendance = $class->attendances->firstWhere('student_id', optional($student)->id);

                        return [
                            'student_id' => optional($student)->id,
                            'student_name' => optional($student)->name ?? 'Sin nombre',
                            'check_in' => $attendance->status ?? 'pending',
                            'notes' => $attendance->notes ?? null,
                            'is_free_trial' => (bool) $enrollment->is_free_trial,
                        ];
                    })
                    ->filter(fn ($row) => ! empty($row['student_id']))
                    ->values()
                : collect();

            $dateStr = $class->date instanceof \Carbon\Carbon ? $class->date->format('Y-m-d') : (string) $class->date;
            $start = $dateStr . 'T' . $class->start_time;
            $end = $dateStr . 'T' . $class->end_time;

            return [
                'id' => $class->id,
                'title' => optional($course)->title ?? 'Clase',
                'start' => $start,
                'end' => $end,
                'extendedProps' => [
                    'branch' => optional($class->branch)->name ?? 'Sin sede',
                    'time' => substr((string) $class->start_time, 0, 5) . ' - ' . substr((string) $class->end_time, 0, 5),
                    'enrolled_count' => $students->count(),
                    'students' => $students,
                    'observations' => $class->observations,
                ],
            ];
        })->values();

        return response()->json($events);
    }

    public function markAttendance(Request $request, LBClass $class): RedirectResponse|JsonResponse
    {
        $coachId = (int) Auth::id();

        $isAllowedCoach = (int) $class->coach_id === $coachId || $class->course()
            ->whereHas('coaches', function ($query) use ($coachId) {
                $query->where('users.id', $coachId);
            })->exists();

        if (! $isAllowedCoach) {
            abort(403);
        }

        $validated = $request->validate([
            'attendance' => 'nullable|array',
            'attendance.*' => 'required|in:present,absent,late,pending',
            'notes' => 'nullable|array',
            'notes.*' => 'nullable|string|max:500',
            'observations' => 'nullable|string|max:2000',
        ]);

        if (array_key_exists('observations', $validated)) {
            $class->observations = $validated['observations'];
            $class->save();
        }

        foreach (($validated['attendance'] ?? []) as $studentId => $status) {
            Attendance::query()->updateOrCreate(
                [
                    'class_id' => $class->id,
                    'student_id' => (int) $studentId,
                ],
                [
                    'date' => $class->date,
                    'status' => $status,
                    'notes' => data_get($validated, 'notes.'.$studentId),
                ]
            );
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Asistencia y observaciones actualizadas correctamente.',
            ]);
        }

        return back()->with('success', 'Asistencia actualizada correctamente.');
    }
}
