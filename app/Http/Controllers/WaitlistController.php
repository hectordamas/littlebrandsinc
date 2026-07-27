<?php

namespace App\Http\Controllers;

use App\Models\{Course, Enrollment, Student, User, Waitlist};
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WaitlistController extends Controller
{
    public function index()
    {
        $waitlists = Waitlist::with(['student.user', 'course.program', 'parent'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('waitlists.index', [
            'waitlists' => $waitlists,
        ]);
    }

    public function approve(Waitlist $waitlist): RedirectResponse
    {
        $waitlist->loadMissing(['student', 'course.program']);

        $course = $waitlist->course;
        $student = $waitlist->student;

        if (! $course || ! $student) {
            return redirect()->back()->with('error', 'La entrada de lista de espera no tiene curso o estudiante asociado.');
        }

        $course->loadCount(['enrollments' => function ($q) {
            $q->where('status', '!=', 'cancelled');
        }]);

        if ((int) $course->enrollments_count + 1 > (int) $course->capacity) {
            return redirect()->back()->with('error', 'El curso "' . $course->title . '" no tiene cupos disponibles.');
        }

        if ($student->birthdate) {
            $age = Carbon::parse($student->birthdate)->floatDiffInYears(Carbon::now());

            if ($course->min_age && $age < (float) $course->min_age) {
                return redirect()->back()->with('error', 'El estudiante no cumple con la edad minima del curso "' . $course->title . '".');
            }
            if ($course->max_age && $age > (float) $course->max_age) {
                return redirect()->back()->with('error', 'El estudiante supera la edad maxima permitida para el curso "' . $course->title . '".');
            }
        }

        // Verificar si el estudiante ya está inscrito en el mismo curso en otra inscripción activa
        $alreadyEnrolled = Enrollment::where('student_id', $student->id)
            ->where('status', '!=', 'cancelled')
            ->whereHas('courses', function ($query) use ($course) {
                $query->where('courses.id', $course->id);
            })
            ->exists();

        if ($alreadyEnrolled) {
            return redirect()->back()->with('error', 'El estudiante ya está inscrito en el curso "' . $course->title . '".');
        }

        DB::transaction(function () use ($waitlist, $course, $student): void {
            $waitlist->update(['status' => 'approved']);

            $enrollment = Enrollment::where('student_id', $student->id)
                ->where('program_id', $course->program_id)
                ->where('status', '!=', 'cancelled')
                ->first();

            if ($enrollment) {
                $enrollment->courses()->syncWithoutDetaching([$course->id]);
            } else {
                $enrollment = Enrollment::create([
                    'student_id' => $student->id,
                    'program_id' => $course->program_id,
                    'parent_id' => $waitlist->parent_id,
                    'status' => 'completed',
                    'payment_method' => 'manual',
                    'payment_status' => 'paid',
                    'terms_accepted' => true,
                ]);

                $enrollment->courses()->attach($course->id);
            }
        });

        return redirect()->back()->with('success', 'Entrada de lista de espera aprobada correctamente.');
    }

    public function reject(Waitlist $waitlist): RedirectResponse
    {
        $waitlist->update(['status' => 'rejected']);

        return redirect()->back()->with('success', 'Entrada de lista de espera rechazada.');
    }
}
