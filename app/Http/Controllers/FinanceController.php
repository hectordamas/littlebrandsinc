<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\{Account, AccountPayable, AccountReceivable, Branch, Enrollment, Transaction};
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            ->with(['branch', 'enrollment.course', 'enrollment.student'])
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
            'enrollment.course',
            'enrollment.student',
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
        ]);

        if ((float) $validated['amount'] > (float) $receivable->balance_due) {
            return back()->withErrors([
                'amount' => 'El abono no puede superar el saldo pendiente.',
            ]);
        }

        $account = Account::query()->findOrFail($validated['account_id']);

        DB::transaction(function () use ($receivable, $validated, $account) {
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
        ]);

        if ((float) $validated['amount'] > (float) $payable->balance_due) {
            return back()->withErrors([
                'amount' => 'El abono no puede superar el saldo pendiente.',
            ]);
        }

        $account = Account::query()->findOrFail($validated['account_id']);

        DB::transaction(function () use ($payable, $validated, $account) {
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
        ]);

        $account = Account::query()->findOrFail($validated['account_id']);

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
        $query = Transaction::with(['account', 'branch'])
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
            ];
        })->all();
    }

    protected function refreshReceivableBalance(AccountReceivable $receivable): void
    {
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
        $enrollments = Enrollment::with(['course', 'student'])
            ->whereNotNull('course_id')
            ->get();

        foreach ($enrollments as $enrollment) {
            $course = $enrollment->course;
            if (!$course || $course->price === null || $course->branch_id === null) {
                continue;
            }

            $receivable = AccountReceivable::query()
                ->where('enrollment_id', $enrollment->id)
                ->first();

            if ($enrollment->payment_status === 'pending') {
                if (! $receivable) {
                    $receivable = AccountReceivable::create([
                        'branch_id' => $course->branch_id,
                        'enrollment_id' => $enrollment->id,
                        'title' => 'Inscripcion #'.$enrollment->id.' - '.($course->title ?? 'Curso'),
                        'amount_total' => $course->price,
                        'balance_due' => $course->price,
                        'currency' => 'USD',
                        'status' => 'pending',
                    ]);
                } else {
                    $receivable->update([
                        'branch_id' => $course->branch_id,
                        'title' => 'Inscripcion #'.$enrollment->id.' - '.($course->title ?? 'Curso'),
                        'amount_total' => $course->price,
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
}
