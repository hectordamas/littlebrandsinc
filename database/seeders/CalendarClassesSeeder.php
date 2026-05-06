<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Course;
use App\Models\LBClass;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CalendarClassesSeeder extends Seeder
{
    public function run(): void
    {
        $courses = Course::query()->orderBy('id')->get();
        $coaches = User::query()->where('role', 'Coach')->orderBy('id')->get();

        if ($courses->isEmpty()) {
            $this->command?->warn('No hay cursos para crear clases de calendario.');

            return;
        }

        $fallbackBranchId = Branch::query()->value('id');
        $created = 0;

        foreach ($courses as $index => $course) {
            $baseDate = Carbon::now()->startOfWeek()->addDays($index % 5);

            for ($week = 0; $week < 4; $week++) {
                $firstDate = (clone $baseDate)->addWeeks($week);
                $secondDate = (clone $firstDate)->addDays(2);

                $created += $this->createClassIfMissing($course, $coaches, $fallbackBranchId, $firstDate, '16:00:00', '17:00:00');
                $created += $this->createClassIfMissing($course, $coaches, $fallbackBranchId, $secondDate, '17:30:00', '18:30:00');
            }
        }

        $this->command?->info("CalendarClassesSeeder completado: {$created} clase(s) creadas.");
    }

    protected function createClassIfMissing(
        Course $course,
        $coaches,
        ?int $fallbackBranchId,
        Carbon $date,
        string $startTime,
        string $endTime
    ): int {
        $branchId = $course->branch_id ?: $fallbackBranchId;
        if (! $branchId) {
            return 0;
        }

        $coachId = $coaches->isNotEmpty()
            ? (int) $coaches->get(($course->id + $date->weekOfYear) % $coaches->count())->id
            : null;

        $class = LBClass::query()->firstOrCreate([
            'course_id' => $course->id,
            'branch_id' => $branchId,
            'date' => $date->toDateString(),
            'start_time' => $startTime,
        ], [
            'end_time' => $endTime,
            'coach_id' => $coachId,
        ]);

        if (! $class->wasRecentlyCreated) {
            return 0;
        }

        return 1;
    }
}