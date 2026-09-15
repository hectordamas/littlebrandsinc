<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\Enrollment;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncCourseBalancesCommand extends Command
{
    protected $signature = 'courses:sync-balances 
                            {--course= : ID de un curso específico (opcional, por defecto procesa todos los cursos)}
                            {--months= : Cantidad de mensualidades a sumar (por defecto 1)}
                            {--add-months= : Alias de --months (por defecto 1)}
                            {--exclude-students= : IDs de estudiantes a excluir separados por comas (ej: --exclude-students=19,25)}
                            {--exclude-enrollments= : IDs de inscripciones a excluir separados por comas (ej: --exclude-enrollments=15,19)}
                            {--exclude-names= : Nombres de estudiantes a excluir separados por comas (ej: --exclude-names="Andres Quintero,Derek Blanco")}
                            {--dry-run : Ejecuta una simulación sin modificar la base de datos}';

    protected $description = 'Sincroniza retroactivamente los montos de cursos extendidos y las cuentas por cobrar (CxC) de los estudiantes, sumando las mensualidades pendientes.';

    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');
        $courseId = $this->option('course');
        
        $rawMonths = $this->option('months') ?: $this->option('add-months');
        $monthsToAdd = max(1, (int) ($rawMonths ?: 1));

        $excludedStudents = collect(explode(',', (string) ($this->option('exclude-students') ?? '')))
            ->map(fn($id) => (int) trim($id))
            ->filter(fn($id) => $id > 0)
            ->all();

        $excludedEnrollments = collect(explode(',', (string) ($this->option('exclude-enrollments') ?? '')))
            ->map(fn($id) => (int) trim($id))
            ->filter(fn($id) => $id > 0)
            ->all();

        $excludedNames = collect(explode(',', (string) ($this->option('exclude-names') ?? '')))
            ->map(fn($name) => mb_strtolower(trim($name)))
            ->filter(fn($name) => !empty($name))
            ->values()
            ->all();

        $this->info($isDryRun 
            ? '🔍 MODO SIMULACIÓN (DRY-RUN): No se aplicarán cambios reales en la base de datos.' 
            : '🚀 APLICANDO CAMBIOS EN BASE DE DATOS...'
        );

        if (!empty($excludedNames)) {
            $this->comment('🚫 Nombres excluidos: ' . implode(', ', $excludedNames));
        }
        if (!empty($excludedStudents)) {
            $this->comment('🚫 Estudiantes excluidos (IDs): ' . implode(', ', $excludedStudents));
        }
        if (!empty($excludedEnrollments)) {
            $this->comment('🚫 Inscripciones excluidas (IDs): ' . implode(', ', $excludedEnrollments));
        }

        $query = Course::query()
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->where('monthly_fee', '>', 0)
            ->with(['enrollments' => function ($q) {
                $q->where('status', '!=', 'cancelled')
                  ->where('is_free_trial', false)
                  ->with(['student', 'courses', 'receivable', 'transactions']);
            }]);

        if ($courseId) {
            $query->where('id', $courseId);
        }

        $courses = $query->get();

        if ($courses->isEmpty()) {
            $this->warn('No se encontraron cursos para procesar.');
            return self::SUCCESS;
        }

        $reportRows = [];
        $totalUpdated = 0;

        foreach ($courses as $course) {
            $start = Carbon::parse($course->start_date)->startOfMonth();
            $end = Carbon::parse($course->end_date)->startOfMonth();
            $courseMonths = max(1, $start->diffInMonths($end) + 1);
            $monthlyFee = (float) ($course->monthly_fee ?? 0);
            $standardCourseAmount = $courseMonths * $monthlyFee;
            $feeToAdd = $monthsToAdd * $monthlyFee;

            foreach ($course->enrollments as $enrollment) {
                $studentName = mb_strtolower(trim($enrollment->student?->name ?? ''));
                $isNameExcluded = false;
                foreach ($excludedNames as $exName) {
                    if ($exName !== '' && str_contains($studentName, $exName)) {
                        $isNameExcluded = true;
                        break;
                    }
                }

                if ($isNameExcluded || in_array((int) $enrollment->id, $excludedEnrollments, true) || in_array((int) $enrollment->student_id, $excludedStudents, true)) {
                    continue;
                }

                $targetCourse = $enrollment->courses->firstWhere('id', $course->id);
                if (!$targetCourse) {
                    continue;
                }

                $pivot = $targetCourse->pivot;
                $hasCustom = $pivot && $pivot->custom_amount !== null;

                $receivable = $enrollment->receivable;
                $oldArTotal = $receivable ? (float) $receivable->amount_total : 0.0;
                $oldArBalance = $receivable ? (float) $receivable->balance_due : 0.0;

                $currentCourseAmount = $hasCustom ? (float) $pivot->custom_amount : $standardCourseAmount;
                $newCourseAmount = $currentCourseAmount;

                $needsCustomIncrease = false;

                // Si tiene custom_amount, sumamos la mensualidad solicitada
                if ($hasCustom) {
                    $newCourseAmount = $currentCourseAmount + $feeToAdd;
                    $needsCustomIncrease = true;
                }

                // Calcular el total esperado de la inscripción
                $expectedTotal = 0.0;
                foreach ($enrollment->courses as $idx => $c) {
                    if ($c->id == $course->id && $hasCustom) {
                        $expectedTotal += $newCourseAmount;
                    } else {
                        $expectedTotal += $enrollment->getCourseAmount($c, $idx);
                    }
                }

                // Transacciones existentes del estudiante (100% preservadas e intactas)
                $cTxs = $enrollment->transactions->where('status', 'completed')->where('type', 'income');
                $totalPaid = (float) $cTxs->sum('amount');
                $expectedBalance = max(0.0, $expectedTotal - $totalPaid);

                // Verificar si la CxC está desactualizada respecto a las fechas o si cambió el monto
                $needsArSync = $receivable && (abs($expectedTotal - $oldArTotal) > 0.01 || abs($expectedBalance - $oldArBalance) > 0.01);

                if ($needsCustomIncrease || $needsArSync) {
                    $totalUpdated++;

                    if (!$isDryRun) {
                        if ($needsCustomIncrease) {
                            $enrollment->courses()->updateExistingPivot($course->id, ['custom_amount' => $newCourseAmount]);
                        }
                        $enrollment->refresh();
                        $updatedAr = $enrollment->syncReceivable();
                        $newArBalance = $updatedAr ? (float) $updatedAr->balance_due : $expectedBalance;
                    } else {
                        $newArBalance = $expectedBalance;
                    }

                    // Normalizar transacciones iniciales multi-curso que tenían course_id del primer curso
                    if ($enrollment->courses->count() > 1) {
                        $multiTxs = $enrollment->transactions
                            ->whereNotNull('course_id')
                            ->where('type', 'income')
                            ->filter(fn($t) => (float)$t->amount >= 140.0 || str_contains((string)$t->description, 'Pago confirmado de Inscripción'));
                        
                        if (!$isDryRun) {
                            foreach ($multiTxs as $mTx) {
                                $mTx->update(['course_id' => null]);
                            }
                        }
                    }

                    // Calcular el abono específico para este curso de forma proporcional
                    $directPaid = (float) $cTxs->where('course_id', $course->id)->sum('amount');
                    $generalPaid = (float) $cTxs->whereNull('course_id')->sum('amount');
                    $allocatedGeneral = 0.0;
                    if ($generalPaid > 0) {
                        $totalCourseWeights = 0.0;
                        foreach ($enrollment->courses as $c) {
                            $totalCourseWeights += (float) ($c->monthly_fee ?? 70.0);
                        }
                        if ($totalCourseWeights > 0) {
                            $courseWeight = (float) ($course->monthly_fee ?? 70.0);
                            $allocatedGeneral = ($courseWeight / $totalCourseWeights) * $generalPaid;
                        } else {
                            $allocatedGeneral = $generalPaid / max(1, $enrollment->courses->count());
                        }
                    }
                    $coursePaid = $directPaid + $allocatedGeneral;
                    $oldClassBalance = max(0.00, $currentCourseAmount - $coursePaid);
                    $newClassBalance = max(0.00, $newCourseAmount - $coursePaid);

                    $reportRows[] = [
                        $enrollment->id,
                        substr($enrollment->student?->name ?? 'N/A', 0, 20),
                        substr($course->title, 0, 25),
                        '$' . number_format($currentCourseAmount, 2) . ' ➔ $' . number_format($newCourseAmount, 2),
                        '$' . number_format($coursePaid, 2),
                        '$' . number_format($oldClassBalance, 2) . ' ➔ $' . number_format($newClassBalance, 2),
                    ];
                }
            }
        }

        if (empty($reportRows)) {
            $this->info('✅ Todas las cuentas e inscripciones ya se encuentran al día con las fechas y mensualidades actuales.');
            return self::SUCCESS;
        }

        $this->table(
            ['ID Insc.', 'Estudiante', 'Curso', 'Monto Clase', 'Abonado', 'Saldo CxC (Deuda)'],
            $reportRows
        );

        if ($isDryRun) {
            $this->warn("Se encontraron {$totalUpdated} inscripciones que requieren actualización de CxC.");
            $this->info('Para aplicar los cambios definitivos en la base de datos, ejecute:');
            $this->comment('php artisan courses:sync-balances' . ($courseId ? " --course={$courseId}" : ''));
        } else {
            $this->info("✅ Se sincronizaron exitosamente {$totalUpdated} inscripciones y sus cuentas por cobrar.");
        }

        return self::SUCCESS;
    }
}
