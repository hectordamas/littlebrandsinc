<?php

namespace App\Http\Controllers;

use App\Models\AccountReceivable;
use App\Models\EnrollmentInstallment;
use App\Models\ParentPayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ParentPortalController extends Controller
{
    public function index()
    {
        $user = User::query()->findOrFail((int) Auth::id());

        $students = $user->students()
            ->with([
                'enrollments.courses.branch',
                'enrollments.courses.classes.coach',
                'attendances.class.course',
            ])
            ->orderBy('name')
            ->get();

        $receivables = AccountReceivable::query()
            ->with(['enrollment.student', 'enrollment.program', 'enrollment.courses'])
            ->whereHas('enrollment', function ($query) use ($user) {
                $query->where('parent_id', $user->id);
            })
            ->orderByDesc('id')
            ->get();

        $installments = EnrollmentInstallment::query()
            ->with(['enrollment.student', 'enrollment.program', 'enrollment.courses'])
            ->whereHas('enrollment', function ($query) use ($user) {
                $query->where('parent_id', $user->id);
            })
            ->orderBy('due_date')
            ->get();

        $upcomingClasses = $students
            ->flatMap(function ($student) {
                return $student->enrollments->flatMap(function ($enrollment) {
                    return $enrollment->courses->flatMap(function ($course) {
                        return $course->classes ?? collect();
                    });
                })->map(function ($class) use ($student) {
                    return [
                        'student_name' => $student->name,
                        'class' => $class,
                    ];
                });
            })
            ->filter(function ($row) {
                $classDate = optional($row['class'])->date;

                return $classDate && Carbon::parse($classDate)->greaterThanOrEqualTo(now()->startOfDay());
            })
            ->sortBy(function ($row) {
                $class = $row['class'];

                return sprintf('%s %s', $class->date, $class->start_time);
            })
            ->take(3)
            ->values();

        $parentPayments = ParentPayment::query()
            ->with('receivable')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->take(3)
            ->get();

        return view('portal.parent', [
            'students' => $students,
            'upcomingClasses' => $upcomingClasses,
            'parentPayments' => $parentPayments,
            'pendingBalance' => (float) $receivables->whereIn('status', ['pending', 'partial'])->sum('balance_due'),
            'pendingInstallments' => (int) $installments->whereIn('status', ['pending', 'overdue', 'failed'])->count(),
        ]);
    }

    public function calendar()
    {
        $user = User::query()->findOrFail((int) Auth::id());
        $students = $user->students()->orderBy('name')->get();

        return view('portal.calendar', [
            'students' => $students,
        ]);
    }

    public function calendarEvents(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'start' => 'nullable|date',
            'end' => 'nullable|date',
            'student_id' => 'nullable|integer|exists:students,id',
        ]);

        $user = User::query()->findOrFail((int) Auth::id());

        $studentQuery = $user->students();
        if (!empty($validated['student_id'])) {
            $studentQuery->where('id', $validated['student_id']);
        }
        $students = $studentQuery->get();
        $studentIds = $students->pluck('id')->toArray();

        $enrollments = \App\Models\Enrollment::query()
            ->whereIn('student_id', $studentIds)
            ->where('status', '!=', 'cancelled')
            ->with(['student', 'courses.classes' => function ($query) use ($validated) {
                $query->with(['branch', 'coach', 'attendances'])
                    ->when(isset($validated['start']), function ($q) use ($validated) {
                        $q->whereDate('date', '>=', $validated['start']);
                    })
                    ->when(isset($validated['end']), function ($q) use ($validated) {
                        $q->whereDate('date', '<=', $validated['end']);
                    });
            }])
            ->get();

        $events = [];
        foreach ($enrollments as $enrollment) {
            $student = $enrollment->student;
            foreach ($enrollment->courses as $course) {
                foreach ($course->classes as $class) {
                    $attendance = $class->attendances->firstWhere('student_id', $student->id);
                    $attendanceStatus = $attendance ? $attendance->status : 'pending';
                    $attendanceNotes = $attendance ? $attendance->notes : null;

                    $start = $class->date->format('Y-m-d') . 'T' . $class->start_time;
                    $end = $class->date->format('Y-m-d') . 'T' . $class->end_time;

                    $color = '#2563eb';
                    if ($attendanceStatus === 'present') {
                        $color = '#10b981';
                    } elseif ($attendanceStatus === 'late') {
                        $color = '#f59e0b';
                    } elseif ($attendanceStatus === 'absent') {
                        $color = '#ef4444';
                    }

                    $events[] = [
                        'id' => $class->id . '-' . $student->id,
                        'title' => $student->name . ' - ' . $course->title,
                        'start' => $start,
                        'end' => $end,
                        'backgroundColor' => $color,
                        'borderColor' => $color,
                        'extendedProps' => [
                            'class_id' => $class->id,
                            'student_id' => $student->id,
                            'student_name' => $student->name,
                            'course_title' => $course->title,
                            'branch' => optional($class->branch)->name ?? 'Sin sede',
                            'coach' => optional($class->coach)->name ?? 'Sin asignar',
                            'time' => substr((string) $class->start_time, 0, 5) . ' - ' . substr((string) $class->end_time, 0, 5),
                            'attendance' => $attendanceStatus,
                            'notes' => $attendanceNotes,
                        ],
                    ];
                }
            }
        }

        return response()->json($events);
    }

    public function payments()
    {
        $user = User::query()->findOrFail((int) Auth::id());

        $receivables = AccountReceivable::query()
            ->with(['enrollment.student', 'enrollment.program', 'enrollment.courses'])
            ->whereHas('enrollment', function ($query) use ($user) {
                $query->where('parent_id', $user->id);
            })
            ->orderByDesc('id')
            ->get();

        $installments = EnrollmentInstallment::query()
            ->with(['enrollment.student', 'enrollment.program', 'enrollment.courses'])
            ->whereHas('enrollment', function ($query) use ($user) {
                $query->where('parent_id', $user->id);
            })
            ->orderBy('due_date')
            ->get();

        $parentPayments = ParentPayment::query()
            ->with('receivable')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return view('portal.payments', [
            'receivables' => $receivables,
            'installments' => $installments,
            'parentPayments' => $parentPayments,
            'pendingBalance' => (float) $receivables->whereIn('status', ['pending', 'partial'])->sum('balance_due'),
            'pendingInstallments' => (int) $installments->whereIn('status', ['pending', 'overdue', 'failed'])->count(),
        ]);
    }

    public function registerPayment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'account_receivable_id' => 'required|exists:account_receivables,id',
            'amount' => 'required|numeric|min:0.01',
            'reference' => 'nullable|string|max:255',
            'payment_receipt' => 'bail|required|file|mimes:jpg,jpeg,png,pdf|max:6144',
        ]);

        $receivable = AccountReceivable::query()
            ->whereHas('enrollment', function ($query) {
                $query->where('parent_id', Auth::id());
            })
            ->findOrFail($validated['account_receivable_id']);

        $path = $request->file('payment_receipt')->store('comprobantes', 'public');

        ParentPayment::query()->create([
            'account_receivable_id' => $receivable->id,
            'amount' => $validated['amount'],
            'reference' => $validated['reference'] ?? null,
            'receipt_path' => $path,
            'status' => 'pending',
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('parent.payments')->with('success', 'Pago registrado exitosamente. Será revisado por el administrador.');
    }
}
