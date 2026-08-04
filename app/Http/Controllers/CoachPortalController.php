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
                'course.enrollments.receivable',
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

                        $cxcBalanceDue = 0.00;
                        if (! $enrollment->is_free_trial) {
                            if ($enrollment->receivable) {
                                $cxcBalanceDue = (float) $enrollment->receivable->balance_due;
                            } else {
                                $cxcBalanceDue = (float) $enrollment->getInitialChargeAmount();
                            }
                        }

                        return [
                            'student_id' => optional($student)->id,
                            'student_name' => optional($student)->name ?? 'Sin nombre',
                            'check_in' => $attendance->status ?? 'pending',
                            'notes' => $attendance->notes ?? null,
                            'payment_status' => $enrollment->payment_status,
                            'is_free_trial' => (bool) $enrollment->is_free_trial,
                            'image_consent' => (bool) $enrollment->image_consent_accepted,
                            'cxc_balance_due' => $cxcBalanceDue,
                        ];
                    })
                    ->filter(fn ($row) => ! empty($row['student_id']))
                    ->values()
                : collect();

            $presentCount = 0;
            $absentCount = 0;
            $lateCount = 0;
            $pendingCount = 0;

            foreach ($students as $s) {
                $status = $s['check_in'] ?? 'pending';
                if ($status === 'present') {
                    $presentCount++;
                } elseif ($status === 'absent') {
                    $absentCount++;
                } elseif ($status === 'late') {
                    $lateCount++;
                } else {
                    $pendingCount++;
                }
            }

            $coachName = optional($class->coach)->name;
            if (!$coachName && $course && $course->relationLoaded('coaches')) {
                $coachName = $course->coaches->pluck('name')->filter()->implode(', ');
            }
            if (!$coachName) {
                $coachName = 'Sin coach';
            }

            $dateStr = $class->date instanceof \Carbon\Carbon ? $class->date->format('Y-m-d') : (string) $class->date;
            $start = $dateStr . 'T' . $class->start_time;
            $end = $dateStr . 'T' . $class->end_time;

            return [
                'id' => $class->id,
                'title' => optional($course)->title ?? 'Clase',
                'start' => $start,
                'end' => $end,
                'extendedProps' => [
                    'class_id' => $class->id,
                    'course_description' => optional($course)->description,
                    'course_start_date' => optional($course)->start_date,
                    'course_end_date' => optional($course)->end_date,
                    'course_price' => optional(optional($course)->program)->enrollment_fee,
                    'course_monthly_fee' => optional($course)->monthly_fee,
                    'course_capacity' => optional($course)->capacity,
                    'enrolled_children' => $students->count(),
                    'branch' => optional($class->branch)->name ?? 'Sin sede',
                    'coach' => $coachName,
                    'time' => substr((string) $class->start_time, 0, 5) . ' - ' . substr((string) $class->end_time, 0, 5),
                    'enrolled_count' => $students->count(),
                    'students' => $students,
                    'observations' => $class->observations,
                    'attendance_summary' => [
                        'present' => $presentCount,
                        'absent' => $absentCount,
                        'late' => $lateCount,
                        'pending' => $pendingCount,
                        'total' => $students->count(),
                    ],
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
