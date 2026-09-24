<?php

namespace App\Console\Commands;

use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncEnrollmentStatusCommand extends Command
{
    protected $signature = 'enrollments:sync-statuses
                            {--student= : ID de un estudiante específico (opcional)}
                            {--enrollment= : ID de una inscripción específica (opcional)}
                            {--dry-run : Ejecuta una simulación sin modificar la base de datos}';

    protected $description = 'Sincroniza y repara retroactivamente el estado individual de cursos, estado de inscripción y cuentas por cobrar (CxC).';

    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');
        $studentId = $this->option('student');
        $enrollmentId = $this->option('enrollment');

        $this->info($isDryRun 
            ? '🔍 MODO SIMULACIÓN (DRY-RUN): No se aplicarán cambios reales en la base de datos.' 
            : '🚀 SINCRONIZANDO ESTADOS DE INSCRIPCIONES Y CUENTAS POR COBRAR...'
        );

        $query = Enrollment::query()
            ->with(['student', 'courses', 'transactions', 'receivable', 'program']);

        if ($studentId) {
            $query->where('student_id', $studentId);
        }

        if ($enrollmentId) {
            $query->where('id', $enrollmentId);
        }

        $enrollments = $query->get();

        if ($enrollments->isEmpty()) {
            $this->warn('No se encontraron inscripciones para procesar.');
            return self::SUCCESS;
        }

        $reportRows = [];
        $totalProcessed = 0;

        foreach ($enrollments as $enrollment) {
            $studentName = $enrollment->student?->name ?? 'N/A';
            $courses = $enrollment->courses;

            if ($courses->isEmpty()) {
                continue;
            }

            $activeCourses = $courses->filter(function ($c) {
                return ($c->pivot->status ?? 'active') !== 'cancelled';
            });

            $oldStatus = $enrollment->status;
            $oldPaymentStatus = $enrollment->payment_status;

            // Determinar estado de inscripción
            $newStatus = $oldStatus;
            if ($activeCourses->isEmpty()) {
                $newStatus = 'cancelled';
            } elseif ($oldStatus === 'cancelled' && $activeCourses->isNotEmpty()) {
                $newStatus = 'completed';
            }

            // Determinar payment_status
            $totalPaid = (float) $enrollment->transactions
                ->where('status', 'completed')
                ->where('type', 'income')
                ->sum('amount');

            $newPaymentStatus = $oldPaymentStatus;
            if ($enrollment->is_free_trial) {
                $newPaymentStatus = 'paid';
            } elseif ($totalPaid > 0 || ($enrollment->receivable && (float) $enrollment->receivable->balance_due <= 0.00)) {
                $newPaymentStatus = 'paid';
            }

            $receivable = $enrollment->receivable;
            $oldBalance = $receivable ? (float) $receivable->balance_due : 0.0;

            if (!$isDryRun) {
                DB::transaction(function () use ($enrollment, $newStatus, $newPaymentStatus) {
                    $enrollment->status = $newStatus;
                    $enrollment->payment_status = $newPaymentStatus;
                    $enrollment->save();
                    $enrollment->syncReceivable();
                });

                $enrollment->refresh();
                $updatedReceivable = $enrollment->receivable;
                $newBalance = $updatedReceivable ? (float) $updatedReceivable->balance_due : 0.0;
            } else {
                $breakdown = $enrollment->getCourseBreakdown();
                $newBalance = 0.0;
                foreach ($breakdown as $b) {
                    if (!$b['is_cancelled']) {
                        $newBalance += (float) $b['balance'];
                    }
                }
            }

            $totalProcessed++;
            $activeCoursesList = $activeCourses->pluck('title')->join(', ') ?: 'Ninguno';

            $reportRows[] = [
                $enrollment->id,
                substr($studentName, 0, 22),
                substr($activeCoursesList, 0, 30),
                $oldStatus . ' ➔ ' . $newStatus,
                $oldPaymentStatus . ' ➔ ' . $newPaymentStatus,
                '$' . number_format($oldBalance, 2) . ' ➔ $' . number_format($newBalance, 2),
            ];
        }

        $this->table(
            ['ID Insc.', 'Estudiante', 'Cursos Activos', 'Estado Inscripción', 'Estado Pago', 'Saldo CxC'],
            $reportRows
        );

        if ($isDryRun) {
            $this->info("Simulación finalizada. {$totalProcessed} inscripción(es) analizada(s).");
            $this->comment('Para aplicar los cambios reales, ejecute el comando sin la opción --dry-run:');
            $this->comment('php artisan enrollments:sync-statuses');
        } else {
            $this->info("✅ Sincronización completada exitosamente para {$totalProcessed} inscripción(es).");
        }

        return self::SUCCESS;
    }
}
