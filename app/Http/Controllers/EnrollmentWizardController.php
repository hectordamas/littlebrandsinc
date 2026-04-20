<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class EnrollmentWizardController extends Controller
{
    public function show(Request $request)
    {
        $currentStep = $request->session()->get('enrollment_step', 1);

        $courses = Course::where('active', true)
            ->withCount('enrollments')
            ->get();

        $studentId = $request->session()->get('selected_student_id');
        $studentAge = null;
        $studentBirthdate = null;

        if ($studentId) {
            $student = Student::find($studentId);
            if ($student && $student->birthdate) {
                $studentAge = Carbon::parse($student->birthdate)->age;
                $studentBirthdate = $student->birthdate;
            }
        }

        if ($studentAge !== null) {
            $courses = $courses->map(function ($course) use ($studentAge) {
                $course->can_enroll = true;
                $course->enroll_error = null;

                $spotsLeft = $course->capacity - $course->enrollments_count;
                if ($spotsLeft <= 0) {
                    $course->can_enroll = false;
                    $course->enroll_error = 'Cupo lleno';
                }

                if ($course->min_age && $studentAge < $course->min_age) {
                    $course->can_enroll = false;
                    $course->enroll_error = "Edad mínima requerida: {$course->min_age} años";
                }
                if ($course->max_age && $studentAge > $course->max_age) {
                    $course->can_enroll = false;
                    $course->enroll_error = "Edad máxima permitida: {$course->max_age} años";
                }

                return $course;
            });
        }

        return view('enrollment.wizard', [
            'currentStep' => $currentStep,
            'courses' => $courses,
            'studentBirthdate' => $studentBirthdate,
        ]);
    }

    public function submit(Request $request)
    {
        $step = $request->input('current_step', 1);

        switch ($step) {
            case 1:
                return $this->handleStep1($request);
            case 2:
                return $this->handleStep2($request);
            case 3:
                return $this->handleStep3($request);
            case 4:
                return $this->handleStep4($request);
            default:
                return redirect()->route('enrollment.wizard');
        }
    }

    protected function handleStep1(Request $request)
    {
        $userType = $request->input('user_type');

        if ($userType === 'existing') {
            $validator = Validator::make($request->all(), [
                'email_login' => 'required|email',
                'password_login' => 'required',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator);
            }

            $credentials = [
                'email' => $request->input('email_login'),
                'password' => $request->input('password_login'),
            ];

            if (Auth::attempt($credentials)) {
                $request->session()->put('enrollment_step', 2);
                $request->session()->put('user_type', 'existing');
                $request->session()->put('user_id', Auth::id());
                $request->session()->put('students', Auth::user()->students);

                return redirect()->route('enrollment.wizard');
            }

            return back()->withErrors(['email_login' => 'Credenciales incorrectas']);
        } else {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'whatsapp' => 'required|string',
                'dial_code' => 'required',
                'password' => 'required|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator);
            }

            $user = new User;
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->password = Hash::make($request->input('password'));
            $user->whatsapp = $request->input('dial_code').$request->input('whatsapp');
            $user->role = 'Padre';
            $user->save();

            Auth::login($user);

            $request->session()->put('enrollment_step', 2);
            $request->session()->put('user_type', 'new');
            $request->session()->put('user_id', $user->id);
            $request->session()->put('students', collect());

            return redirect()->route('enrollment.wizard');
        }
    }

    protected function handleStep2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'selected_student' => 'nullable',
            'student_name' => 'nullable',
            'student_birthdate' => 'nullable|date|before_or_equal:today',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $selectedStudentId = $request->input('selected_student');
        $newStudentName = $request->input('student_name');
        $newStudentBirthdate = $request->input('student_birthdate');
        $newStudentMedicalNotes = $request->input('student_medical_notes');

        if ($selectedStudentId) {
            $student = Student::find($selectedStudentId);
            if (! $student || $student->user_id != Auth::id()) {
                return back()->withErrors(['selected_student' => 'Estudiante no válido']);
            }
            $request->session()->put('selected_student_id', $selectedStudentId);
            $request->session()->put('new_student_added', false);
        } elseif ($newStudentName && $newStudentBirthdate) {
            $student = new Student;
            $student->name = $newStudentName;
            $student->birthdate = $newStudentBirthdate;
            $student->medical_notes = $newStudentMedicalNotes;
            $student->user_id = Auth::id();
            $student->save();

            $request->session()->put('selected_student_id', $student->id);
            $request->session()->put('new_student_added', true);
            $request->session()->put('student_name', $newStudentName);
            $request->session()->put('student_birthdate', $newStudentBirthdate);
            $request->session()->put('student_medical_notes', $newStudentMedicalNotes);
        } else {
            return back()->withErrors(['selected_student' => 'Debes seleccionar o agregar un estudiante']);
        }

        $request->session()->put('enrollment_step', 3);

        $studentId = $request->session()->get('selected_student_id');
        $student = Student::find($studentId);
        $studentAge = $student && $student->birthdate ? Carbon::parse($student->birthdate)->age : null;

        $courses = Course::where('active', true)
            ->withCount('enrollments')
            ->get()
            ->map(function ($course) use ($studentAge) {
                $course->can_enroll = true;
                $course->enroll_error = null;

                $spotsLeft = $course->capacity - $course->enrollments_count;
                if ($spotsLeft <= 0) {
                    $course->can_enroll = false;
                    $course->enroll_error = 'Cupo lleno';
                }

                if ($studentAge !== null) {
                    if ($course->min_age && $studentAge < $course->min_age) {
                        $course->can_enroll = false;
                        $course->enroll_error = "Edad mínima requerida: {$course->min_age} años";
                    }
                    if ($course->max_age && $studentAge > $course->max_age) {
                        $course->can_enroll = false;
                        $course->enroll_error = "Edad máxima permitida: {$course->max_age} años";
                    }
                }

                return $course;
            });

        return redirect()->route('enrollment.wizard');
    }

    protected function handleStep3(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'selected_course' => 'required|exists:courses,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $courseId = $request->input('selected_course');
        $course = Course::withCount('enrollments')->findOrFail($courseId);

        $studentId = $request->session()->get('selected_student_id');
        $student = Student::find($studentId);
        $studentAge = $student->birthdate ? Carbon::parse($student->birthdate)->age : null;

        $spotsLeft = $course->capacity - $course->enrollments_count;
        if ($spotsLeft <= 0) {
            return back()->withErrors(['selected_course' => 'Lo sentimos, este programa ya no tiene cupos disponibles']);
        }

        if ($studentAge !== null) {
            if ($course->min_age && $studentAge < $course->min_age) {
                return back()->withErrors(['selected_course' => 'El estudiante no cumple con la edad mínima requerida']);
            }
            if ($course->max_age && $studentAge > $course->max_age) {
                return back()->withErrors(['selected_course' => 'El estudiante excede la edad máxima permitida']);
            }
        }

        $request->session()->put('selected_course_id', $courseId);
        $request->session()->put('course_price', $course->price);
        $request->session()->put('enrollment_step', 4);

        return redirect()->route('enrollment.wizard');
    }

    protected function handleStep4(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:card,pending',
            'terms' => 'accepted',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $studentId = $request->session()->get('selected_student_id');
        $courseId = $request->session()->get('selected_course_id');
        $paymentMethod = $request->input('payment_method');

        if (! $studentId || ! $courseId) {
            return redirect()->route('enrollment.wizard');
        }

        $enrollment = new Enrollment;
        $enrollment->student_id = $studentId;
        $enrollment->course_id = $courseId;
        $enrollment->status = $paymentMethod === 'pending' ? 'pending' : 'completed';
        $enrollment->payment_method = $paymentMethod;
        $enrollment->payment_status = $paymentMethod === 'pending' ? 'pending' : 'paid';
        $enrollment->save();

        $request->session()->forget([
            'enrollment_step',
            'user_type',
            'user_id',
            'students',
            'selected_student_id',
            'new_student_added',
            'student_name',
            'student_birthdate',
            'student_medical_notes',
            'selected_course_id',
            'course_price',
        ]);

        return redirect()->route('home')->with('success', '¡Inscripción completada exitosamente!');
    }

    public function reset(Request $request)
    {
        $request->session()->flush();

        return redirect()->route('enrollment.wizard');
    }
}
