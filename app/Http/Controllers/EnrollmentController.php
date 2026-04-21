<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\{Account, AccountReceivable, Course, Enrollment, Student, Transaction, User};
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
        $enrollments = Enrollment::with(['student.user', 'course'])->orderBy('id', 'desc')->get();
        $students = Student::with('user')->orderBy('name')->get();
        $courses = Course::query()
            ->withCount('enrollments')
            ->where('active', true)
            ->whereDate('end_date', '>=', now()->toDateString())
            ->orderBy('title')
            ->get();
        $parents = User::where('role', 'Padre')->orderBy('name')->get();

        return view('enrollments.index', [
            'enrollments' => $enrollments,
            'students' => $students,
            'parents' => $parents,
            'courses' => $courses,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'payment_status' => ['required', Rule::in(['pending', 'paid'])],
            'user.name' => ['nullable', 'string', 'max:255'],
            'user.email' => ['nullable', 'email', 'max:255'],
            'user.whatsapp' => ['nullable', 'string', 'max:30'],
            'user.password' => ['nullable', 'string', 'min:8'],
            'student.name' => ['nullable', 'string', 'max:255'],
            'student.birthdate' => ['nullable', 'date', 'before_or_equal:today'],
            'student.medical_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request): void {
            $parent = $this->resolveParent($request);
            $student = $this->resolveStudent($request, $parent);
            $course = $this->validateCourseForStudent($request, $student);

            $paymentStatus = $request->string('payment_status')->toString();
            $enrollment = Enrollment::create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'parent_id' => $parent->id,
                'status' => $paymentStatus === 'paid' ? 'completed' : 'pending',
                'payment_method' => 'manual',
                'payment_status' => $paymentStatus,
            ]);

            if ($enrollment->payment_status === 'paid') {
                Transaction::create([
                    'enrollment_id' => $enrollment->id,
                    'student_id' => $student->id,
                    'course_id' => $course->id,
                    'branch_id' => $course->branch_id,
                    'account_id' => $this->resolveIncomeAccountId(),
                    'amount' => $course->price ?? 0,
                    'currency' => 'USD',
                    'type' => 'income',
                    'status' => 'completed',
                    'payment_method' => 'manual',
                    'reference' => 'admin-enrollment-'.$enrollment->id,
                    'description' => 'Pago de inscripcion (admin): '.$course->title,
                ]);
            }
        });

        return redirect()->to('enrollment')->with('success', 'Inscripcion registrada correctamente.');
    }

    public function show(Request $request, Enrollment $enrollment)
    {
        $enrollment->loadMissing([
            'student.user',
            'course.branch',
            'course.classes' => function ($query) {
                $query->with('coach')->orderBy('date')->orderBy('start_time');
            },
        ]);

        if ($enrollment->course) {
            $enrollment->course->loadCount('enrollments');
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
            'course.branch',
            'course.classes' => function ($query) {
                $query->with('coach')->orderBy('date')->orderBy('start_time');
            },
        ]);

        if ($enrollment->course) {
            $enrollment->course->loadCount('enrollments');
        }

        $pdf = Pdf::loadView('enrollments.receipt-pdf', [
            'enrollment' => $enrollment,
            'generatedAt' => now(),
        ])->setPaper('a4');

        return $pdf->download('comprobante-inscripcion-'.$enrollment->id.'.pdf');
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

        $enrollment->refresh()->load(['student.user', 'course']);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Inscripcion actualizada correctamente.',
                'enrollment' => $this->enrollmentPayload($enrollment),
            ]);
        }

        return redirect()->route('enrollment.show', $enrollment)->with('success', 'Inscripcion actualizada correctamente.');
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
            $enrollments = Enrollment::with(['student.user', 'course'])
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

        $updatedEnrollments = Enrollment::with(['student.user', 'course'])
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

        return redirect()->to('enrollment')->with('success', 'Estado de inscripcion actualizado.');
    }

    protected function applyEnrollmentState(
        Enrollment $enrollment,
        ?string $status,
        ?string $paymentStatus
    ): void {
        $enrollment->loadMissing('course');
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

        $enrollment->load(['student.user', 'course']);

        if ($previousPaymentStatus === 'paid' && $enrollment->payment_status === 'pending') {
            Transaction::query()
                ->where('enrollment_id', $enrollment->id)
                ->where('type', 'income')
                ->update(['account_receivable_id' => null]);
        }

        $this->syncEnrollmentReceivableState($enrollment);

        if ($enrollment->payment_status === 'paid') {
            $this->syncEnrollmentIncomeTransaction($enrollment);
        }
    }

    protected function syncEnrollmentIncomeTransaction(Enrollment $enrollment): void
    {
        if (! $enrollment->student || ! $enrollment->course) {
            return;
        }

        $receivable = AccountReceivable::query()
            ->where('enrollment_id', $enrollment->id)
            ->first();

        $incomeTransaction = Transaction::where('enrollment_id', $enrollment->id)
            ->where('type', 'income')
            ->first();

        $payload = [
            'enrollment_id' => $enrollment->id,
            'student_id' => $enrollment->student_id,
            'course_id' => $enrollment->course_id,
            'branch_id' => $enrollment->course->branch_id,
            'account_id' => $this->resolveIncomeAccountId(),
            'account_receivable_id' => $receivable?->id,
            'amount' => $enrollment->course->price ?? 0,
            'currency' => 'USD',
            'type' => 'income',
            'status' => 'completed',
            'payment_method' => $enrollment->payment_method ?: 'manual',
            'reference' => 'admin-enrollment-'.$enrollment->id,
            'description' => 'Pago confirmado de inscripcion: '.$enrollment->course->title,
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
        if (! $enrollment->course || $enrollment->course->price === null || $enrollment->course->branch_id === null) {
            return;
        }

        $receivable = AccountReceivable::query()
            ->where('enrollment_id', $enrollment->id)
            ->first();

        if ($enrollment->payment_status === 'pending') {
            if (! $receivable) {
                AccountReceivable::create([
                    'branch_id' => $enrollment->course->branch_id,
                    'enrollment_id' => $enrollment->id,
                    'title' => 'Inscripcion #'.$enrollment->id.' - '.($enrollment->course->title ?? 'Curso'),
                    'amount_total' => $enrollment->course->price,
                    'balance_due' => $enrollment->course->price,
                    'currency' => 'USD',
                    'status' => 'pending',
                ]);

                return;
            }

            $receivable->update([
                'branch_id' => $enrollment->course->branch_id,
                'title' => 'Inscripcion #'.$enrollment->id.' - '.($enrollment->course->title ?? 'Curso'),
                'amount_total' => $enrollment->course->price,
                'balance_due' => $enrollment->course->price,
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
        $course = $enrollment->course;
        $student = $enrollment->student;
        $parent = optional($student)->user;
        $classes = ($course && $course->relationLoaded('classes')) ? $course->classes : collect();

        return [
            'id' => (int) $enrollment->id,
            'status' => (string) $enrollment->status,
            'payment_status' => (string) $enrollment->payment_status,
            'course_id' => (int) $enrollment->course_id,
            'course_title' => optional($course)->title,
            'course_description' => optional($course)->description,
            'course_branch_name' => optional(optional($course)->branch)->name,
            'course_start_date' => optional($course)->start_date,
            'course_end_date' => optional($course)->end_date,
            'course_price' => optional($course)->price,
            'course_capacity' => optional($course)->capacity,
            'course_enrollments_count' => optional($course)->enrollments_count,
            'classes' => $classes->map(function ($class) {
                return [
                    'id' => (int) $class->id,
                    'date' => $class->date,
                    'start_time' => $class->start_time,
                    'end_time' => $class->end_time,
                    'coach_name' => optional($class->coach)->name,
                ];
            })->values(),
            'student_name' => optional($student)->name,
            'student_birthdate' => optional($student)->birthdate,
            'student_medical_notes' => optional($student)->medical_notes,
            'parent_name' => optional($parent)->name,
            'parent_email' => optional($parent)->email,
            'parent_whatsapp' => optional($parent)->whatsapp,
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
            'user.whatsapp' => ['nullable', 'string', 'max:30'],
        ]);

        return User::create([
            'name' => $request->input('user.name'),
            'email' => $request->input('user.email'),
            'password' => Hash::make($request->input('user.password')),
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

    protected function validateCourseForStudent(Request $request, Student $student): Course
    {
        $course = Course::query()
            ->withCount('enrollments')
            ->where('active', true)
            ->whereDate('end_date', '>=', now()->toDateString())
            ->findOrFail((int) $request->input('course_id'));

        if (Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->exists()) {
            throw ValidationException::withMessages([
                'student_id' => 'Este estudiante ya esta inscrito en este curso.',
            ]);
        }

        if (((int) $course->capacity - (int) $course->enrollments_count) <= 0) {
            throw ValidationException::withMessages([
                'course_id' => 'El curso seleccionado no tiene cupos disponibles.',
            ]);
        }

        if ($student->birthdate) {
            $age = Carbon::parse($student->birthdate)->age;
            if ($course->min_age && $age < (int) $course->min_age) {
                throw ValidationException::withMessages([
                    'course_id' => 'El estudiante no cumple con la edad minima del curso.',
                ]);
            }
            if ($course->max_age && $age > (int) $course->max_age) {
                throw ValidationException::withMessages([
                    'course_id' => 'El estudiante supera la edad maxima permitida para el curso.',
                ]);
            }
        }

        return $course;
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
}
