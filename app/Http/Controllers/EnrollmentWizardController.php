<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
class EnrollmentWizardController extends Controller
{
    public function show(Request $request)
    {
        $forcedCourseId = $request->query('course_id') ?? $request->query('program_id');

        if ($forcedCourseId) {
            $exists = Course::where('active', true)->whereKey($forcedCourseId)->exists();
            if ($exists) {
                $request->session()->put('wizard_locked_course_id', (int) $forcedCourseId);
            }
        }

        $lockedCourseId = $request->session()->get('wizard_locked_course_id');

        if (Auth::check()) {
            $step = (int) $request->session()->get('enrollment_step', 2);
            if ($step < 2) {
                $request->session()->put('enrollment_step', 2);
                $step = 2;
            }
            $students = Auth::user()->students()->orderBy('name')->get();
            $request->session()->put('students', $students);
        } else {
            $step = (int) $request->session()->get('enrollment_step', 1);
        }

        $studentId = $request->session()->get('selected_student_id');
        $studentBirthdate = null;
        if ($studentId) {
            $student = Student::find($studentId);
            $studentBirthdate = $student?->birthdate;
        }

        $studentAge = $studentBirthdate ? Carbon::parse($studentBirthdate)->age : null;

        $courses = $this->loadCoursesForWizard($lockedCourseId, $studentAge);

        return view('enrollment.wizard', [
            'initialStep' => $step,
            'courses' => $courses,
            'studentBirthdate' => $studentBirthdate,
            'lockedCourseId' => $lockedCourseId,
            'stripeKey' => config('services.stripe.key'),
            'wizardPayload' => $this->wizardPayload($request),
        ]);
    }

    public function submit(Request $request)
    {
        $step = (int) $request->input('current_step', 1);

        return match ($step) {
            1 => $this->handleStep1($request),
            2 => $this->handleStep2($request),
            3 => $this->handleStep3($request),
            4 => $this->handleStep4($request),
            5 => $this->handleStep5($request),
            default => $this->wizardRedirect($request, redirect()->route('enrollment.wizard')),
        };
    }

    public function createPaymentIntent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|integer|exists:courses,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos para procesar el pago.',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $course = Course::query()
            ->where('active', true)
            ->whereDate('end_date', '>=', now()->toDateString())
            ->findOrFail((int) $request->input('course_id'));

        $amount = (int) round(((float) ($course->price ?? 0)) * 100);
        if ($amount <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Este curso no tiene un monto válido para cobrar.',
            ], 422);
        }

        $stripeSecret = config('services.stripe.secret');
        if (! $stripeSecret) {
            return response()->json([
                'success' => false,
                'message' => 'Stripe no está configurado en el servidor.',
            ], 500);
        }

        try {
            $stripe = new StripeClient($stripeSecret);
            $intent = $stripe->paymentIntents->create([
                'amount' => $amount,
                'currency' => 'usd',
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => [
                    'course_id' => (string) $course->id,
                    'course_title' => $course->title,
                    'student_id' => (string) ($request->session()->get('selected_student_id') ?? ''),
                ],
            ]);

            return response()->json([
                'success' => true,
                'client_secret' => $intent->client_secret,
                'payment_intent_id' => $intent->id,
            ]);
        } catch (ApiErrorException $e) {
            return response()->json([
                'success' => false,
                'message' => 'No fue posible crear el intento de pago en Stripe.',
                'error' => $e->getMessage(),
            ], 502);
        }
    }

    protected function handleStep1(Request $request): RedirectResponse|JsonResponse
    {
        if (Auth::check()) {
            $request->session()->put('enrollment_step', 2);

            return $this->wizardJsonOrRedirect($request, [
                'success' => true,
                'next_step' => 2,
                'data' => $this->wizardPayload($request),
            ], redirect()->route('enrollment.wizard'));
        }

        $userType = $request->input('user_type');

        if ($userType === 'existing') {
            $validator = Validator::make($request->all(), [
                'email_login' => 'required|email',
                'password_login' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->wizardValidationError($request, $validator);
            }

            $credentials = [
                'email' => $request->input('email_login'),
                'password' => $request->input('password_login'),
            ];

            if (! Auth::attempt($credentials)) {
                $msg = 'Credenciales incorrectas';

                return $this->wizardJsonOrRedirect($request, [
                    'success' => false,
                    'message' => $msg,
                    'errors' => ['email_login' => [$msg]],
                ], back()->withErrors(['email_login' => $msg]));
            }

            $request->session()->regenerate();
            $request->session()->put('enrollment_step', 2);
            $request->session()->put('user_type', 'existing');
            $request->session()->put('students', Auth::user()->students()->orderBy('name')->get());

            return $this->wizardJsonOrRedirect($request, [
                'success' => true,
                'next_step' => 2,
                'data' => $this->wizardPayload($request),
            ], redirect()->route('enrollment.wizard'));
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'whatsapp' => 'required|string',
            'dial_code' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->wizardValidationError($request, $validator);
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
        $request->session()->put('students', collect());

        return $this->wizardJsonOrRedirect($request, [
            'success' => true,
            'next_step' => 2,
            'data' => $this->wizardPayload($request),
        ], redirect()->route('enrollment.wizard'));
    }

    protected function handleStep2(Request $request): RedirectResponse|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'selected_student' => 'nullable',
            'student_name' => 'nullable|string|max:255',
            'student_birthdate' => 'nullable|date|before_or_equal:today',
            'student_medical_notes' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return $this->wizardValidationError($request, $validator);
        }

        $selectedStudentId = $request->input('selected_student');
        $newStudentName = $request->input('student_name');
        $newStudentBirthdate = $request->input('student_birthdate');
        $newStudentMedicalNotes = $request->input('student_medical_notes');

        if ($selectedStudentId) {
            $student = Student::find($selectedStudentId);
            if (! $student || $student->user_id != Auth::id()) {
                $msg = 'Estudiante no válido';

                return $this->wizardJsonOrRedirect($request, [
                    'success' => false,
                    'message' => $msg,
                    'errors' => ['selected_student' => [$msg]],
                ], back()->withErrors(['selected_student' => $msg]));
            }
            $request->session()->put('selected_student_id', (int) $selectedStudentId);
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
            $request->session()->put('students', Auth::user()->students()->orderBy('name')->get());
        } else {
            $msg = 'Debes seleccionar o agregar un estudiante';

            return $this->wizardJsonOrRedirect($request, [
                'success' => false,
                'message' => $msg,
                'errors' => ['selected_student' => [$msg]],
            ], back()->withErrors(['selected_student' => $msg]));
        }

        $request->session()->put('enrollment_step', 3);

        return $this->wizardJsonOrRedirect($request, [
            'success' => true,
            'next_step' => 3,
            'data' => $this->wizardPayload($request),
        ], redirect()->route('enrollment.wizard'));
    }

    protected function handleStep3(Request $request): RedirectResponse|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'selected_course' => 'required|exists:courses,id',
        ]);

        if ($validator->fails()) {
            return $this->wizardValidationError($request, $validator);
        }

        $courseId = (int) $request->input('selected_course');
        $locked = $request->session()->get('wizard_locked_course_id');
        if ($locked && (int) $courseId !== (int) $locked) {
            $msg = 'Este curso no está permitido para esta inscripción';

            return $this->wizardJsonOrRedirect($request, [
                'success' => false,
                'message' => $msg,
                'errors' => ['selected_course' => [$msg]],
            ], back()->withErrors(['selected_course' => $msg]));
        }

        $course = Course::withCount('enrollments')->findOrFail($courseId);

        if (! $course->active || Carbon::parse($course->end_date)->lt(Carbon::today())) {
            $msg = 'Este programa no está disponible o ya finalizó';

            return $this->wizardJsonOrRedirect($request, [
                'success' => false,
                'message' => $msg,
                'errors' => ['selected_course' => [$msg]],
            ], back()->withErrors(['selected_course' => $msg]));
        }

        $studentId = $request->session()->get('selected_student_id');
        $student = Student::find($studentId);
        $alreadyEnrolled = Enrollment::query()
            ->where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->exists();
        if ($alreadyEnrolled) {
            $msg = 'Este estudiante ya está inscrito en este programa';

            return $this->wizardJsonOrRedirect($request, [
                'success' => false,
                'message' => $msg,
                'errors' => ['selected_course' => [$msg]],
            ], back()->withErrors(['selected_course' => $msg]));
        }

        if (! $student) {
            return $this->wizardJsonOrRedirect($request, [
                'success' => false,
                'message' => 'Sesión de estudiante inválida',
                'errors' => ['selected_student' => ['Vuelve a seleccionar el estudiante.']],
            ], redirect()->route('enrollment.wizard'));
        }

        $studentAge = $student->birthdate ? Carbon::parse($student->birthdate)->age : null;

        $spotsLeft = $course->capacity - $course->enrollments_count;
        if ($spotsLeft <= 0) {
            $msg = 'Lo sentimos, este programa ya no tiene cupos disponibles';

            return $this->wizardJsonOrRedirect($request, [
                'success' => false,
                'message' => $msg,
                'errors' => ['selected_course' => [$msg]],
            ], back()->withErrors(['selected_course' => $msg]));
        }

        if ($studentAge !== null) {
            if ($course->min_age && $studentAge < $course->min_age) {
                $msg = 'El estudiante no cumple con la edad mínima requerida';

                return $this->wizardJsonOrRedirect($request, [
                    'success' => false,
                    'message' => $msg,
                    'errors' => ['selected_course' => [$msg]],
                ], back()->withErrors(['selected_course' => $msg]));
            }
            if ($course->max_age && $studentAge > $course->max_age) {
                $msg = 'El estudiante excede la edad máxima permitida';

                return $this->wizardJsonOrRedirect($request, [
                    'success' => false,
                    'message' => $msg,
                    'errors' => ['selected_course' => [$msg]],
                ], back()->withErrors(['selected_course' => $msg]));
            }
        }

        $request->session()->put('selected_course_id', $courseId);
        $request->session()->put('course_price', $course->price);
        $request->session()->put('enrollment_step', 4);

        return $this->wizardJsonOrRedirect($request, [
            'success' => true,
            'next_step' => 4,
            'data' => $this->wizardPayload($request),
        ], redirect()->route('enrollment.wizard'));
    }

    protected function handleStep4(Request $request): RedirectResponse|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:card,pending',
            'stripe_payment_intent_id' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->wizardValidationError($request, $validator);
        }

        $paymentMethod = $request->input('payment_method');

        if ($paymentMethod === 'card' && ! $request->filled('stripe_payment_intent_id')) {
            $validator = Validator::make([], []);
            $validator->errors()->add('stripe_payment_intent_id', 'Completa el pago con tarjeta para continuar.');

            return $this->wizardValidationError($request, $validator);
        }

        $request->session()->put('payment_method', $paymentMethod);
        if ($paymentMethod === 'card') {
            $request->session()->put('stripe_payment_intent_id', $request->input('stripe_payment_intent_id'));
        } else {
            $request->session()->forget('stripe_payment_intent_id');
        }

        $request->session()->put('enrollment_step', 5);

        return $this->wizardJsonOrRedirect($request, [
            'success' => true,
            'next_step' => 5,
            'data' => $this->wizardPayload($request),
        ], redirect()->route('enrollment.wizard'));
    }

    protected function handleStep5(Request $request): RedirectResponse|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'terms' => 'accepted',
        ]);

        if ($validator->fails()) {
            return $this->wizardValidationError($request, $validator);
        }

        $studentId = $request->session()->get('selected_student_id');
        $courseId = $request->session()->get('selected_course_id');
        $paymentMethod = $request->session()->get('payment_method');
        $stripePaymentIntentId = $request->session()->get('stripe_payment_intent_id');

        if (! $studentId || ! $courseId || ! $paymentMethod) {
            return $this->wizardJsonOrRedirect($request, [
                'success' => false,
                'message' => 'Sesión incompleta. Reinicia el proceso.',
                'errors' => ['session' => ['Sesión incompleta.']],
            ], redirect()->route('enrollment.wizard')->withErrors(['session' => 'Sesión incompleta.']));
        }

        $student = Student::find($studentId);
        $course = Course::withCount('enrollments')->find($courseId);
        if (! $student || ! $course) {
            return $this->wizardJsonOrRedirect($request, [
                'success' => false,
                'message' => 'Sesión inválida. Reinicia el proceso.',
                'errors' => ['session' => ['Sesión inválida.']],
            ], redirect()->route('enrollment.wizard')->withErrors(['session' => 'Sesión inválida.']));
        }

        if (! $course->active || Carbon::parse($course->end_date)->lt(Carbon::today())) {
            $msg = 'Este programa ya no está vigente.';

            return $this->wizardJsonOrRedirect($request, [
                'success' => false,
                'message' => $msg,
                'errors' => ['selected_course' => [$msg]],
            ], redirect()->route('enrollment.wizard')->withErrors(['selected_course' => $msg]));
        }

        $alreadyEnrolled = Enrollment::query()
            ->where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->exists();
        if ($alreadyEnrolled) {
            $msg = 'Este estudiante ya está inscrito en este programa';

            return $this->wizardJsonOrRedirect($request, [
                'success' => false,
                'message' => $msg,
                'errors' => ['selected_course' => [$msg]],
            ], redirect()->route('enrollment.wizard')->withErrors(['selected_course' => $msg]));
        }

        $spotsLeft = $course->capacity - $course->enrollments_count;
        if ($spotsLeft <= 0) {
            $msg = 'Lo sentimos, este programa ya no tiene cupos disponibles';

            return $this->wizardJsonOrRedirect($request, [
                'success' => false,
                'message' => $msg,
                'errors' => ['selected_course' => [$msg]],
            ], redirect()->route('enrollment.wizard')->withErrors(['selected_course' => $msg]));
        }

        if ($paymentMethod === 'card' && $stripePaymentIntentId && str_starts_with($stripePaymentIntentId, 'pi_')) {
            $stripeSecret = config('services.stripe.secret');
            if (! $stripeSecret) {
                return $this->wizardJsonOrRedirect($request, [
                    'success' => false,
                    'message' => 'Stripe no está configurado en el servidor.',
                    'errors' => ['payment_method' => ['No se pudo validar el pago.']],
                ], redirect()->route('enrollment.wizard')->withErrors(['payment_method' => 'No se pudo validar el pago.']));
            }

            try {
                $stripe = new StripeClient($stripeSecret);
                $intent = $stripe->paymentIntents->retrieve($stripePaymentIntentId, []);
                if (! in_array($intent->status, ['succeeded', 'processing'], true)) {
                    return $this->wizardJsonOrRedirect($request, [
                        'success' => false,
                        'message' => 'El pago con tarjeta no fue aprobado.',
                        'errors' => ['payment_method' => ['No se pudo confirmar el pago en Stripe.']],
                    ], redirect()->route('enrollment.wizard')->withErrors(['payment_method' => 'No se pudo confirmar el pago en Stripe.']));
                }
            } catch (ApiErrorException $e) {
                return $this->wizardJsonOrRedirect($request, [
                    'success' => false,
                    'message' => 'No se pudo validar el pago en Stripe.',
                    'errors' => ['payment_method' => [$e->getMessage()]],
                ], redirect()->route('enrollment.wizard')->withErrors(['payment_method' => 'No se pudo validar el pago en Stripe.']));
            }
        }

        $enrollment = new Enrollment;
        $enrollment->student_id = $studentId;
        $enrollment->course_id = $courseId;
        $enrollment->parent_id = Auth::id();
        $enrollment->status = $paymentMethod === 'pending' ? 'pending' : 'completed';
        $enrollment->payment_method = $paymentMethod;
        $enrollment->payment_status = $paymentMethod === 'pending' ? 'pending' : 'paid';
        $enrollment->save();

        if ($enrollment->payment_status === 'paid') {
            Transaction::create([
                'enrollment_id' => $enrollment->id,
                'student_id' => $student->id,
                'course_id' => $course->id,
                'branch_id' => $course->branch_id,
                'account_id' => $this->resolveIncomeAccountId($paymentMethod),
                'amount' => $course->price ?? 0,
                'currency' => 'USD',
                'type' => 'income',
                'status' => 'completed',
                'payment_method' => $paymentMethod === 'card' ? 'stripe' : $paymentMethod,
                'reference' => $stripePaymentIntentId,
                'description' => 'Pago de inscripción: '.$course->title,
            ]);
        }

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
            'payment_method',
            'stripe_payment_intent_id',
            'wizard_locked_course_id',
        ]);

        $home = redirect()->route('home')->with('success', '¡Inscripción completada exitosamente!');

        return $this->wizardJsonOrRedirect($request, [
            'success' => true,
            'redirect_url' => route('home'),
            'message' => '¡Inscripción completada exitosamente!',
        ], $home);
    }

    public function reset(Request $request)
    {
        $keys = [
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
            'payment_method',
            'stripe_payment_intent_id',
            'wizard_locked_course_id',
        ];
        foreach ($keys as $key) {
            $request->session()->forget($key);
        }

        return redirect()->route('enrollment.wizard');
    }

    protected function loadCoursesForWizard(?int $lockedCourseId, ?int $studentAge)
    {
        $q = Course::query()
            ->where('active', true)
            ->whereDate('end_date', '>=', now()->toDateString())
            ->withCount('enrollments');

        if ($lockedCourseId) {
            $q->whereKey($lockedCourseId);
        }

        return $q->orderBy('title')->get()->map(function ($course) use ($studentAge) {
            $course->can_enroll = true;
            $course->enroll_error = null;

            $spotsLeft = $course->capacity - $course->enrollments_count;
            $course->spots_left = max(0, $spotsLeft);

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
    }

    protected function wizardPayload(Request $request): array
    {
        $lockedCourseId = $request->session()->get('wizard_locked_course_id');
        $studentId = $request->session()->get('selected_student_id');
        $studentBirthdate = null;
        $studentName = null;
        if ($studentId) {
            $s = Student::find($studentId);
            if ($s) {
                $studentBirthdate = $s->birthdate ? Carbon::parse($s->birthdate)->format('Y-m-d') : null;
                $studentName = $s->name;
            }
        }

        $studentAge = $studentBirthdate ? Carbon::parse($studentBirthdate)->age : null;
        $courses = $this->loadCoursesForWizard($lockedCourseId, $studentAge);

        $students = Auth::check()
            ? Auth::user()->students()->orderBy('name')->get()->map(fn ($st) => [
                'id' => $st->id,
                'name' => $st->name,
                'birthdate' => $st->birthdate ? Carbon::parse($st->birthdate)->format('Y-m-d') : null,
            ])->values()->all()
            : [];

        $courseId = $request->session()->get('selected_course_id');
        $courseModel = $courseId ? Course::withCount('enrollments')->find($courseId) : null;

        return [
            'authenticated' => Auth::check(),
            'enrollment_step' => (int) $request->session()->get('enrollment_step', 1),
            'students' => $students,
            'selected_student_id' => $studentId ? (int) $studentId : null,
            'selected_student_name' => $studentName,
            'courses' => $courses->map(fn ($c) => $this->serializeCourse($c))->values()->all(),
            'selected_course_id' => $courseId ? (int) $courseId : null,
            'selected_course' => $courseModel ? $this->serializeCourse($courseModel) : null,
            'course_price' => $request->session()->get('course_price'),
            'locked_course_id' => $lockedCourseId ? (int) $lockedCourseId : null,
            'payment_method' => $request->session()->get('payment_method'),
        ];
    }

    protected function serializeCourse(Course $course): array
    {
        $spotsLeft = isset($course->spots_left)
            ? (int) $course->spots_left
            : max(0, $course->capacity - ($course->enrollments_count ?? 0));

        return [
            'id' => $course->id,
            'title' => $course->title,
            'description' => $course->description,
            'min_age' => $course->min_age,
            'max_age' => $course->max_age,
            'capacity' => $course->capacity,
            'enrollments_count' => $course->enrollments_count ?? 0,
            'spots_left' => $spotsLeft,
            'price' => $course->price !== null ? (float) $course->price : null,
            'can_enroll' => (bool) ($course->can_enroll ?? true),
            'enroll_error' => $course->enroll_error,
        ];
    }

    protected function wantsWizardJson(Request $request): bool
    {
        return $request->expectsJson()
            || $request->ajax()
            || $request->boolean('wizard_json')
            || $request->header('X-Wizard-Json') === '1';
    }

    protected function wizardValidationError(Request $request, $validator): JsonResponse|RedirectResponse
    {
        if ($this->wantsWizardJson($request)) {
            return response()->json([
                'success' => false,
                'message' => __('Revisa los campos marcados.'),
                'errors' => $validator->errors()->toArray(),
                'csrf_token' => csrf_token(),
            ], 422);
        }

        return back()->withErrors($validator)->withInput();
    }

    protected function wizardJsonOrRedirect(Request $request, array $json, RedirectResponse $redirect): JsonResponse|RedirectResponse
    {
        if ($this->wantsWizardJson($request)) {
            $json['csrf_token'] = csrf_token();

            return response()->json($json);
        }

        return $redirect;
    }

    protected function wizardRedirect(Request $request, RedirectResponse $redirect): RedirectResponse|JsonResponse
    {
        if ($this->wantsWizardJson($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Paso no válido',
                'csrf_token' => csrf_token(),
            ], 400);
        }

        return $redirect;
    }

    protected function resolveIncomeAccountId(string $paymentMethod): int
    {
        if ($paymentMethod === 'card') {
            $account = Account::firstOrCreate(
                ['slug' => 'stripe'],
                [
                    'name' => 'Stripe',
                    'type' => 'stripe',
                    'currency' => 'USD',
                    'active' => true,
                    'meta' => ['provider' => 'stripe'],
                ]
            );

            return (int) $account->id;
        }

        $account = Account::firstOrCreate(
            ['slug' => 'cash'],
            [
                'name' => 'Caja / Efectivo',
                'type' => 'cash',
                'currency' => 'USD',
                'active' => true,
            ]
        );

        return (int) $account->id;
    }
}
