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
            ->withCount(['enrollments' => function ($q) {
                $q->where('status', '!=', 'cancelled');
            }])
            ->with(['branch', 'classes' => function ($q) {
                $q->orderBy('date')->orderBy('start_time');
            }])
            ->where('active', true)
            ->whereDate('end_date', '>=', now()->toDateString())
            ->orderBy('title')
            ->get();
        $parents = User::where('role', 'Padre')->orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();
        $accounts = Account::where('active', true)->orderBy('name')->get();

        return view('enrollments.index', [
            'enrollments' => $enrollments,
            'students' => $students,
            'parents' => $parents,
            'programs' => $programs,
            'courses' => $courses,
            'branches' => $branches,
            'accounts' => $accounts,
        ]);
    }

    public function checkFee(Request $request): JsonResponse
    {
        $studentId = (int) $request->query('student_id');
        $programId = (int) $request->query('program_id');

        $hasPaid = $this->hasPaidProgramEnrollmentFee($studentId, $programId);

        return response()->json([
            'has_paid' => $hasPaid,
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
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
            'account_id' => [
                Rule::requiredIf(fn () => ! $request->boolean('is_free_trial') && $request->input('payment_status') === 'paid'),
                'nullable',
                'integer',
                'exists:accounts,id',
            ],
            'reference' => ['nullable', 'string', 'max:255'],
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
            'custom_total_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($request): void {
            $parent = $this->resolveParent($request);
            $student = $this->resolveStudent($request, $parent);
            $program = Program::findOrFail((int) $request->input('program_id'));
            $courseIds = collect($request->input('course_ids'))->map(fn ($id) => (int) $id)->unique()->values();
            $courses = Course::query()
                ->withCount(['enrollments' => function ($q) {
                    $q->where('status', '!=', 'cancelled');
                }])
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

            $accountId = null;
            $account = null;
            if (! $isFreeTrial && $paymentStatus === 'paid' && $request->filled('account_id')) {
                $accountId = (int) $request->input('account_id');
                $account = Account::find($accountId);
            }

            if ($request->hasFile('payment_receipt')) {
                $file = $request->file('payment_receipt');
                $destinationPath = public_path('uploads/comprobantes');

                $filename = uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($destinationPath, $filename);
                $receiptPath = 'uploads/comprobantes/'.$filename;
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

            $customEnrollmentFee = null;
            if ($request->input('enrollment_fee_type') === 'custom') {
                if ($request->has('custom_total_amount')) {
                    $customTotalAmount = (float) $request->input('custom_total_amount', 0.0);
                    $monthlyFeesSum = 0.0;
                    foreach ($courses as $course) {
                        $monthlyFeesSum += (float) ($course->monthly_fee ?? 0);
                    }
                    $customEnrollmentFee = max(0.0, $customTotalAmount - $monthlyFeesSum);
                } else {
                    $customEnrollmentFee = (float) $request->input('custom_enrollment_fee', 0.0);
                }
            } elseif ($this->hasPaidProgramEnrollmentFee($student->id, $program->id)) {
                $customEnrollmentFee = 0.0;
            }

            $enrollment = Enrollment::create([
                'student_id' => $student->id,
                'program_id' => $program->id,
                'parent_id' => $parent->id,
                'status' => ($paymentStatus === 'paid' || $isFreeTrial) ? 'completed' : 'pending',
                'payment_method' => $isFreeTrial ? 'free_trial' : ($account ? $account->name : 'manual'),
                'payment_status' => $isFreeTrial ? 'paid' : $paymentStatus,
                'is_free_trial' => $isFreeTrial,
                'terms_accepted' => true,
                'image_consent_accepted' => (bool) $request->boolean('image_consent_accepted'),
                'payment_receipt_path' => $receiptPath,
                'payment_receipt_original_name' => $receiptOriginalName,
                'custom_enrollment_fee' => $customEnrollmentFee,
            ]);

            $enrollment->courses()->sync($courseIds);

            $this->syncEnrollmentReceivableState($enrollment);
            $this->ensureManualBillingArtifacts($enrollment, $paymentStatus === 'paid');

            if ($enrollment->payment_status === 'paid') {
                $enrollment->load(['student.user', 'program', 'courses']);
                $reference = $request->input('reference');
                $this->syncEnrollmentIncomeTransaction($enrollment, null, $reference, $accountId);
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
            $course->loadCount(['enrollments' => function ($q) {
                $q->where('status', '!=', 'cancelled');
            }]);
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
            'courses.coaches',
            'courses.classes' => function ($query) {
                $query->with('coach')->orderBy('date')->orderBy('start_time');
            },
        ]);

        foreach ($enrollment->courses as $course) {
            $course->loadCount(['enrollments' => function ($q) {
                $q->where('status', '!=', 'cancelled');
            }]);
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
            'is_free_trial' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($enrollment, $validated): void {
            if (array_key_exists('is_free_trial', $validated)) {
                $enrollment->is_free_trial = (bool) $validated['is_free_trial'];
            }
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

        return redirect()->back()->with('success', 'Estado de Inscripción actualizado.');
    }



    public function attachPayment(Request $request, Enrollment $enrollment): RedirectResponse
    {
        $request->validate([
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'reference' => ['nullable', 'string', 'max:255'],
            'payment_receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:6144'],
            'amount_option' => ['required', 'string', 'in:suggested,custom'],
            'custom_amount' => ['nullable', 'numeric', 'min:0.01'],
        ]);

        $accountId = (int) $request->input('account_id');
        $account = Account::findOrFail($accountId);
        $reference = $request->input('reference');
        $receiptPath = $enrollment->payment_receipt_path;
        $receiptOriginalName = $enrollment->payment_receipt_original_name;

        // Calculate amount to pay
        $suggestedAmount = $enrollment->getInitialChargeAmount();
        $amountOption = $request->input('amount_option');
        $paymentAmount = $suggestedAmount;

        if ($amountOption === 'custom') {
            $paymentAmount = (float) $request->input('custom_amount');
        }

        if ($request->hasFile('payment_receipt')) {
            $file = $request->file('payment_receipt');
            $destinationPath = public_path('uploads/comprobantes');

            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $filename);
            $receiptPath = 'uploads/comprobantes/'.$filename;
            $receiptOriginalName = $file->getClientOriginalName();
        }

        DB::transaction(function () use ($enrollment, $account, $reference, $receiptPath, $receiptOriginalName, $paymentAmount, $amountOption): void {
            $enrollment->payment_method = $account->name;
            $enrollment->payment_receipt_path = $receiptPath;
            $enrollment->payment_receipt_original_name = $receiptOriginalName;
            $enrollment->is_free_trial = false;

            $this->applyEnrollmentState($enrollment, 'completed', 'paid', $paymentAmount, $reference, (int) $account->id);
        });

        return redirect()->back()->with('success', 'Pago registrado e inscripción confirmada exitosamente.');
    }

    public function updateCourseAmount(Request $request, Enrollment $enrollment, Course $course): RedirectResponse
    {
        $validated = $request->validate([
            'amount_total' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($enrollment, $course, $validated): void {
            $enrollment->courses()->updateExistingPivot($course->id, [
                'custom_amount' => (float) $validated['amount_total'],
            ]);

            $enrollment->syncReceivable();
        });

        return redirect()->back()->with('success', 'Monto de la cuenta por cobrar del curso actualizado correctamente.');
    }

    public function storeCoursePayment(Request $request, Enrollment $enrollment, Course $course): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'payment_date' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'payment_receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $account = Account::findOrFail($validated['account_id']);

        $receiptPath = null;
        $receiptOriginalName = null;
        if ($request->hasFile('payment_receipt')) {
            $file = $request->file('payment_receipt');
            $receiptPath = $file->store('receipts', 'public');
            $receiptOriginalName = $file->getClientOriginalName();
        }

        DB::transaction(function () use ($enrollment, $course, $account, $validated, $receiptPath, $receiptOriginalName): void {
            $receivable = $enrollment->receivable;
            if (!$receivable) {
                $enrollment->syncReceivable();
                $receivable = $enrollment->fresh()->receivable;
            }

            Transaction::create([
                'branch_id' => $receivable ? $receivable->branch_id : null,
                'user_id' => $enrollment->student ? $enrollment->student->user_id : null,
                'student_id' => $enrollment->student_id,
                'course_id' => $course->id,
                'enrollment_id' => $enrollment->id,
                'account_id' => $account->id,
                'account_receivable_id' => $receivable ? $receivable->id : null,
                'amount' => (float) $validated['amount'],
                'currency' => strtoupper($account->currency ?? 'USD'),
                'type' => 'income',
                'status' => 'completed',
                'payment_method' => $account->name,
                'reference' => $validated['reference'] ?? null,
                'description' => $validated['description'] ?? ('Pago registrado para la clase ' . $course->title),
                'payment_receipt_path' => $receiptPath,
                'payment_receipt_original_name' => $receiptOriginalName,
                'created_at' => $validated['payment_date'],
                'updated_at' => $validated['payment_date'],
            ]);

            $enrollment->syncReceivable();
        });

        return redirect()->back()->with('success', 'Pago registrado correctamente para la clase ' . $course->title . '.');
    }

    protected function applyEnrollmentState(
        Enrollment $enrollment,
        ?string $status,
        ?string $paymentStatus,
        ?float $paymentAmount = null,
        ?string $reference = null,
        ?int $accountId = null
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
            $this->syncEnrollmentIncomeTransaction($enrollment, $paymentAmount, $reference, $accountId);
        }
    }

    protected function syncEnrollmentIncomeTransaction(
        Enrollment $enrollment,
        ?float $paymentAmount = null,
        ?string $reference = null,
        ?int $accountId = null
    ): void {
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

        // If a specific payment amount was passed, use that.
        // If not, but the transaction already exists, preserve its current amount.
        // Otherwise, calculate the default initial charge amount.
        $amount = $paymentAmount;

        if ($amount === null) {
            if ($incomeTransaction) {
                $amount = (float) $incomeTransaction->amount;
            } else {
                $amount = $this->calculateInitialChargeAmount($enrollment->program, $enrollment->courses, $enrollment);
            }
        }

        $account = $accountId ? Account::find($accountId) : null;
        $resolvedAccountId = $account ? (int) $account->id : $this->resolveIncomeAccountId();

        $payload = [
            'enrollment_id' => $enrollment->id,
            'student_id' => $enrollment->student_id,
            'course_id' => $courseId,
            'branch_id' => $branchId,
            'account_id' => $resolvedAccountId,
            'account_receivable_id' => $receivable?->id,
            'amount' => $amount,
            'currency' => 'USD',
            'type' => 'income',
            'status' => 'completed',
            'payment_method' => $account ? $account->name : ($enrollment->payment_method ?: 'manual'),
            'reference' => $reference ?: 'admin-enrollment-'.$enrollment->id,
            'description' => 'Pago confirmado de Inscripción + 1er mes: '.$courseTitles,
            'payment_receipt_path' => $enrollment->payment_receipt_path,
            'payment_receipt_original_name' => $enrollment->payment_receipt_original_name,
        ];

        $existingCompletedCount = Transaction::where('enrollment_id', $enrollment->id)
            ->where('type', 'income')
            ->where('status', 'completed')
            ->count();

        if ($incomeTransaction && ($existingCompletedCount === 0 || $paymentAmount === null)) {
            $incomeTransaction->update($payload);
        } else {
            Transaction::create($payload);
        }

        if ($receivable) {
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
    }

    protected function syncEnrollmentReceivableState(Enrollment $enrollment): void
    {
        $enrollment->loadMissing(['program', 'courses']);

        // Handle cancelled enrollment (student removal)
        if ($enrollment->status === 'cancelled') {
            $receivable = AccountReceivable::query()
                ->where('enrollment_id', $enrollment->id)
                ->first();

            if ($receivable) {
                Transaction::query()
                    ->where('enrollment_id', $enrollment->id)
                    ->where('type', 'income')
                    ->whereNull('account_receivable_id')
                    ->update(['account_receivable_id' => $receivable->id]);

                $paidAmount = (float) $receivable->transactions()->where('status', 'completed')->sum('amount');
                if ($paidAmount <= 0) {
                    $paidAmount = (float) $enrollment->transactions()->where('status', 'completed')->where('type', 'income')->sum('amount');
                }
                if ($paidAmount <= 0) {
                    $receivable->delete();
                } else {
                    $receivable->update([
                        'amount_total' => $paidAmount,
                        'balance_due' => 0.0,
                        'status' => 'paid',
                    ]);
                }
            }

            // Cancel/delete pending installments
            $enrollment->installments()->where('status', 'pending')->delete();

            return;
        }

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

        $receivable = AccountReceivable::query()
            ->where('enrollment_id', $enrollment->id)
            ->first();

        if ($receivable && $receivable->is_custom_amount) {
            $amountTotal = (float) $receivable->amount_total;
        } else {
            $amountTotal = $this->calculateEnrollmentReceivableTotal($enrollment->program, $enrollment->courses, $enrollment);
        }

        $courseTitles = $enrollment->courses->pluck('title')->join(', ');
        $programName = $enrollment->program->name;
        $title = 'Inscripción + mensualidades #'.$enrollment->id.' - '.$programName.' ('.$courseTitles.')';

        if ($enrollment->payment_status === 'pending') {
            if (! $receivable) {
                $receivable = AccountReceivable::create([
                    'branch_id' => $firstCourse->branch_id,
                    'enrollment_id' => $enrollment->id,
                    'title' => $title,
                    'amount_total' => $amountTotal,
                    'balance_due' => $amountTotal,
                    'currency' => 'USD',
                    'status' => 'pending',
                ]);
            } else {
                $updateData = [
                    'branch_id' => $firstCourse->branch_id,
                    'currency' => 'USD',
                    'status' => in_array($receivable->status, ['partial', 'paid'], true)
                        ? $receivable->status
                        : 'pending',
                ];
                if (! $receivable->is_custom_amount) {
                    $updateData['title'] = $title;
                    $updateData['amount_total'] = $amountTotal;
                }
                $receivable->update($updateData);
            }

            $this->syncInstallmentsPaymentStatus($receivable);
            return;
        }

        if (! $receivable) {
            return;
        }

        $paidAmount = (float) $receivable->transactions()->where('status', 'completed')->sum('amount');
        $balance = max(0, (float) $amountTotal - $paidAmount);

        $status = 'pending';
        if ($balance <= 0) {
            $status = 'paid';
        } elseif ($paidAmount > 0) {
            $status = 'partial';
        }

        $updateData = [
            'balance_due' => $balance,
            'status' => $status,
        ];
        if (! $receivable->is_custom_amount) {
            $updateData['amount_total'] = $amountTotal;
        }

        $receivable->update($updateData);

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
        $enrollmentFee = $enrollment->getEnrollmentFee();
        
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
                ->where('status', '!=', 'cancelled')
                ->whereHas('courses', function ($query) use ($course) {
                    $query->where('courses.id', $course->id);
                })
                ->first();

            if ($existingEnrollment && ! $existingEnrollment->is_free_trial) {
                throw ValidationException::withMessages([
                    'course_ids' => 'Este estudiante ya está inscrito en el curso "' . $course->title . '".',
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

    protected function hasPaidProgramEnrollmentFee(int $studentId, int $programId, ?int $excludeEnrollmentId = null): bool
    {
        $query = Enrollment::where('student_id', $studentId)
            ->where('program_id', $programId)
            ->where('status', '!=', 'cancelled')
            ->where('is_free_trial', false);

        if ($excludeEnrollmentId) {
            $query->where('id', '!=', $excludeEnrollmentId);
        }

        return $query->exists();
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
        $enrollmentFee = $enrollment 
            ? $enrollment->getEnrollmentFee() 
            : (float) ($program->enrollment_fee ?? 50.00);

        $total = $enrollmentFee;

        foreach ($courses as $course) {
            $total += (float) ($course->monthly_fee ?? 0);
        }

        return $total;
    }

    protected function calculateEnrollmentReceivableTotal(Program $program, $courses, ?Enrollment $enrollment = null): float
    {
        $enrollmentFee = $enrollment 
            ? $enrollment->getEnrollmentFee() 
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
