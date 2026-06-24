<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountReceivable;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\EnrollmentBillingProfile;
use App\Models\EnrollmentInstallment;
use App\Models\Program;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Waitlist;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class EnrollmentWizardController extends Controller
{
    public function show(Request $request)
    {
        $forcedCourseId = $request->query('course_id');
        $forcedProgramId = $request->query('program_id');

        if ($forcedCourseId) {
            $forcedCourse = Course::query()
                ->where('active', true)
                ->whereDate('end_date', '>=', now()->toDateString())
                ->whereKey($forcedCourseId)
                ->first();

            if ($forcedCourse) {
                $request->session()->put('wizard_locked_course_id', (int) $forcedCourse->id);
                $request->session()->put('program_id', (int) $forcedCourse->program_id);
                $request->session()->forget('selected_course_ids');
            }
        }

        if ($forcedProgramId) {
            $programExists = Program::query()
                ->where('active', true)
                ->whereKey($forcedProgramId)
                ->exists();

            if ($programExists) {
                $request->session()->put('program_id', (int) $forcedProgramId);
                $request->session()->forget('wizard_locked_course_id');
                $request->session()->forget('selected_course_ids');
            }
        }

        $lockedCourseId = $request->session()->get('wizard_locked_course_id');
        $programId = $request->session()->get('program_id');

        if (Auth::check()) {
            $step = (int) $request->session()->get('enrollment_step', 2);
            if ($step < 2) {
                $request->session()->put('enrollment_step', 2);
                $step = 2;
            }
            $authUser = Auth::user();
            if ($authUser instanceof User) {
                $students = $authUser->students()->orderBy('name')->get();
                $request->session()->put('students', $students);
            }
        } else {
            $step = (int) $request->session()->get('enrollment_step', 1);
        }

        $studentId = $request->session()->get('selected_student_id');
        $studentBirthdate = null;
        if ($studentId) {
            $student = Student::find($studentId);
            $studentBirthdate = $student?->birthdate;
        }

        $studentAge = $studentBirthdate ? round(Carbon::parse($studentBirthdate)->floatDiffInYears(), 1) : null;

        $programs = Program::where('active', true)->orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();
        $courses = $this->loadCoursesForWizard($lockedCourseId, $studentAge, $programId ? (int) $programId : null);

        return view('enrollment.wizard', [
            'initialStep' => $step,
            'programs' => $programs,
            'branches' => $branches,
            'courses' => $courses,
            'studentBirthdate' => $studentBirthdate,
            'lockedCourseId' => $lockedCourseId,
            'stripeKey' => config('services.stripe.key'),
            'stripeEnabled' => config('services.stripe.enabled'),
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
            'course_ids' => 'required|array|min:1',
            'course_ids.*' => 'integer|exists:courses,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos para procesar el pago.',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $courseIds = array_map('intval', $request->input('course_ids', []));
        $courses = Course::query()
            ->where('active', true)
            ->whereDate('end_date', '>=', now()->toDateString())
            ->whereIn('id', $courseIds)
            ->get();

        if ($courses->count() !== count($courseIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Uno o más cursos no están disponibles.',
            ], 422);
        }

        $programIds = $courses->pluck('program_id')->unique();
        if ($programIds->count() !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'Todos los cursos deben pertenecer al mismo programa.',
            ], 422);
        }

        $program = Program::find($programIds->first());
        if (! $program) {
            return response()->json([
                'success' => false,
                'message' => 'Programa no encontrado.',
            ], 422);
        }

        $initialAmount = $this->calculateInitialChargeAmount($program, $courses->all());
        $amount = (int) round($initialAmount * 100);
        if ($amount <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Estos cursos no tienen un monto válido para cobrar.',
            ], 422);
        }

        if (! config('services.stripe.enabled')) {
            return response()->json([
                'success' => false,
                'message' => 'Stripe está deshabilitado en la configuración.',
            ], 503);
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
            $courseTitles = $courses->pluck('title')->implode(', ');
            $intent = $stripe->paymentIntents->create([
                'amount' => $amount,
                'currency' => 'usd',
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => [
                    'course_ids' => implode(',', $courseIds),
                    'course_title' => $courseTitles,
                    'program_id' => (string) $program->id,
                    'student_id' => (string) ($request->session()->get('selected_student_id') ?? ''),
                    'concept' => 'enrollment_plus_first_month',
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
            $authUser = Auth::user();
            if ($authUser instanceof User) {
                $request->session()->put('students', $authUser->students()->orderBy('name')->get());
            }

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
            $authUser = Auth::user();
            if ($authUser instanceof User) {
                $request->session()->put('students', $authUser->students()->orderBy('name')->get());
            }
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
            'program_id' => 'required|integer|exists:programs,id',
            'selected_courses' => 'required|array|min:1',
            'selected_courses.*' => 'integer|exists:courses,id',
        ]);

        if ($validator->fails()) {
            return $this->wizardValidationError($request, $validator);
        }

        $programId = (int) $request->input('program_id');
        $courseIds = array_map('intval', $request->input('selected_courses', []));

        $locked = $request->session()->get('wizard_locked_course_id');
        if ($locked) {
            $allMatchLocked = count($courseIds) === 1 && (int) $courseIds[0] === (int) $locked;
            if (! $allMatchLocked) {
                $msg = 'El curso seleccionado no está permitido para esta inscripción';

                return $this->wizardJsonOrRedirect($request, [
                    'success' => false,
                    'message' => $msg,
                    'errors' => ['selected_courses' => [$msg]],
                ], back()->withErrors(['selected_courses' => $msg]));
            }
        }

        $program = Program::findOrFail($programId);

        $courses = Course::withCount('enrollments')
            ->whereIn('id', $courseIds)
            ->get();

        if ($courses->count() !== count($courseIds)) {
            $msg = 'Uno o más cursos no fueron encontrados';

            return $this->wizardJsonOrRedirect($request, [
                'success' => false,
                'message' => $msg,
                'errors' => ['selected_courses' => [$msg]],
            ], back()->withErrors(['selected_courses' => $msg]));
        }

        foreach ($courses as $course) {
            if ((int) $course->program_id !== $programId) {
                $msg = "El curso \"{$course->title}\" no pertenece al programa seleccionado";

                return $this->wizardJsonOrRedirect($request, [
                    'success' => false,
                    'message' => $msg,
                    'errors' => ['selected_courses' => [$msg]],
                ], back()->withErrors(['selected_courses' => $msg]));
            }
        }

        $studentId = $request->session()->get('selected_student_id');
        $student = Student::find($studentId);
        if (! $student) {
            return $this->wizardJsonOrRedirect($request, [
                'success' => false,
                'message' => 'Sesión de estudiante inválida',
                'errors' => ['selected_student' => ['Vuelve a seleccionar el estudiante.']],
            ], redirect()->route('enrollment.wizard'));
        }

        $studentAge = $student->birthdate ? Carbon::parse($student->birthdate)->age : null;
        $validCourseIds = [];
        $waitlistedCourses = [];
        $errorMessages = [];

        foreach ($courses as $course) {
            if (! $course->active || Carbon::parse($course->end_date)->lt(Carbon::today())) {
                $errorMessages[] = "El curso \"{$course->title}\" no está disponible o ya finalizó";
                continue;
            }

            if ($studentAge !== null) {
                if ($course->min_age !== null && (float) $studentAge < (float) $course->min_age) {
                    $errorMessages[] = "El estudiante no cumple con la edad mínima ({$course->min_age} años) para \"{$course->title}\"";
                    continue;
                }
                if ($course->max_age !== null && (float) $studentAge > (float) $course->max_age) {
                    $errorMessages[] = "El estudiante excede la edad máxima ({$course->max_age} años) para \"{$course->title}\"";
                    continue;
                }
            }

            $existingEnrollment = Enrollment::query()
                ->where('student_id', $student->id)
                ->whereHas('courses', fn($q) => $q->where('course_id', $course->id))
                ->first();

            if ($existingEnrollment) {
                if ($existingEnrollment->is_free_trial) {
                    // Allow re-enrollment — the old trial will be cancelled on confirmation
                } else {
                    $errorMessages[] = "El estudiante ya está inscrito en \"{$course->title}\"";
                    continue;
                }
            }

            $spotsLeft = $course->capacity - $course->enrollments_count;
            if ($spotsLeft <= 0) {
                Waitlist::create([
                    'student_id' => $student->id,
                    'course_id' => $course->id,
                    'parent_id' => Auth::id(),
                    'status' => 'pending',
                ]);
                $waitlistedCourses[] = $course->title;
                continue;
            }

            $validCourseIds[] = $course->id;
        }

        if (! empty($errorMessages)) {
            $msg = implode('. ', $errorMessages);

            return $this->wizardJsonOrRedirect($request, [
                'success' => false,
                'message' => $msg,
                'errors' => ['selected_courses' => $errorMessages],
            ], back()->withErrors(['selected_courses' => $errorMessages]));
        }

        if (empty($validCourseIds)) {
            $msg = 'Ninguno de los cursos seleccionados tiene cupos disponibles.';

            if (! empty($waitlistedCourses)) {
                $msg .= ' Se te ha agregado a la lista de espera para: ' . implode(', ', $waitlistedCourses) . '.';
            }

            return $this->wizardJsonOrRedirect($request, [
                'success' => false,
                'message' => $msg,
                'errors' => ['selected_courses' => [$msg]],
            ], back()->withErrors(['selected_courses' => $msg]));
        }

        $request->session()->put('program_id', $programId);
        $request->session()->put('selected_course_ids', $validCourseIds);
        $request->session()->put('enrollment_step', 4);

        $responseData = [
            'success' => true,
            'next_step' => 4,
            'data' => $this->wizardPayload($request),
        ];

        if (! empty($waitlistedCourses)) {
            $responseData['message'] = 'Algunos cursos no tienen cupo. Se te ha agregado a la lista de espera para: ' . implode(', ', $waitlistedCourses) . '.';
            $responseData['waitlisted'] = $waitlistedCourses;
        }

        return $this->wizardJsonOrRedirect($request, $responseData, redirect()->route('enrollment.wizard'));
    }

    protected function handleStep4(Request $request): RedirectResponse|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:card,pending',
            'is_clase_prueba' => 'nullable|boolean',
            'stripe_payment_intent_id' => 'nullable|string|max:255',
            'payment_receipt' => 'bail|nullable|file|mimes:jpg,jpeg,png,pdf|max:6144',
        ]);

        if ($validator->fails()) {
            return $this->wizardValidationError($request, $validator);
        }

        $paymentMethod = $request->input('payment_method');
        $isFreeTrial = (bool) ($request->boolean('is_clase_prueba') || $request->boolean('is_free_trial'));
        if ($paymentMethod === 'card') {
            $isFreeTrial = false;
        }

        if ($paymentMethod === 'pending' && ! $isFreeTrial && ! $request->hasFile('payment_receipt') && ! $request->session()->has('payment_receipt_path')) {
            $validator = Validator::make([], []);
            $validator->errors()->add('payment_receipt', 'Adjunta el comprobante (PDF o imagen) para pagos manuales.');

            return $this->wizardValidationError($request, $validator);
        }

        if ($paymentMethod === 'card' && ! $request->filled('stripe_payment_intent_id')) {
            $validator = Validator::make([], []);
            $validator->errors()->add('stripe_payment_intent_id', 'Completa el pago con tarjeta para continuar.');

            return $this->wizardValidationError($request, $validator);
        }

        $programId = $request->session()->get('program_id');
        $courseIds = $request->session()->get('selected_course_ids', []);

        $program = $programId ? Program::find($programId) : null;
        $selectedCourses = Course::whereIn('id', $courseIds)->get();

        $total = 0;
        if ($program) {
            $total += (float) ($program->enrollment_fee ?? 50.00);
        }
        foreach ($selectedCourses as $course) {
            $total += (float) ($course->monthly_fee ?? 0);
        }

        $request->session()->put('payment_method', $paymentMethod);
        $request->session()->put('is_free_trial', $isFreeTrial);
        $request->session()->put('wizard_total', $total);

        if ($request->hasFile('payment_receipt')) {
            $previousPath = $request->session()->get('payment_receipt_path');
            if ($previousPath && file_exists(public_path('uploads/comprobantes/' . basename($previousPath)))) {
                unlink(public_path('uploads/comprobantes/' . basename($previousPath)));
            }

            $file = $request->file('payment_receipt');
            $storedPath = $file->move(public_path('uploads/comprobantes'), $file->hashName());
            $request->session()->put('payment_receipt_path', 'uploads/comprobantes/' . $file->hashName());
            $request->session()->put('payment_receipt_original_name', $file->getClientOriginalName());
        } elseif ($paymentMethod === 'card' || $isFreeTrial) {
            $request->session()->forget(['payment_receipt_path', 'payment_receipt_original_name']);
        }

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
            'image_consent' => 'accepted',
        ]);

        if ($validator->fails()) {
            return $this->wizardValidationError($request, $validator);
        }

        $studentId = $request->session()->get('selected_student_id');
        $programId = $request->session()->get('program_id');
        $courseIds = $request->session()->get('selected_course_ids', []);
        $paymentMethod = $request->session()->get('payment_method');
        $isFreeTrial = (bool) $request->session()->get('is_free_trial', false);
        $paymentReceiptPath = $request->session()->get('payment_receipt_path');
        $paymentReceiptOriginalName = $request->session()->get('payment_receipt_original_name');
        $stripePaymentIntentId = $request->session()->get('stripe_payment_intent_id');

        if ($paymentMethod === 'pending' && ! $isFreeTrial && ! $paymentReceiptPath) {
            return $this->wizardJsonOrRedirect($request, [
                'success' => false,
                'message' => 'Debes adjuntar un comprobante para el pago manual.',
                'errors' => ['payment_receipt' => ['Debes adjuntar un comprobante para el pago manual.']],
            ], redirect()->route('enrollment.wizard')->withErrors([
                'payment_receipt' => 'Debes adjuntar un comprobante para el pago manual.',
            ]));
        }

        if (! $studentId || ! $programId || empty($courseIds) || ! $paymentMethod) {
            return $this->wizardJsonOrRedirect($request, [
                'success' => false,
                'message' => 'Sesión incompleta. Reinicia el proceso.',
                'errors' => ['session' => ['Sesión incompleta.']],
            ], redirect()->route('enrollment.wizard')->withErrors(['session' => 'Sesión incompleta.']));
        }

        $student = Student::find($studentId);
        $program = Program::find($programId);
        $courses = Course::withCount('enrollments')->whereIn('id', $courseIds)->get();

        if (! $student || ! $program || $courses->isEmpty()) {
            return $this->wizardJsonOrRedirect($request, [
                'success' => false,
                'message' => 'Sesión inválida. Reinicia el proceso.',
                'errors' => ['session' => ['Sesión inválida.']],
            ], redirect()->route('enrollment.wizard')->withErrors(['session' => 'Sesión inválida.']));
        }

        foreach ($courses as $course) {
            if (! $course->active || Carbon::parse($course->end_date)->lt(Carbon::today())) {
                $msg = "El curso \"{$course->title}\" ya no está vigente.";

                return $this->wizardJsonOrRedirect($request, [
                    'success' => false,
                    'message' => $msg,
                    'errors' => ['selected_courses' => [$msg]],
                ], redirect()->route('enrollment.wizard')->withErrors(['selected_courses' => $msg]));
            }

            $existingEnrollment = Enrollment::query()
                ->where('student_id', $student->id)
                ->whereHas('courses', fn($q) => $q->where('course_id', $course->id))
                ->where('is_free_trial', false)
                ->exists();

            if ($existingEnrollment) {
                $msg = "El estudiante ya está inscrito en \"{$course->title}\"";

                return $this->wizardJsonOrRedirect($request, [
                    'success' => false,
                    'message' => $msg,
                    'errors' => ['selected_courses' => [$msg]],
                ], redirect()->route('enrollment.wizard')->withErrors(['selected_courses' => $msg]));
            }

            $spotsLeft = $course->capacity - $course->enrollments_count;
            if ($spotsLeft <= 0) {
                $msg = "Lo sentimos, \"{$course->title}\" ya no tiene cupos disponibles";

                return $this->wizardJsonOrRedirect($request, [
                    'success' => false,
                    'message' => $msg,
                    'errors' => ['selected_courses' => [$msg]],
                ], redirect()->route('enrollment.wizard')->withErrors(['selected_courses' => $msg]));
            }
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

        try {
            $enrollment = DB::transaction(function () use (
                $studentId, $programId, $courseIds, $paymentMethod,
                $student, $program, $courses, $stripePaymentIntentId,
                $isFreeTrial, $paymentReceiptPath, $paymentReceiptOriginalName
            ) {
                $isCardPayment = $paymentMethod === 'card';
                $effectivePaymentMethod = $isFreeTrial ? 'clase_prueba' : $paymentMethod;
                $paymentStatus = ($isCardPayment || $isFreeTrial) ? 'paid' : 'pending';

                $existingEnrollment = Enrollment::query()
                    ->where('student_id', $studentId)
                    ->where('program_id', $programId)
                    ->where('status', '!=', 'cancelled')
                    ->where('is_free_trial', false)
                    ->first();

                if ($existingEnrollment) {
                    $existingCourseIds = $existingEnrollment->courses()->pluck('course_id')->toArray();
                    $newCourseIds = array_diff($courseIds, $existingCourseIds);

                    if (empty($newCourseIds)) {
                        $existingEnrollment->courses()->sync(array_unique(array_merge($existingCourseIds, $courseIds)));

                        return $existingEnrollment;
                    }

                    $existingEnrollment->courses()->sync(array_unique(array_merge($existingCourseIds, $courseIds)));

                    $newCourses = Course::whereIn('id', $newCourseIds)->get();
                    $totalMonthlyFee = $newCourses->sum(fn($c) => (float) ($c->monthly_fee ?? 0));
                    $installmentMonths = $this->calculateCombinedInstallmentMonths($newCourses);
                    $totalAdditional = $totalMonthlyFee * $installmentMonths;

                    if ($totalAdditional <= 0) {
                        if ($paymentStatus === 'paid' && $existingEnrollment->payment_status !== 'paid') {
                            $existingEnrollment->payment_status = 'paid';
                            $existingEnrollment->status = 'completed';
                        }
                        $existingEnrollment->save();

                        return $existingEnrollment;
                    }

                    $existingEnrollment->payment_status = $existingEnrollment->payment_status === 'paid' ? 'paid' : $paymentStatus;
                    $existingEnrollment->status = $existingEnrollment->payment_status === 'paid' ? 'completed' : $existingEnrollment->status;
                    $existingEnrollment->save();

                    $branchId = $newCourses->first()->branch_id;
                    $studentName = optional($student)->name ?? 'Estudiante';

                    $receivable = AccountReceivable::create([
                        'branch_id' => $branchId,
                        'enrollment_id' => $existingEnrollment->id,
                        'title' => 'Mensualidades adicionales #' . $existingEnrollment->id . ' - ' . $studentName,
                        'amount_total' => $totalAdditional,
                        'balance_due' => $totalAdditional,
                        'currency' => 'USD',
                        'status' => $paymentMethod === 'pending' ? 'pending' : 'partial',
                        'due_date' => $newCourses->min('start_date')
                            ? Carbon::parse($newCourses->min('start_date'))->toDateString()
                            : now()->toDateString(),
                        'notes' => 'Clases adicionales agregadas a la inscripción existente.',
                    ]);

                    $this->createInstallmentsForEnrollment($existingEnrollment, $newCourses, $receivable, $installmentMonths, $totalMonthlyFee, $paymentMethod === 'card');

                    if ($paymentMethod === 'card' && $stripePaymentIntentId && str_starts_with($stripePaymentIntentId, 'pi_')) {
                        Transaction::create([
                            'enrollment_id' => $existingEnrollment->id,
                            'student_id' => $student->id,
                            'course_id' => $newCourses->first()->id,
                            'branch_id' => $branchId,
                            'account_id' => $this->resolveIncomeAccountId($paymentMethod),
                            'account_receivable_id' => $receivable->id,
                            'amount' => $totalMonthlyFee,
                            'currency' => 'USD',
                            'type' => 'income',
                            'status' => 'completed',
                            'payment_method' => 'stripe',
                            'reference' => $stripePaymentIntentId,
                            'description' => 'Pago de mensualidades adicionales: ' . $courseTitles,
                        ]);

                        $this->refreshReceivableBalance($receivable->fresh());
                    }

                    return $existingEnrollment;
                }

                $enrollment = new Enrollment;
                $enrollment->student_id = $studentId;
                $enrollment->program_id = $programId;
                $enrollment->parent_id = Auth::id();
                $enrollment->status = $paymentStatus === 'paid' ? 'completed' : 'pending';
                $enrollment->payment_method = $effectivePaymentMethod;
                $enrollment->payment_status = $paymentStatus;
                $enrollment->is_free_trial = $isFreeTrial;
                $enrollment->terms_accepted = true;
                $enrollment->image_consent_accepted = true;
                $enrollment->payment_receipt_path = $paymentReceiptPath;
                $enrollment->payment_receipt_original_name = $paymentReceiptOriginalName;
                $enrollment->save();

                $enrollment->courses()->sync($courseIds);

                // Cancel any previous free trial enrollment for the same program
                if (! $isFreeTrial) {
                    $previousTrial = Enrollment::query()
                        ->where('student_id', $studentId)
                        ->where('program_id', $programId)
                        ->where('is_free_trial', true)
                        ->where('id', '!=', $enrollment->id)
                        ->first();

                    if ($previousTrial) {
                        $previousTrial->update(['status' => 'cancelled']);
                    }
                }

                if ($isFreeTrial) {
                    EnrollmentBillingProfile::updateOrCreate(
                        ['enrollment_id' => $enrollment->id],
                        [
                            'billing_mode' => 'manual',
                            'auto_pay_enabled' => false,
                            'status' => 'active',
                        ]
                    );

                    return $enrollment;
                }

                $installmentMonths = $this->calculateCombinedInstallmentMonths($courses);
                $enrollmentFee = (float) ($program->enrollment_fee ?? 50.00);
                $totalMonthlyFee = $courses->sum(fn($c) => (float) ($c->monthly_fee ?? 0));
                $totalReceivable = $enrollmentFee + ($totalMonthlyFee * $installmentMonths);

                $branchId = $courses->first()->branch_id;
                $studentName = optional($student)->name ?? 'Estudiante';

                $receivable = AccountReceivable::create([
                    'branch_id' => $branchId,
                    'enrollment_id' => $enrollment->id,
                    'title' => 'Inscripción #' . $enrollment->id . ' - ' . $studentName . ' (' . ($program->name ?? 'Programa') . ')',
                    'amount_total' => $totalReceivable,
                    'balance_due' => $totalReceivable,
                    'currency' => 'USD',
                    'status' => $paymentMethod === 'pending' ? 'pending' : 'partial',
                    'due_date' => $courses->min('start_date')
                        ? Carbon::parse($courses->min('start_date'))->toDateString()
                        : now()->toDateString(),
                    'notes' => 'Incluye inscripción y plan de mensualidades.',
                ]);

                $this->createInstallmentsForEnrollment($enrollment, $courses, $receivable, $installmentMonths, $totalMonthlyFee, $paymentMethod === 'card');

                if ($enrollment->payment_status === 'paid') {
                    $initialAmount = $this->calculateInitialChargeAmount($program, $courses->all());

                    Transaction::create([
                        'enrollment_id' => $enrollment->id,
                        'student_id' => $student->id,
                        'course_id' => $courses->first()->id,
                        'branch_id' => $branchId,
                        'account_id' => $this->resolveIncomeAccountId($paymentMethod),
                        'account_receivable_id' => $receivable->id,
                        'amount' => $initialAmount,
                        'currency' => 'USD',
                        'type' => 'income',
                        'status' => 'completed',
                        'payment_method' => $paymentMethod === 'card' ? 'stripe' : $paymentMethod,
                        'reference' => $stripePaymentIntentId,
                        'description' => 'Pago de inscripción + 1er mes: ' . $courseTitles,
                        'payment_receipt_path' => $paymentReceiptPath,
                        'payment_receipt_original_name' => $paymentReceiptOriginalName,
                    ]);

                    $firstInstallment = EnrollmentInstallment::query()
                        ->where('enrollment_id', $enrollment->id)
                        ->where('is_first_month', true)
                        ->first();

                    if ($firstInstallment) {
                        $firstInstallment->update([
                            'status' => 'paid',
                            'stripe_payment_intent_id' => $stripePaymentIntentId,
                            'paid_at' => now(),
                        ]);
                    }

                    $this->refreshReceivableBalance($receivable->fresh());
                }

                if ($paymentMethod === 'card' && $stripePaymentIntentId && str_starts_with($stripePaymentIntentId, 'pi_')) {
                    $this->setupRecurringSubscription($enrollment, $courses, $stripePaymentIntentId);
                } else {
                    EnrollmentBillingProfile::updateOrCreate(
                        ['enrollment_id' => $enrollment->id],
                        [
                            'billing_mode' => 'manual',
                            'auto_pay_enabled' => false,
                            'status' => 'active',
                        ]
                    );
                }

                return $enrollment;
            });
        } catch (\Throwable $e) {
            return $this->wizardJsonOrRedirect($request, [
                'success' => false,
                'message' => 'No se pudo completar la configuración de cobro automático.',
                'errors' => ['payment_method' => [$e->getMessage()]],
            ], redirect()->route('enrollment.wizard')->withErrors([
                'payment_method' => 'No se pudo completar la configuración de cobro automático.',
            ]));
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
            'program_id',
            'selected_course_ids',
            'payment_method',
            'is_free_trial',
            'stripe_payment_intent_id',
            'payment_receipt_path',
            'payment_receipt_original_name',
            'wizard_locked_course_id',
            'wizard_total',
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
            'program_id',
            'selected_course_ids',
            'payment_method',
            'is_free_trial',
            'stripe_payment_intent_id',
            'payment_receipt_path',
            'payment_receipt_original_name',
            'wizard_locked_course_id',
            'wizard_total',
        ];
        foreach ($keys as $key) {
            $request->session()->forget($key);
        }

        return redirect()->route('enrollment.wizard');
    }

    protected function loadCoursesForWizard(?int $lockedCourseId, ?int $studentAge, ?int $programId = null)
    {
        $q = Course::query()
            ->where('active', true)
            ->whereDate('end_date', '>=', now()->toDateString())
            ->withCount('enrollments')
            ->with(['program', 'branch']);

        if ($lockedCourseId) {
            $q->whereKey($lockedCourseId);
        }

        if ($programId) {
            $q->where('program_id', $programId);
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
                if ($course->min_age !== null && (float) $studentAge < (float) $course->min_age) {
                    $course->can_enroll = false;
                    $course->enroll_error = "Edad mínima requerida: {$course->min_age} años";
                }
                if ($course->max_age !== null && (float) $studentAge > (float) $course->max_age) {
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

        $studentAge = $studentBirthdate ? round(Carbon::parse($studentBirthdate)->floatDiffInYears(), 1) : null;
        $programId = $request->session()->get('program_id');
        $courses = $this->loadCoursesForWizard($lockedCourseId, $studentAge, $programId ? (int) $programId : null);

        $students = [];
        if (Auth::check()) {
            $authUser = Auth::user();
            if ($authUser instanceof User) {
                $students = $authUser->students()->orderBy('name')->get()->map(fn($st) => [
                    'id' => $st->id,
                    'name' => $st->name,
                    'birthdate' => $st->birthdate ? Carbon::parse($st->birthdate)->format('Y-m-d') : null,
                ])->values()->all();
            }
        }

        $programs = Program::where('active', true)->orderBy('name')->get()->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'enrollment_fee' => (float) ($p->enrollment_fee ?? 50.00),
        ])->values()->all();

        $branches = Branch::orderBy('name')->get()->map(fn($b) => [
            'id' => $b->id,
            'name' => $b->name,
        ])->values()->all();

        $courseIds = $request->session()->get('selected_course_ids', []);
        $selectedCourseModels = ! empty($courseIds)
            ? Course::withCount('enrollments')->whereIn('id', $courseIds)->get()
            : collect();

        $courseSchedules = [];
        foreach ($selectedCourseModels as $c) {
            $courseSchedules[$c->id] = $this->formatCourseSchedule($c);
        }

        $programModel = $programId ? Program::find($programId) : null;
        $total = $request->session()->get('wizard_total');

        return [
            'authenticated' => Auth::check(),
            'enrollment_step' => (int) $request->session()->get('enrollment_step', 1),
            'students' => $students,
            'selected_student_id' => $studentId ? (int) $studentId : null,
            'selected_student_name' => $studentName,
            'courses' => $courses->map(fn($c) => $this->serializeCourse($c))->values()->all(),
            'programs' => $programs,
            'branches' => $branches,
            'program_id' => $programId ? (int) $programId : null,
            'selected_course_ids' => array_map('intval', $courseIds),
            'selected_courses' => $selectedCourseModels->map(fn($c) => $this->serializeCourse($c))->values()->all(),
            'course_schedules' => $courseSchedules,
            'enrollment_fee' => $programModel ? (float) ($programModel->enrollment_fee ?? 50.00) : null,
            'program_enrollment_fee' => $programModel ? (float) ($programModel->enrollment_fee ?? 50.00) : null,
            'total' => $total !== null ? (float) $total : null,
            'locked_course_id' => $lockedCourseId ? (int) $lockedCourseId : null,
            'payment_method' => $request->session()->get('payment_method'),
            'is_free_trial' => (bool) $request->session()->get('is_free_trial', false),
            'payment_receipt_name' => $request->session()->get('payment_receipt_original_name'),
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
            'program_id' => $course->program_id,
            'branch_id' => $course->branch_id,
            'min_age' => $course->min_age !== null ? (float) $course->min_age : null,
            'max_age' => $course->max_age !== null ? (float) $course->max_age : null,
            'capacity' => $course->capacity,
            'enrollments_count' => $course->enrollments_count ?? 0,
            'spots_left' => $spotsLeft,
            'price' => $course->price !== null ? (float) $course->price : null,
            'monthly_fee' => $course->monthly_fee !== null ? (float) $course->monthly_fee : null,
            'can_enroll' => (bool) ($course->can_enroll ?? true),
            'enroll_error' => $course->enroll_error,
            'schedule' => $this->formatCourseSchedule($course),
            'branch_name' => optional($course->branch)->name,
        ];
    }

    protected function formatCourseSchedule(Course $course): string
    {
        $weekDays = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábados',
            7 => 'Domingos',
        ];

        $slots = $course->classes
            ->map(function ($class) {
                $date = $class->date instanceof Carbon
                    ? $class->date
                    : Carbon::parse($class->date);

                return [
                    'day' => (int) $date->dayOfWeekIso,
                    'time' => substr((string) $class->start_time, 0, 5),
                ];
            })
            ->unique(fn($slot) => $slot['day'] . '|' . $slot['time'])
            ->sortBy([
                ['day', 'asc'],
                ['time', 'asc'],
            ])
            ->values();

        if ($slots->isEmpty()) {
            return 'Horario por confirmar';
        }

        return $slots
            ->map(function ($slot) use ($weekDays) {
                $carbonTime = Carbon::createFromFormat('H:i', $slot['time']);

                return ($weekDays[$slot['day']] ?? 'Dia') . ' ' . mb_strtolower($carbonTime->format('g:i A'));
            })
            ->map(fn($value) => str_replace(['am', 'pm'], ['a.m.', 'p.m.'], $value))
            ->implode(' • ');
    }

    protected function calculateInitialChargeAmount(Program $program, array $courses): float
    {
        $enrollmentFee = (float) ($program->enrollment_fee ?? 50.00);
        $monthlySum = array_sum(array_map(fn($c) => (float) ($c->monthly_fee ?? 0), $courses));

        return $enrollmentFee + $monthlySum;
    }

    protected function calculateCombinedInstallmentMonths($courses): int
    {
        $startDates = $courses->map(fn($c) => $c->start_date ? Carbon::parse($c->start_date) : null)->filter();
        $endDates = $courses->map(fn($c) => $c->end_date ? Carbon::parse($c->end_date) : null)->filter();

        if ($startDates->isEmpty() || $endDates->isEmpty()) {
            return 1;
        }

        $start = $startDates->min()->startOfMonth();
        $end = $endDates->max()->startOfMonth();

        return max(1, $start->diffInMonths($end) + 1);
    }

    protected function createInstallmentsForEnrollment(
        Enrollment $enrollment,
        $courses,
        AccountReceivable $receivable,
        int $installmentMonths,
        float $totalMonthlyFee,
        bool $firstMonthPaid
    ): void {
        if ($totalMonthlyFee <= 0) {
            return;
        }

        $startDates = $courses->map(fn($c) => $c->start_date ? Carbon::parse($c->start_date) : null)->filter();
        $baseDate = $startDates->isNotEmpty()
            ? $startDates->min()->startOfDay()
            : now()->startOfDay();

        for ($offset = 0; $offset < $installmentMonths; $offset++) {
            $dueDate = (clone $baseDate)->addMonthsNoOverflow($offset);

            EnrollmentInstallment::create([
                'enrollment_id' => $enrollment->id,
                'account_receivable_id' => $receivable->id,
                'period_year' => (int) $dueDate->year,
                'period_month' => (int) $dueDate->month,
                'due_date' => $dueDate->toDateString(),
                'amount' => $totalMonthlyFee,
                'currency' => 'USD',
                'status' => ($offset === 0 && $firstMonthPaid) ? 'paid' : 'pending',
                'is_first_month' => $offset === 0,
                'paid_at' => ($offset === 0 && $firstMonthPaid) ? now() : null,
            ]);
        }
    }

    protected function setupRecurringSubscription(Enrollment $enrollment, $courses, string $stripePaymentIntentId): void
    {
        $totalMonthlyFee = $courses->sum(fn($c) => (float) ($c->monthly_fee ?? 0));
        if ($totalMonthlyFee <= 0) {
            EnrollmentBillingProfile::updateOrCreate(
                ['enrollment_id' => $enrollment->id],
                [
                    'billing_mode' => 'manual',
                    'auto_pay_enabled' => false,
                    'status' => 'active',
                ]
            );

            return;
        }

        $stripeSecret = config('services.stripe.secret');
        if (! $stripeSecret) {
            throw new \RuntimeException('Stripe no está configurado para suscripciones.');
        }

        $stripe = new StripeClient($stripeSecret);
        $paymentIntent = $stripe->paymentIntents->retrieve($stripePaymentIntentId, []);

        if (! $paymentIntent || empty($paymentIntent->payment_method)) {
            throw new \RuntimeException('No se encontró método de pago para activar la suscripción.');
        }

        $parent = $enrollment->parent()->first();
        if (! $parent) {
            throw new \RuntimeException('No se encontró el representante para crear la suscripción.');
        }

        $customerId = $parent->stripe_customer_id;
        if (! $customerId) {
            $customer = $stripe->customers->create([
                'name' => $parent->name,
                'email' => $parent->email,
                'metadata' => [
                    'parent_user_id' => (string) $parent->id,
                ],
            ]);
            $customerId = $customer->id;
            $parent->stripe_customer_id = $customerId;
            $parent->save();
        }

        $stripe->paymentMethods->attach($paymentIntent->payment_method, [
            'customer' => $customerId,
        ]);

        $stripe->customers->update($customerId, [
            'invoice_settings' => [
                'default_payment_method' => $paymentIntent->payment_method,
            ],
        ]);

        $courseTitles = $courses->pluck('title')->implode(', ');
        $billingAnchor = now()->addMonth()->startOfDay();
        $subscription = $stripe->subscriptions->create([
            'customer' => $customerId,
            'collection_method' => 'charge_automatically',
            'items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'recurring' => ['interval' => 'month'],
                    'unit_amount' => (int) round($totalMonthlyFee * 100),
                    'product_data' => [
                        'name' => 'Mensualidad - ' . $courseTitles,
                    ],
                ],
            ]],
            'billing_cycle_anchor' => $billingAnchor->timestamp,
            'proration_behavior' => 'none',
            'metadata' => [
                'enrollment_id' => (string) $enrollment->id,
                'program_id' => (string) ($enrollment->program_id ?? ''),
            ],
        ]);

        EnrollmentBillingProfile::updateOrCreate(
            ['enrollment_id' => $enrollment->id],
            [
                'billing_mode' => 'stripe_subscription',
                'auto_pay_enabled' => true,
                'stripe_customer_id' => $customerId,
                'stripe_subscription_id' => $subscription->id,
                'stripe_default_payment_method_id' => $paymentIntent->payment_method,
                'billing_anchor_day' => (int) $billingAnchor->day,
                'next_billing_date' => Carbon::createFromTimestamp((int) $subscription->current_period_end)->toDateString(),
                'status' => 'active',
            ]
        );
    }

    protected function refreshReceivableBalance(AccountReceivable $receivable): void
    {
        $paidAmount = (float) $receivable->transactions()->where('status', 'completed')->sum('amount');
        $balance = max(0, (float) $receivable->amount_total - $paidAmount);

        $status = 'pending';
        if ($balance <= 0) {
            $status = 'paid';
        } elseif ($paidAmount > 0) {
            $status = 'partial';
        }

        $receivable->update([
            'balance_due' => $balance,
            'status' => $status,
        ]);

        $this->syncInstallmentsPaymentStatus($receivable);
    }

    protected function syncInstallmentsPaymentStatus(AccountReceivable $receivable): void
    {
        if (!$receivable->enrollment_id) {
            return;
        }

        $enrollment = $receivable->enrollment;
        if (!$enrollment) {
            return;
        }

        $totalPaid = (float) $receivable->transactions()->where('status', 'completed')->sum('amount');
        $program = $enrollment->program;
        $enrollmentFee = $program ? (float) ($program->enrollment_fee ?? 50.00) : 0.0;
        
        $remainingPaid = max(0.0, $totalPaid - $enrollmentFee);
        $installments = $enrollment->installments()->orderBy('due_date')->get();

        foreach ($installments as $installment) {
            $installmentAmount = (float) $installment->amount;
            if ($remainingPaid >= $installmentAmount) {
                $installment->update([
                    'status' => 'paid',
                    'paid_at' => $installment->paid_at ?? now(),
                ]);
                $remainingPaid -= $installmentAmount;
            } elseif ($remainingPaid > 0) {
                $installment->update([
                    'status' => 'pending',
                ]);
                $remainingPaid = 0.0;
            } else {
                if ($installment->status === 'paid') {
                    $installment->update([
                        'status' => 'pending',
                        'paid_at' => null,
                    ]);
                }
            }
        }
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
