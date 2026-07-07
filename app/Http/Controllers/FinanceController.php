<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\{Account, AccountPayable, AccountReceivable, Branch, Enrollment, ParentPayment, Program, Transaction};
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ])->validate();

        $branchId = isset($validated['branch_id']) ? (int) $validated['branch_id'] : null;

        $accounts = Account::query()
            ->withCount('transactions')
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $branches = Branch::query()
            ->orderBy('name')
            ->get();

        $this->syncEnrollmentReceivables();

        $summary = $this->buildSummary($branchId);

        if ($request->ajax()) {
            $transactions = $this->transactionsQuery($branchId)->get();

            return response()->json([
                'branch_id' => $branchId,
                'summary' => $summary,
                'transactions' => $this->serializeTransactions($transactions),
            ]);
        }

        return view('finance.index', [
            'accounts' => $accounts,
            'branches' => $branches,
            'selectedBranchId' => $branchId,
            'completedIncome' => $summary['completedIncome'],
            'completedExpenses' => $summary['completedExpenses'],
            'pendingCollectionAmount' => $summary['pendingCollectionAmount'],
            'netBalance' => $summary['netBalance'],
            'pendingCollectionsCount' => $summary['pendingCollectionsCount'],
        ]);
    }

    public function collections()
    {
        $this->syncEnrollmentReceivables();

        $receivables = AccountReceivable::query()
            ->with(['branch', 'enrollment.courses', 'enrollment.student', 'enrollment.program'])
            ->whereIn('status', ['pending', 'partial'])
            ->orderByDesc('id')
            ->get();

        $branches = Branch::query()->orderBy('name')->get();

        $accounts = Account::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return view('finance.collections', [
            'receivables' => $receivables,
            'branches' => $branches,
            'accounts' => $accounts,
            'pendingCollectionAmount' => (float) $receivables->sum('balance_due'),
        ]);
    }

    public function storeCollection(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'title' => ['required', 'string', 'max:255'],
            'amount_total' => ['required', 'numeric', 'gt:0'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        AccountReceivable::create([
            'branch_id' => (int) $validated['branch_id'],
            'enrollment_id' => null,
            'title' => $validated['title'],
            'amount_total' => $validated['amount_total'],
            'balance_due' => $validated['amount_total'],
            'currency' => 'USD',
            'status' => 'pending',
            'due_date' => $validated['due_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('finance.collections')->with('success', 'Cuenta por cobrar creada correctamente.');
    }

    public function showCollection(AccountReceivable $receivable)
    {
        $receivable->load([
            'branch',
            'enrollment.courses',
            'enrollment.student',
            'enrollment.program',
            'transactions.account',
        ]);

        $accounts = Account::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return view('finance.collection-show', [
            'receivable' => $receivable,
            'accounts' => $accounts,
        ]);
    }

    public function storeCollectionPayment(Request $request, AccountReceivable $receivable): RedirectResponse
    {
        $validated = $request->validate([
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'payment_date' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'payment_receipt' => ['bail', 'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:6144'],
        ]);

        if ((float) $validated['amount'] > (float) $receivable->balance_due) {
            return back()->withErrors([
                'amount' => 'El abono no puede superar el saldo pendiente.',
            ]);
        }

        $account = Account::query()->findOrFail($validated['account_id']);

        $receiptPath = null;
        $receiptOriginalName = null;
        if ($request->hasFile('payment_receipt')) {
            $destinationPath = public_path('uploads/comprobantes');

            $file = $request->file('payment_receipt');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $filename);
            $receiptPath = 'uploads/comprobantes/'.$filename;
            $receiptOriginalName = $file->getClientOriginalName();
        }

        DB::transaction(function () use ($receivable, $validated, $account, $receiptPath, $receiptOriginalName) {
            Transaction::create([
                'enrollment_id' => $receivable->enrollment_id,
                'student_id' => optional($receivable->enrollment)->student_id,
                'course_id' => optional($receivable->enrollment)->course_id,
                'branch_id' => $receivable->branch_id,
                'account_id' => $account->id,
                'account_receivable_id' => $receivable->id,
                'amount' => $validated['amount'],
                'currency' => strtoupper($account->currency),
                'type' => 'income',
                'status' => 'completed',
                'payment_method' => $account->name,
                'reference' => $validated['reference'] ?? null,
                'description' => $validated['notes'] ?? 'Abono de cuenta por cobrar #'.$receivable->id,
                'payment_receipt_path' => $receiptPath,
                'payment_receipt_original_name' => $receiptOriginalName,
                'created_at' => $validated['payment_date'],
                'updated_at' => $validated['payment_date'],
            ]);

            $this->refreshReceivableBalance($receivable->fresh());
        });

        return redirect()->route('finance.collections.show', $receivable)->with('success', 'Abono registrado correctamente.');
    }

    public function payables()
    {
        $payables = AccountPayable::query()
            ->with(['branch'])
            ->orderByDesc('id')
            ->get();

        $branches = Branch::query()->orderBy('name')->get();

        return view('finance.payables', [
            'payables' => $payables,
            'branches' => $branches,
            'pendingPayableAmount' => (float) $payables->whereIn('status', ['pending', 'partial'])->sum('balance_due'),
        ]);
    }

    public function storePayable(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'vendor_name' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'amount_total' => ['required', 'numeric', 'gt:0'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        AccountPayable::create([
            'branch_id' => (int) $validated['branch_id'],
            'vendor_name' => $validated['vendor_name'],
            'title' => $validated['title'],
            'amount_total' => $validated['amount_total'],
            'balance_due' => $validated['amount_total'],
            'currency' => 'USD',
            'status' => 'pending',
            'due_date' => $validated['due_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('finance.payables')->with('success', 'Cuenta por pagar creada correctamente.');
    }

    public function showPayable(AccountPayable $payable)
    {
        $payable->load([
            'branch',
            'transactions.account',
        ]);

        $accounts = Account::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return view('finance.payable-show', [
            'payable' => $payable,
            'accounts' => $accounts,
        ]);
    }

    public function storePayablePayment(Request $request, AccountPayable $payable): RedirectResponse
    {
        $validated = $request->validate([
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'payment_date' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'payment_receipt' => ['bail', 'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:6144'],
        ]);

        if ((float) $validated['amount'] > (float) $payable->balance_due) {
            return back()->withErrors([
                'amount' => 'El abono no puede superar el saldo pendiente.',
            ]);
        }

        $account = Account::query()->findOrFail($validated['account_id']);

        $receiptPath = null;
        $receiptOriginalName = null;
        if ($request->hasFile('payment_receipt')) {
            $destinationPath = public_path('uploads/comprobantes');

            $file = $request->file('payment_receipt');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $filename);
            $receiptPath = 'uploads/comprobantes/'.$filename;
            $receiptOriginalName = $file->getClientOriginalName();
        }

        DB::transaction(function () use ($payable, $validated, $account, $receiptPath, $receiptOriginalName) {
            Transaction::create([
                'branch_id' => $payable->branch_id,
                'account_id' => $account->id,
                'account_payable_id' => $payable->id,
                'amount' => $validated['amount'],
                'currency' => strtoupper($account->currency),
                'type' => 'expense',
                'status' => 'completed',
                'payment_method' => $account->name,
                'reference' => $validated['reference'] ?? null,
                'description' => $validated['notes'] ?? 'Pago de cuenta por pagar #'.$payable->id,
                'payment_receipt_path' => $receiptPath,
                'payment_receipt_original_name' => $receiptOriginalName,
                'created_at' => $validated['payment_date'],
                'updated_at' => $validated['payment_date'],
            ]);

            $this->refreshPayableBalance($payable->fresh());
        });

        return redirect()->route('finance.payables.show', $payable)->with('success', 'Abono registrado correctamente.');
    }

    public function storeTransaction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'return_branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'type' => ['required', Rule::in(['income', 'expense'])],
            'status' => ['required', Rule::in(['pending', 'completed', 'failed'])],
            'reference' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'payment_receipt' => ['bail', 'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        $account = Account::query()->findOrFail($validated['account_id']);

        $receiptPath = null;
        $receiptOriginalName = null;
        if ($request->hasFile('payment_receipt')) {
            $file = $request->file('payment_receipt');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/comprobantes'), $filename);
            $receiptPath = 'uploads/comprobantes/' . $filename;
            $receiptOriginalName = $file->getClientOriginalName();
        }

        Transaction::create([
            'branch_id' => (int) $validated['branch_id'],
            'account_id' => (int) $validated['account_id'],
            'amount' => $validated['amount'],
            'currency' => strtoupper($account->currency),
            'type' => $validated['type'],
            'status' => $validated['status'],
            'payment_method' => $account->name,
            'reference' => $validated['reference'] ?? null,
            'description' => $validated['description'] ?? null,
            'payment_receipt_path' => $receiptPath,
            'payment_receipt_original_name' => $receiptOriginalName,
        ]);

        return redirect()
            ->route('finance.index', array_filter([
                'branch_id' => $validated['return_branch_id'] ?? null,
            ]))
            ->with('success', 'Movimiento financiero registrado correctamente.');
    }

    public function downloadTransactionReceipt(Transaction $transaction)
    {
        $transaction->loadMissing(['branch', 'account']);

        $pdf = Pdf::loadView('finance.transaction-receipt-pdf', [
            'transaction' => $transaction,
            'generatedAt' => now(),
        ])->setPaper('a4');

        return $pdf->download('comprobante-movimiento-'.$transaction->id.'.pdf');
    }

    protected function transactionsQuery(?int $branchId = null)
    {
        $query = Transaction::with(['account', 'branch', 'enrollment'])
            ->orderBy('created_at', 'desc');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query;
    }

    protected function pendingCollectionsByBranchQuery(?int $branchId = null)
    {
        $query = AccountReceivable::query()
            ->whereIn('status', ['pending', 'partial']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query;
    }

    protected function buildSummary(?int $branchId = null): array
    {
        $completedIncome = (float) Transaction::query()
            ->when($branchId, function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })
            ->where('type', 'income')
            ->where('status', 'completed')
            ->sum('amount');

        $completedExpenses = (float) Transaction::query()
            ->when($branchId, function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })
            ->where('type', 'expense')
            ->where('status', 'completed')
            ->sum('amount');

        $pendingCollectionAmount = (float) $this->pendingCollectionsByBranchQuery($branchId)->sum('balance_due');

        $pendingCollectionsCount = (int) $this->pendingCollectionsByBranchQuery($branchId)->count();

        return [
            'completedIncome' => $completedIncome,
            'completedExpenses' => $completedExpenses,
            'pendingCollectionAmount' => $pendingCollectionAmount,
            'netBalance' => $completedIncome - $completedExpenses,
            'pendingCollectionsCount' => $pendingCollectionsCount,
        ];
    }

    protected function serializeTransactions($transactions): array
    {
        return $transactions->map(function (Transaction $transaction) {
            $receiptPath = $transaction->payment_receipt_path
                ?: optional($transaction->enrollment)->payment_receipt_path;

            $receiptName = $transaction->payment_receipt_original_name
                ?: optional($transaction->enrollment)->payment_receipt_original_name;

            return [
                'id' => $transaction->id,
                'created_at' => $transaction->created_at ? $transaction->created_at->format('d/m/Y h:i A') : 'N/A',
                'type' => $transaction->type,
                'amount' => (float) $transaction->amount,
                'status' => $transaction->status,
                'account' => optional($transaction->account)->name ?? 'N/A',
                'branch' => optional($transaction->branch)->name ?? 'N/A',
                'reference' => $transaction->reference ?? 'N/A',
                'receipt_url' => route('finance.transactions.receipt', $transaction),
                'payment_receipt_url' => $this->resolvePaymentReceiptUrl($receiptPath),
                'payment_receipt_name' => $receiptName,
            ];
        })->all();
    }

    protected function resolvePaymentReceiptUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $normalizedPath = ltrim(str_replace('\\', '/', $path), '/');

        if (preg_match('/^https?:\/\//i', $normalizedPath) === 1) {
            return $normalizedPath;
        }

        if (is_file(public_path($normalizedPath))) {
            return asset($normalizedPath);
        }

        $storagePath = 'storage/'.$normalizedPath;
        if (is_file(public_path($storagePath))) {
            return asset($storagePath);
        }

        return asset($normalizedPath);
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

    protected function refreshPayableBalance(AccountPayable $payable): void
    {
        $paidAmount = (float) $payable->transactions()->sum('amount');
        $balance = max(0, (float) $payable->amount_total - $paidAmount);

        $status = 'pending';
        if ($balance <= 0) {
            $status = 'paid';
        } elseif ($paidAmount > 0) {
            $status = 'partial';
        }

        $payable->update([
            'balance_due' => $balance,
            'status' => $status,
        ]);
    }

    protected function syncEnrollmentReceivables(): void
    {
        $enrollments = Enrollment::with(['program', 'courses', 'student'])
            ->whereNotNull('program_id')
            ->get();

        foreach ($enrollments as $enrollment) {
            if ($enrollment->status === 'cancelled') {
                $receivable = AccountReceivable::query()
                    ->where('enrollment_id', $enrollment->id)
                    ->first();

                if ($receivable) {
                    $paidAmount = (float) $receivable->transactions()->where('status', 'completed')->sum('amount');
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

                $enrollment->installments()->where('status', 'pending')->delete();
                continue;
            }

            if ($enrollment->is_free_trial) {
                AccountReceivable::query()
                    ->where('enrollment_id', $enrollment->id)
                    ->delete();
                continue;
            }

            $program = $enrollment->program;
            $courses = $enrollment->courses;

            if (!$program || $courses->isEmpty()) {
                continue;
            }

            $firstCourse = $courses->first();
            $amountTotal = $this->calculateEnrollmentReceivableTotal($program, $courses, $enrollment);
            $courseTitles = $courses->pluck('title')->join(', ');

            $receivable = AccountReceivable::query()
                ->where('enrollment_id', $enrollment->id)
                ->first();

            if ($enrollment->payment_status === 'pending') {
                $studentName = optional($enrollment->student)->name ?? 'Estudiante';
                $cleanTitle = 'Inscripción #' . $enrollment->id . ' - ' . $studentName . ' (' . ($program->name ?? 'Programa') . ')';

                if (! $receivable) {
                    $receivable = AccountReceivable::create([
                        'branch_id' => $firstCourse->branch_id,
                        'enrollment_id' => $enrollment->id,
                        'title' => $cleanTitle,
                        'amount_total' => $amountTotal,
                        'balance_due' => $amountTotal,
                        'currency' => 'USD',
                        'status' => 'pending',
                    ]);
                } else {
                    $receivable->update([
                        'branch_id' => $firstCourse->branch_id,
                        'title' => $cleanTitle,
                        'amount_total' => $amountTotal,
                        'currency' => 'USD',
                        'status' => in_array($receivable->status, ['partial', 'paid'], true)
                            ? $receivable->status
                            : 'pending',
                    ]);
                }

                $this->refreshReceivableBalance($receivable->fresh());
                continue;
            }

            if (! $receivable) {
                continue;
            }

            $hasLinkedTransactions = $receivable->transactions()->exists();

            if (! $hasLinkedTransactions) {
                Transaction::query()
                    ->where('enrollment_id', $enrollment->id)
                    ->where('type', 'income')
                    ->whereNull('account_receivable_id')
                    ->update(['account_receivable_id' => $receivable->id]);

                $hasLinkedTransactions = $receivable->transactions()->exists();
            }

            if (! $hasLinkedTransactions) {
                $receivable->delete();
                continue;
            }

            $this->refreshReceivableBalance($receivable->fresh());
        }
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
                $start = \Carbon\Carbon::parse($course->start_date)->startOfMonth();
                $end = \Carbon\Carbon::parse($course->end_date)->startOfMonth();
                $months = max(1, $start->diffInMonths($end) + 1);
            }
            $total += (float) ($course->monthly_fee ?? 0) * $months;
        }

        return $total;
    }

    public function parentPayments()
    {
        $payments = ParentPayment::with(['user', 'receivable.enrollment.student'])
            ->orderByDesc('id')
            ->get();

        return view('finance.parent-payments', [
            'payments' => $payments,
        ]);
    }

    public function approveParentPayment(ParentPayment $payment): RedirectResponse
    {
        abort_if($payment->status !== 'pending', 400, 'Este pago ya fue procesado.');

        DB::transaction(function () use ($payment) {
            $payment->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            // Find or create a default account for parent payments
            $defaultAccount = Account::firstOrCreate(
                ['slug' => 'parent-payments'],
                [
                    'name' => 'Pagos de Padres',
                    'type' => 'transfer',
                    'active' => true,
                ]
            );

            Transaction::create([
                'account_receivable_id' => $payment->account_receivable_id,
                'enrollment_id' => $payment->receivable?->enrollment_id,
                'account_id' => $defaultAccount->id,
                'branch_id' => $payment->receivable?->branch_id,
                'amount' => $payment->amount,
                'type' => 'income',
                'status' => 'completed',
                'payment_method' => 'transfer',
                'reference' => $payment->reference,
                'description' => 'Pago de padre aprobado #' . $payment->id,
            ]);

            $receivable = $payment->receivable;
            if ($receivable) {
                $this->refreshReceivableBalance($receivable->fresh());
            }
        });

        try {
            \Mail::to($payment->user->email)->send(new \App\Mail\PaymentApproved($payment));
        } catch (\Exception $e) {
            \Log::warning('No se pudo enviar correo de aprobacion: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Pago aprobado correctamente.');
    }

    public function rejectParentPayment(Request $request, ParentPayment $payment): RedirectResponse
    {
        abort_if($payment->status !== 'pending', 400, 'Este pago ya fue procesado.');

        $validated = $request->validate([
            'rejected_reason' => 'required|string|max:500',
        ]);

        $payment->update([
            'status' => 'rejected',
            'rejected_reason' => $validated['rejected_reason'],
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        try {
            \Mail::to($payment->user->email)->send(new \App\Mail\PaymentRejected($payment));
        } catch (\Exception $e) {
            \Log::warning('No se pudo enviar correo de rechazo: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Pago rechazado.');
    }
}
