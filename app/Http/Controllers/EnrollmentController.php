<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\{Account, AccountReceivable, Branch, Course, Enrollment, EnrollmentBillingProfile, EnrollmentInstallment, Program, Student, Transaction, User};
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EnrollmentController extends Controller
{
    public function index()
    {
        $enrollments = Enrollment::with(['student.user', 'program', 'courses.branch'])->orderBy('id', 'desc')->get();
        $students = Student::with('user')->orderBy('name')->get();
        $programs = Program::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();
        $courses = Course::query()
            ->withCount('enrollments')
            ->with(['branch', 'classes' => function ($q) {
                $q->orderBy('date')->orderBy('start_time');
            }])
            ->where('active', true)
            ->whereDate('end_date', '>=', now()->toDateString())
            ->orderBy('title')
            ->get();
        $parents = User::where('role', 'Padre')->orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();

        return view('enrollments.index', [
            'enrollments' => $enrollments,
            'students' => $students,
            'parents' => $parents,
            'programs' => $programs,
            'courses' => $courses,
            'branches' => $branches,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
            'program_id' => ['required', 'integer', 'exists:programs,id'],
            'course_ids' => ['required', 'array', 'min:1'],
            'course_ids.*' => ['integer', 'exists:courses,id'],
            'payment_status' => ['required', Rule::in(['pending', 'paid'])],
            'is_free_trial' => ['nullable', 'boolean'],
            'image_consent_accepted' => ['nullable', 'boolean'],
            'payment_receipt' => [
                'bail',
                Rule::requiredIf(fn () => ! $request->boolean('is_free_trial') && $request->input('payment_status') === 'paid'),
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:6144',
            ],
            'user.name' => ['nullable', 'string', 'max:255'],
            'user.email' => ['nullable', 'email', 'max:255'],
            'user.dial_code' => ['nullable', 'string', 'max:6'],
            'user.whatsapp' => ['nullable', 'string', 'max:30'],
            'user.password' => ['nullable', 'string', 'min:8'],
            'student.name' => ['nullable', 'string', 'max:255'],
            'student.birthdate' => ['nullable', 'date', 'before_or_equal:today'],
            'student.medical_notes' => ['nullable', 'string', 'max:2000'],
            'enrollment_fee_type' => ['nullable', Rule::in(['standard', 'custom'])],
            'custom_enrollment_fee' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($request): void {
            $parent = $this->resolveParent($request);
            $student = $this->resolveStudent($request, $parent);
            $program = Program::findOrFail((int) $request->input('program_id'));
            $courseIds = collect($request->input('course_ids'))->map(fn ($id) => (int) $id)->unique()->values();
            $courses = Course::query()
                ->withCount('enrollments')
                ->where('active', true)
                ->whereDate('end_date', '>=', now()->toDateString())
                ->whereIn('id', $courseIds)
                ->get();

            if ($courses->count() !== $courseIds->count()) {
                throw ValidationException::withMessages([
                    'course_ids' => 'Uno o mas cursos seleccionados no estan disponibles.',
                ]);
            }

            if ($courses->pluck('program_id')->unique()->count() > 1 || $courses->first()->program_id !== $program->id) {
                throw ValidationException::withMessages([
                    'course_ids' => 'Todos los cursos seleccionados deben pertenecer al mismo programa.',
                ]);
            }

            $this->validateCoursesForStudent($student, $courses);

            $paymentStatus = $request->string('payment_status')->toString();
            $isFreeTrial = (bool) $request->boolean('is_free_trial');
            $receiptPath = null;
            $receiptOriginalName = null;

            if ($request->hasFile('payment_receipt')) {
                $file = $request->file('payment_receipt');
                $destinationPath = public_path('uploads/comprobantes');
                if (! is_dir($destinationPath)) {
                    mkdir($destinationPath, 0775, true);
                }

                $file->move($destinationPath, $file->hashName());
                $receiptPath = 'uploads/comprobantes/'.$file->hashName();
                $receiptOriginalName = $file->getClientOriginalName();
            }

            if ($isFreeTrial) {
                $existingTrialEnrollments = Enrollment::where('student_id', $student->id)
                    ->where('is_free_trial', true)
                    ->whereHas('courses', function ($query) use ($courseIds) {
                        $query->whereIn('courses.id', $courseIds);
                    })
                    ->get();

                foreach ($existingTrialEnrollments as $trialEnrollment) {
                    $trialEnrollment->update(['status' => 'cancelled']);
                }
            }

            $enrollment = Enrollment::create([
                'student_id' => $student->id,
                'program_id' => $program->id,
                'parent_id' => $parent->id,
                'status' => ($paymentStatus === 'paid' || $isFreeTrial) ? 'completed' : 'pending',
                'payment_method' => $isFreeTrial ? 'free_trial' : 'manual',
                'payment_status' => $isFreeTrial ? 'paid' : $paymentStatus,
                'is_free_trial' => $isFreeTrial,
                'terms_accepted' => true,
                'image_consent_accepted' => (bool) $request->boolean('image_consent_accepted'),
                'payment_receipt_path' => $receiptPath,
                'payment_receipt_original_name' => $receiptOriginalName,
                'custom_enrollment_fee' => ($request->input('enrollment_fee_type') === 'custom') ? (float) $request->input('custom_enrollment_fee', 0.0) : null,
            ]);

            $enrollment->courses()->sync($courseIds);

            $this->syncEnrollmentReceivableState($enrollment);
            $this->ensureManualBillingArtifacts($enrollment, $paymentStatus === 'paid');

            if ($enrollment->payment_status === 'paid') {
                $enrollment->load(['student.user', 'program', 'courses']);
                $this->syncEnrollmentIncomeTransaction($enrollment);
            }
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Inscripción registrada correctamente.',
                'redirect' => url('enrollment'),
            ]);
        }

        return redirect()->to('enrollment')->with('success', 'Inscripción registrada correctamente.');
    }

    public function show(Request $request, Enrollment $enrollment)
    {
        $enrollment->loadMissing([
            'student.user',
            'program',
            'courses.branch',
            'courses.classes' => function ($query) {
                $query->with('coach')->orderBy('date')->orderBy('start_time');
            },
        ]);

        foreach ($enrollment->courses as $course) {
            $course->loadCount('enrollments');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'enrollment' => $this->enrollmentPayload($enrollment),
            ]);
        }

        return view('enrollments.show', [
            'enrollment' => $enrollment,
        ]);
    }

    public function downloadReceipt(Enrollment $enrollment)
    {
        $enrollment->loadMissing([
            'student.user',
            'program',
            'courses.branch',
            'courses.classes' => function ($query) {
                $query->with('coach')->orderBy('date')->orderBy('start_time');
            },
        ]);

        foreach ($enrollment->courses as $course) {
            $course->loadCount('enrollments');
        }

        $pdf = Pdf::loadView('enrollments.receipt-pdf', [
            'enrollment' => $enrollment,
            'generatedAt' => now(),
        ])->setPaper('a4');

        return $pdf->download('comprobante-Inscripción-'.$enrollment->id.'.pdf');
    }

    public function update(Request $request, Enrollment $enrollment)
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'completed', 'cancelled'])],
            'payment_status' => ['required', Rule::in(['pending', 'paid'])],
        ]);

        DB::transaction(function () use ($enrollment, $validated): void {
            $this->applyEnrollmentState(
                $enrollment,
                $validated['status'] ?? null,
                $validated['payment_status']
            );
        });

        $enrollment->refresh()->load(['student.user', 'program', 'courses']);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Inscripción actualizada correctamente.',
                'enrollment' => $this->enrollmentPayload($enrollment),
            ]);
        }

        return redirect()->route('enrollment.show', $enrollment)->with('success', 'Inscripción actualizada correctamente.');
    }

    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'enrollment_ids' => ['required', 'array', 'min:1'],
            'enrollment_ids.*' => ['integer', 'distinct', 'exists:enrollments,id'],
            'status' => ['nullable', Rule::in(['pending', 'completed', 'cancelled'])],
            'payment_status' => ['nullable', Rule::in(['pending', 'paid'])],
        ]);

        if (! isset($validated['status']) && ! isset($validated['payment_status'])) {
            throw ValidationException::withMessages([
                'status' => 'Debe indicar al menos un cambio de estado o pago.',
            ]);
        }

        $ids = collect($validated['enrollment_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        DB::transaction(function () use ($ids, $validated): void {
            $enrollments = Enrollment::with(['student.user', 'program', 'courses'])
                ->whereIn('id', $ids)
                ->get();

            foreach ($enrollments as $enrollment) {
                $this->applyEnrollmentState(
                    $enrollment,
                    $validated['status'] ?? null,
                    $validated['payment_status'] ?? null
                );
            }
        });

        $updatedEnrollments = Enrollment::with(['student.user', 'program', 'courses'])
            ->whereIn('id', $ids)
            ->get()
            ->map(fn (Enrollment $enrollment) => $this->enrollmentPayload($enrollment))
            ->values();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Cambios masivos aplicados correctamente.',
                'enrollments' => $updatedEnrollments,
            ]);
        }

        return redirect()->to('enrollment')->with('success', 'Cambios masivos aplicados correctamente.');
    }

    public function updateStatus(Request $request, Enrollment $enrollment): RedirectResponse
    {
        $request->validate([
            'status' => ['required', Rule::in(['pending', 'completed', 'cancelled'])],
            'payment_status' => ['nullable', Rule::in(['pending', 'paid'])],
        ]);

        $status = $request->string('status')->toString();
        $paymentStatus = $request->input('payment_status') ?: ($status === 'completed' ? 'paid' : 'pending');

        DB::transaction(function () use ($enrollment, $status, $paymentStatus): void {
            $this->applyEnrollmentState($enrollment, $status, $paymentStatus);
        });

        return redirect()->to('enrollment')->with('success', 'Estado de Inscripción actualizado.');
    }

    protected function applyEnrollmentState(
        Enrollment $enrollment,
        ?string $status,
        ?string $paymentStatus
    ): void {
        $enrollment->loadMissing(['program', 'courses']);
        $previousPaymentStatus = (string) $enrollment->payment_status;

        $resolvedStatus = $status ?? $enrollment->status;

        if (! $status && $paymentStatus) {
            $resolvedStatus = $paymentStatus === 'paid' ? 'completed' : 'pending';
        }

        $resolvedPaymentStatus = $paymentStatus
            ?? ($resolvedStatus === 'completed' ? 'paid' : ($resolvedStatus === 'cancelled' ? 'pending' : $enrollment->payment_status));

        $enrollment->status = $resolvedStatus;
        $enrollment->payment_status = $resolvedPaymentStatus;
        $enrollment->payment_method = $enrollment->payment_method ?: 'manual';
        $enrollment->save();

        $enrollment->load(['student.user', 'program', 'courses']);

        if ($previousPaymentStatus === 'paid' && $enrollment->payment_status === 'pending') {
            Transaction::query()
                ->where('enrollment_id', $enrollment->id)
                ->where('type', 'income')
                ->update(['account_receivable_id' => null]);
        }

        $this->syncEnrollmentReceivableState($enrollment);
        $this->ensureManualBillingArtifacts($enrollment, $enrollment->payment_status === 'paid');

        if ($enrollment->payment_status === 'paid') {
            $this->syncEnrollmentIncomeTransaction($enrollment);
        }
    }

    protected function syncEnrollmentIncomeTransaction(Enrollment $enrollment): void
    {
        $enrollment->loadMissing(['program', 'courses']);

        if ($enrollment->is_free_trial) {
            Transaction::query()
                ->where('enrollment_id', $enrollment->id)
                ->where('type', 'income')
                ->delete();

            return;
        }

        if (! $enrollment->student || ! $enrollment->program || $enrollment->courses->isEmpty()) {
            return;
        }

        $receivable = AccountReceivable::query()
            ->where('enrollment_id', $enrollment->id)
            ->first();

        $incomeTransaction = Transaction::where('enrollment_id', $enrollment->id)
            ->where('type', 'income')
            ->first();

        $firstCourse = $enrollment->courses->first();
        $courseId = $firstCourse ? $firstCourse->id : null;
        $branchId = $firstCourse ? $firstCourse->branch_id : null;

        $courseTitles = $enrollment->courses->pluck('title')->join(', ');

        $payload = [
            'enrollment_id' => $enrollment->id,
            'student_id' => $enrollment->student_id,
            'course_id' => $courseId,
            'branch_id' => $branchId,
            'account_id' => $this->resolveIncomeAccountId(),
            'account_receivable_id' => $receivable?->id,
            'amount' => $this->calculateInitialChargeAmount($enrollment->program, $enrollment->courses, $enrollment),
            'currency' => 'USD',
            'type' => 'income',
            'status' => 'completed',
            'payment_method' => $enrollment->payment_method ?: 'manual',
            'reference' => 'admin-enrollment-'.$enrollment->id,
            'description' => 'Pago confirmado de Inscripción + 1er mes: '.$courseTitles,
            'payment_receipt_path' => $enrollment->payment_receipt_path,
            'payment_receipt_original_name' => $enrollment->payment_receipt_original_name,
        ];

        if ($incomeTransaction) {
            $incomeTransaction->update($payload);
        } else {
            Transaction::create($payload);
        }

        if ($receivable) {
            $paidAmount = (float) $receivable->transactions()->sum('amount');
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
        }
    }

    protected function syncEnrollmentReceivableState(Enrollment $enrollment): void
    {
        $enrollment->loadMissing(['program', 'courses']);

        if ($enrollment->is_free_trial) {
            AccountReceivable::query()
                ->where('enrollment_id', $enrollment->id)
                ->delete();

            return;
        }

        if (! $enrollment->program || $enrollment->courses->isEmpty()) {
            return;
        }

        $firstCourse = $enrollment->courses->first();
        if ($firstCourse->branch_id === null) {
            return;
        }

        $amountTotal = $this->calculateEnrollmentReceivableTotal($enrollment->program, $enrollment->courses, $enrollment);

        $receivable = AccountReceivable::query()
            ->where('enrollment_id', $enrollment->id)
            ->first();

        $courseTitles = $enrollment->courses->pluck('title')->join(', ');
        $programName = $enrollment->program->name;
        $title = 'Inscripción + mensualidades #'.$enrollment->id.' - '.$programName.' ('.$courseTitles.')';

        if ($enrollment->payment_status === 'pending') {
            if (! $receivable) {
                AccountReceivable::create([
                    'branch_id' => $firstCourse->branch_id,
                    'enrollment_id' => $enrollment->id,
                    'title' => $title,
                    'amount_total' => $amountTotal,
                    'balance_due' => $amountTotal,
                    'currency' => 'USD',
                    'status' => 'pending',
                ]);

                return;
            }

            $receivable->update([
                'branch_id' => $firstCourse->branch_id,
                'title' => $title,
                'amount_total' => $amountTotal,
                'balance_due' => $amountTotal,
                'currency' => 'USD',
                'status' => 'pending',
            ]);

            return;
        }

        if (! $receivable) {
            return;
        }

        $paidAmount = (float) $receivable->transactions()->sum('amount');
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
    }

    protected function enrollmentPayload(Enrollment $enrollment): array
    {
        $program = $enrollment->program;
        $student = $enrollment->student;
        $parent = optional($student)->user;

        $courses = $enrollment->courses->map(function ($course) {
            $classes = ($course->relationLoaded('classes')) ? $course->classes : collect();

            return [
                'id' => (int) $course->id,
                'title' => $course->title,
                'description' => $course->description,
                'branch_name' => optional($course->branch)->name,
                'start_date' => $course->start_date,
                'end_date' => $course->end_date,
                'monthly_fee' => $course->monthly_fee,
                'capacity' => $course->capacity,
                'enrollments_count' => $course->enrollments_count,
                'classes' => $classes->map(function ($class) {
                    return [
                        'id' => (int) $class->id,
                        'date' => $class->date,
                        'start_time' => $class->start_time,
                        'end_time' => $class->end_time,
                        'coach_name' => optional($class->coach)->name,
                    ];
                })->values(),
            ];
        })->values();

        return [
            'id' => (int) $enrollment->id,
            'status' => (string) $enrollment->status,
            'payment_status' => (string) $enrollment->payment_status,
            'program_id' => (int) $enrollment->program_id,
            'program_name' => optional($program)->name,
            'program_enrollment_fee' => optional($program)->enrollment_fee,
            'courses' => $courses,
            'student_name' => optional($student)->name,
            'student_birthdate' => optional($student)->birthdate,
            'student_medical_notes' => optional($student)->medical_notes,
            'parent_name' => optional($parent)->name,
            'parent_email' => optional($parent)->email,
            'parent_whatsapp' => optional($parent)->whatsapp,
            'is_free_trial' => (bool) $enrollment->is_free_trial,
            'terms_accepted' => (bool) $enrollment->terms_accepted,
            'image_consent_accepted' => (bool) $enrollment->image_consent_accepted,
            'payment_receipt_path' => $enrollment->payment_receipt_path,
            'payment_receipt_original_name' => $enrollment->payment_receipt_original_name,
            'payment_receipt_url' => $enrollment->payment_receipt_path ? asset('storage/'.$enrollment->payment_receipt_path) : null,
        ];
    }

    protected function resolveParent(Request $request): User
    {
        if ($request->filled('user_id')) {
            return User::where('role', 'Padre')->findOrFail((int) $request->input('user_id'));
        }

        $request->validate([
            'user.name' => ['required', 'string', 'max:255'],
            'user.email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'user.password' => ['required', 'string', 'min:8'],
            'user.dial_code' => ['nullable', 'string', 'max:6'],
            'user.whatsapp' => ['nullable', 'string', 'max:30'],
        ]);

        return User::create([
            'name' => $request->input('user.name'),
            'email' => $request->input('user.email'),
            'password' => Hash::make($request->input('user.password')),
            'dial_code' => $request->input('user.dial_code'),
            'whatsapp' => $request->input('user.whatsapp'),
            'role' => 'Padre',
        ]);
    }

    protected function resolveStudent(Request $request, User $parent): Student
    {
        if ($request->filled('student_id')) {
            $student = Student::findOrFail((int) $request->input('student_id'));
            if ((int) $student->user_id !== (int) $parent->id) {
                throw ValidationException::withMessages([
                    'student_id' => 'El estudiante seleccionado no pertenece al representante.',
                ]);
            }

            return $student;
        }

        $request->validate([
            'student.name' => ['required', 'string', 'max:255'],
            'student.birthdate' => ['required', 'date', 'before_or_equal:today'],
            'student.medical_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        return Student::create([
            'name' => $request->input('student.name'),
            'birthdate' => $request->input('student.birthdate'),
            'medical_notes' => $request->input('student.medical_notes'),
            'user_id' => $parent->id,
        ]);
    }

    protected function validateCoursesForStudent(Student $student, $courses): void
    {
        foreach ($courses as $course) {
            $existingEnrollment = Enrollment::where('student_id', $student->id)
                ->whereHas('courses', function ($query) use ($course) {
                    $query->where('courses.id', $course->id);
                })
                ->first();

            if ($existingEnrollment && ! $existingEnrollment->is_free_trial) {
                throw ValidationException::withMessages([
                    'course_ids' => 'Este estudiante ya esta inscrito en este programa.',
                ]);
            }

            if (((int) $course->capacity - (int) $course->enrollments_count) <= 0) {
                throw ValidationException::withMessages([
                    'course_ids' => 'El curso "'.$course->title.'" no tiene cupos disponibles.',
                ]);
            }

            if ($student->birthdate) {
                $age = Carbon::parse($student->birthdate)->floatDiffInYears(Carbon::now());

                if ($course->min_age && $age < (float) $course->min_age) {
                    throw ValidationException::withMessages([
                        'course_ids' => 'El estudiante no cumple con la edad minima del curso "'.$course->title.'".',
                    ]);
                }
                if ($course->max_age && $age > (float) $course->max_age) {
                    throw ValidationException::withMessages([
                        'course_ids' => 'El estudiante supera la edad maxima permitida para el curso "'.$course->title.'".',
                    ]);
                }
            }
        }
    }

    protected function resolveIncomeAccountId(): int
    {
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

    protected function calculateInitialChargeAmount(Program $program, $courses, ?Enrollment $enrollment = null): float
    {
        $enrollmentFee = ($enrollment && $enrollment->custom_enrollment_fee !== null)
            ? (float) $enrollment->custom_enrollment_fee
            : (float) ($program->enrollment_fee ?? 50.00);

        $total = $enrollmentFee;

        foreach ($courses as $course) {
            $total += (float) ($course->monthly_fee ?? 0);
        }

        return $total;
    }

    protected function calculateEnrollmentReceivableTotal(Program $program, $courses, ?Enrollment $enrollment = null): float
    {
        $enrollmentFee = ($enrollment && $enrollment->custom_enrollment_fee !== null)
            ? (float) $enrollment->custom_enrollment_fee
            : (float) ($program->enrollment_fee ?? 50.00);

        $total = $enrollmentFee;

        foreach ($courses as $course) {
            $months = 1;
            if ($course->start_date && $course->end_date) {
                $start = Carbon::parse($course->start_date)->startOfMonth();
                $end = Carbon::parse($course->end_date)->startOfMonth();
                $months = max(1, $start->diffInMonths($end) + 1);
            }
            $total += (float) ($course->monthly_fee ?? 0) * $months;
        }

        return $total;
    }

    protected function ensureManualBillingArtifacts(Enrollment $enrollment, bool $firstMonthPaid): void
    {
        if (($enrollment->payment_method ?? 'manual') !== 'manual') {
            return;
        }

        $enrollment->loadMissing(['program', 'courses']);
        $courses = $enrollment->courses;

        if ($courses->isEmpty()) {
            return;
        }

        $receivable = AccountReceivable::query()
            ->where('enrollment_id', $enrollment->id)
            ->first();

        EnrollmentBillingProfile::updateOrCreate(
            ['enrollment_id' => $enrollment->id],
            [
                'billing_mode' => 'manual',
                'auto_pay_enabled' => false,
                'status' => 'active',
                'billing_anchor_day' => (int) now()->day,
                'next_billing_date' => now()->addMonth()->toDateString(),
            ]
        );

        $hasAnyMonthlyFee = $courses->contains(function ($course) {
            return ((float) ($course->monthly_fee ?? 0)) > 0;
        });

        if (! $hasAnyMonthlyFee) {
            return;
        }

        $overallStartDate = null;
        $overallEndDate = null;

        foreach ($courses as $course) {
            if ($course->start_date) {
                $start = Carbon::parse($course->start_date);
                if ($overallStartDate === null || $start->lt($overallStartDate)) {
                    $overallStartDate = $start;
                }
            }
            if ($course->end_date) {
                $end = Carbon::parse($course->end_date);
                if ($overallEndDate === null || $end->gt($overallEndDate)) {
                    $overallEndDate = $end;
                }
            }
        }

        $baseDate = $overallStartDate
            ? $overallStartDate->copy()->startOfDay()
            : now()->startOfDay();

        $endDate = $overallEndDate
            ? $overallEndDate->copy()->startOfDay()
            : now()->addMonths(1)->startOfDay();

        $months = max(1, $baseDate->copy()->startOfMonth()->diffInMonths($endDate->copy()->startOfMonth()) + 1);

        for ($offset = 0; $offset < $months; $offset++) {
            $dueDate = (clone $baseDate)->addMonthsNoOverflow($offset);

            $monthlyTotal = 0.0;
            foreach ($courses as $course) {
                $courseStart = $course->start_date ? Carbon::parse($course->start_date)->startOfMonth() : null;
                $courseEnd = $course->end_date ? Carbon::parse($course->end_date)->startOfMonth() : null;
                $dueMonth = $dueDate->copy()->startOfMonth();

                if ($courseStart && $dueMonth->lt($courseStart)) {
                    continue;
                }
                if ($courseEnd && $dueMonth->gt($courseEnd)) {
                    continue;
                }

                $monthlyTotal += (float) ($course->monthly_fee ?? 0);
            }

            if ($monthlyTotal <= 0) {
                continue;
            }

            EnrollmentInstallment::updateOrCreate(
                [
                    'enrollment_id' => $enrollment->id,
                    'period_year' => (int) $dueDate->year,
                    'period_month' => (int) $dueDate->month,
                ],
                [
                    'account_receivable_id' => $receivable?->id,
                    'due_date' => $dueDate->toDateString(),
                    'amount' => $monthlyTotal,
                    'currency' => 'USD',
                    'status' => ($offset === 0 && $firstMonthPaid) ? 'paid' : 'pending',
                    'is_first_month' => $offset === 0,
                    'paid_at' => ($offset === 0 && $firstMonthPaid) ? now() : null,
                ]
            );
        }

        if ($receivable) {
            EnrollmentInstallment::query()
                ->where('enrollment_id', $enrollment->id)
                ->whereNull('account_receivable_id')
                ->update(['account_receivable_id' => $receivable->id]);
        }
    }
}
