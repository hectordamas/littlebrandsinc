<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountPayable;
use App\Models\AccountReceivable;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FinancialMovementsSeeder extends Seeder
{
    private const RECEIPTS = [
        ['path' => 'uploads/test_recibo/receipt1.pdf', 'name' => 'receipt1.pdf'],
        ['path' => 'uploads/test_recibo/receipt2.JPG', 'name' => 'receipt2.JPG'],
    ];

    public function run(): void
    {
        $this->warnIfMissingTestReceipts();

        $branches = Branch::query()->orderBy('id')->get();
        if ($branches->isEmpty()) {
            $this->command?->warn('FinancialMovementsSeeder: no hay sedes para sembrar movimientos.');
            return;
        }

        $accounts = Account::query()
            ->where('active', true)
            ->whereIn('slug', ['caja-chica-usd', 'banco-principal-usd', 'zelle-usd'])
            ->orderBy('id')
            ->get();

        if ($accounts->isEmpty()) {
            $this->command?->warn('FinancialMovementsSeeder: no hay cuentas financieras activas.');
            return;
        }

        DB::transaction(function () use ($branches, $accounts): void {
            $this->cleanupPreviousSeedData();

            $manualCount = 8;
            for ($i = 0; $i < $manualCount; $i++) {
                $branch = $branches[$i % $branches->count()];
                $account = $accounts[$i % $accounts->count()];
                $receipt = self::RECEIPTS[$i % count(self::RECEIPTS)];

                Transaction::query()->create([
                    'branch_id' => $branch->id,
                    'account_id' => $account->id,
                    'amount' => $this->manualAmount($i),
                    'currency' => strtoupper((string) $account->currency),
                    'type' => $i % 2 === 0 ? 'income' : 'expense',
                    'status' => $this->manualStatus($i),
                    'payment_method' => $account->name,
                    'reference' => 'SEED-RECIBO-MANUAL-'.str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT),
                    'description' => 'Movimiento semilla para pruebas del modal de comprobantes',
                    'payment_receipt_path' => $receipt['path'],
                    'payment_receipt_original_name' => $receipt['name'],
                    'created_at' => now()->subDays(18 - $i),
                    'updated_at' => now()->subDays(18 - $i),
                ]);
            }

            $receivableCount = 4;
            for ($i = 0; $i < $receivableCount; $i++) {
                $branch = $branches[$i % $branches->count()];
                $account = $accounts[$i % $accounts->count()];
                $receipt = self::RECEIPTS[$i % count(self::RECEIPTS)];
                $enrollment = $this->ensureEnrollmentForBranch($branch->id);

                if (! $enrollment) {
                    continue;
                }

                $receivable = AccountReceivable::query()->create([
                    'branch_id' => $branch->id,
                    'enrollment_id' => null,
                    'title' => 'Cobranza seed #'.($i + 1),
                    'amount_total' => 220,
                    'balance_due' => 220,
                    'currency' => 'USD',
                    'status' => 'pending',
                    'due_date' => now()->addDays(10 + $i)->toDateString(),
                    'notes' => 'SEED-RECIBO-TEST',
                ]);

                $amount = 70 + ($i * 10);
                Transaction::query()->create([
                    'enrollment_id' => $enrollment->id,
                    'student_id' => $enrollment->student_id,
                    'course_id' => $enrollment->course_id,
                    'branch_id' => $branch->id,
                    'account_id' => $account->id,
                    'account_receivable_id' => $receivable->id,
                    'amount' => $amount,
                    'currency' => strtoupper((string) $account->currency),
                    'type' => 'income',
                    'status' => 'completed',
                    'payment_method' => $account->name,
                    'reference' => 'SEED-RECIBO-RECEIVABLE-'.str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT),
                    'description' => 'Abono semilla para pruebas de cobranzas',
                    'payment_receipt_path' => $receipt['path'],
                    'payment_receipt_original_name' => $receipt['name'],
                    'created_at' => now()->subDays(8 - $i),
                    'updated_at' => now()->subDays(8 - $i),
                ]);

                $this->refreshReceivable($receivable->fresh());
            }

            $payableCount = 4;
            for ($i = 0; $i < $payableCount; $i++) {
                $branch = $branches[$i % $branches->count()];
                $account = $accounts[$i % $accounts->count()];
                $receipt = self::RECEIPTS[$i % count(self::RECEIPTS)];

                $payable = AccountPayable::query()->create([
                    'branch_id' => $branch->id,
                    'vendor_name' => 'Proveedor Seed '.($i + 1),
                    'title' => 'Servicio operativo #'.($i + 1),
                    'amount_total' => 300,
                    'balance_due' => 300,
                    'currency' => 'USD',
                    'status' => 'pending',
                    'due_date' => now()->addDays(5 + $i)->toDateString(),
                    'notes' => 'SEED-RECIBO-TEST',
                ]);

                $amount = 90 + ($i * 15);
                Transaction::query()->create([
                    'branch_id' => $branch->id,
                    'account_id' => $account->id,
                    'account_payable_id' => $payable->id,
                    'amount' => $amount,
                    'currency' => strtoupper((string) $account->currency),
                    'type' => 'expense',
                    'status' => 'completed',
                    'payment_method' => $account->name,
                    'reference' => 'SEED-RECIBO-PAYABLE-'.str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT),
                    'description' => 'Pago semilla para pruebas de cuentas por pagar',
                    'payment_receipt_path' => $receipt['path'],
                    'payment_receipt_original_name' => $receipt['name'],
                    'created_at' => now()->subDays(4 - $i),
                    'updated_at' => now()->subDays(4 - $i),
                ]);

                $this->refreshPayable($payable->fresh());
            }
        });

        $this->command?->info('FinancialMovementsSeeder completado con 16 movimientos de prueba.');
    }

    private function warnIfMissingTestReceipts(): void
    {
        foreach (self::RECEIPTS as $receipt) {
            if (! is_file(public_path($receipt['path']))) {
                $this->command?->warn('No se encontro archivo de prueba: public/'.$receipt['path']);
            }
        }
    }

    private function cleanupPreviousSeedData(): void
    {
        Transaction::query()->where('reference', 'like', 'SEED-RECIBO-%')->delete();
        AccountReceivable::query()->where('notes', 'SEED-RECIBO-TEST')->delete();
        AccountPayable::query()->where('notes', 'SEED-RECIBO-TEST')->delete();
    }

    private function ensureEnrollmentForBranch(int $branchId): ?Enrollment
    {
        $course = Course::query()->where('branch_id', $branchId)->orderBy('id')->first();
        if (! $course) {
            return null;
        }

        $parent = User::query()->where('role', 'Padre')->orderBy('id')->first();
        if (! $parent) {
            $parent = User::query()->create([
                'name' => 'Padre Seeder',
                'email' => 'padre.seeder@example.com',
                'password' => bcrypt('password'),
                'role' => 'Padre',
                'dial_code' => '+58',
                'whatsapp' => '4240000000',
            ]);
        }

        $student = Student::query()->where('user_id', $parent->id)->orderBy('id')->first();
        if (! $student) {
            $student = Student::query()->create([
                'user_id' => $parent->id,
                'name' => 'Estudiante Seeder',
                'birthdate' => now()->subYears(8)->toDateString(),
                'level' => 'intermedio',
                'medical_notes' => null,
            ]);
        }

        return Enrollment::query()->firstOrCreate(
            [
                'student_id' => $student->id,
                'course_id' => $course->id,
            ],
            [
                'parent_id' => $parent->id,
                'status' => 'active',
                'payment_method' => 'transfer',
                'payment_status' => 'pending',
                'is_free_trial' => false,
                'terms_accepted' => true,
                'image_consent_accepted' => true,
            ]
        );
    }

    private function refreshReceivable(AccountReceivable $receivable): void
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

    private function refreshPayable(AccountPayable $payable): void
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

    private function manualAmount(int $index): float
    {
        return 45 + ($index * 17.5);
    }

    private function manualStatus(int $index): string
    {
        if ($index % 5 === 0) {
            return 'pending';
        }

        if ($index % 7 === 0) {
            return 'failed';
        }

        return 'completed';
    }
}
