<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\{Student, User, Enrollment};
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;


class StudentsController extends Controller
{
    public function index()
    {
        $students = Student::with([
            'user',
            'enrollments.course',
        ])->orderBy('id', 'desc')->get();

        return view('students.index', [
            'students' => $students
        ]);
    }

    public function show(Student $student)
    {
        $student->load([
            'user',
            'enrollments.course.branch',
            'enrollments.course.classes' => function ($query) {
                $query->with('coach')->orderBy('date')->orderBy('start_time');
            },
        ]);

        $upcomingClasses = $student->enrollments
            ->flatMap(function ($enrollment) {
                return optional($enrollment->course)->classes ?? collect();
            })
            ->filter(function ($class) {
                return $class->date && Carbon::parse($class->date)->greaterThanOrEqualTo(now()->startOfDay());
            })
            ->sortBy(function ($class) {
                return sprintf('%s %s', $class->date, $class->start_time);
            })
            ->values();

        return view('students.show', [
            'student' => $student,
            'upcomingClasses' => $upcomingClasses,
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'whatsapp' => 'required',
            'password' => 'required|confirmed|min:6',
            'student_name' => 'required',
            'birthdate' => 'required|date',
            'terms' => 'accepted'

        ]);


        if ($request->user_type === 'existing') {

            $user = User::where('email', $request->email_login)->first();

            if (!$user || !Hash::check($request->password_login, $user->password)) {
                return back()->withErrors(['email_login' => 'Credenciales incorrectas']);
            }
        } else {

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'whatsapp' => $request->whatsapp,
                'password' => Hash::make($request->password),
            ]);
        }

        // ✅ login automático
        Auth::login($user);
        // 4. Crear estudiante
        $student = Student::create([
            'user_id' => $user->id,
            'name' => $request->student_name,
            'birthdate' => $request->birthdate,
            'medical_notes' => $request->medical_notes,
        ]);

        // 5. Crear inscripción (ajústalo a tu modelo)
        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $request->course_id, // si lo envías oculto
            'status' => 'active',
        ]);

        return redirect()->route('home')->with('success', 'Inscripción completada');
    }
}
