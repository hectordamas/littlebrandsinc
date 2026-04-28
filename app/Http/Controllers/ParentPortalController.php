<?php

namespace App\Http\Controllers;

use App\Models\AccountReceivable;
use App\Models\EnrollmentInstallment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ParentPortalController extends Controller
{
    public function index()
    {
        $user = User::query()->findOrFail((int) Auth::id());

        $students = $user->students()
            ->with([
                'enrollments.course.branch',
                'enrollments.course.classes.coach',
                'attendances.class.course',
            ])
            ->orderBy('name')
            ->get();

        $receivables = AccountReceivable::query()
            ->with(['enrollment.student', 'enrollment.course'])
            ->whereHas('enrollment', function ($query) use ($user) {
                $query->where('parent_id', $user->id);
            })
            ->orderByDesc('id')
            ->get();

        $installments = EnrollmentInstallment::query()
            ->with(['enrollment.student', 'enrollment.course'])
            ->whereHas('enrollment', function ($query) use ($user) {
                $query->where('parent_id', $user->id);
            })
            ->orderBy('due_date')
            ->get();

        $upcomingClasses = $students
            ->flatMap(function ($student) {
                return $student->enrollments->flatMap(function ($enrollment) {
                    return optional($enrollment->course)->classes ?? collect();
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
            ->values();

        $attendanceRows = $students
            ->flatMap(function ($student) {
                return $student->attendances->map(function ($attendance) use ($student) {
                    return [
                        'student_name' => $student->name,
                        'attendance' => $attendance,
                    ];
                });
            })
            ->sortByDesc(function ($row) {
                return optional($row['attendance'])->date;
            })
            ->values();

        return view('portal.parent', [
            'students' => $students,
            'receivables' => $receivables,
            'installments' => $installments,
            'upcomingClasses' => $upcomingClasses,
            'attendanceRows' => $attendanceRows,
            'pendingBalance' => (float) $receivables->whereIn('status', ['pending', 'partial'])->sum('balance_due'),
            'pendingInstallments' => (int) $installments->whereIn('status', ['pending', 'overdue', 'failed'])->count(),
        ]);
    }
}
