<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Course;
use App\Models\LBClass;
use App\Models\Program;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProgramsCoursesSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();
        $endDate = (clone $today)->addMonthsNoOverflow(12);

        $programs = [
            'little-strikers' => Program::query()->firstOrCreate(
                ['slug' => 'little-strikers'],
                [
                    'name' => 'Little Strikers',
                    'description' => 'Programa de futbol infantil.',
                    'enrollment_fee' => 50.00,
                    'active' => true,
                ]
            ),
            'little-paddlers' => Program::query()->firstOrCreate(
                ['slug' => 'little-paddlers'],
                [
                    'name' => 'Little Paddlers',
                    'description' => 'Programa de padel infantil.',
                    'enrollment_fee' => 50.00,
                    'active' => true,
                ]
            ),
        ];

        $branches = Branch::query()->whereIn('name', [
            'SEDE SAN LUIS',
            'SEDE LOS CAMPITOS',
            'SEDE LOS CHORROS',
        ])->get()->keyBy('name');

        $catalog = [
            // ── SEDE SAN LUIS · Little Strikers ──
            ['program_slug' => 'little-strikers', 'branch_name' => 'SEDE SAN LUIS', 'title' => 'Little Strikers Baby - Lunes',     'min_age' => 1, 'max_age' => 2, 'day' => 1, 'start' => '16:00:00'],
            ['program_slug' => 'little-strikers', 'branch_name' => 'SEDE SAN LUIS', 'title' => 'Little Strikers Baby - Miércoles', 'min_age' => 1, 'max_age' => 2, 'day' => 3, 'start' => '16:00:00'],
            ['program_slug' => 'little-strikers', 'branch_name' => 'SEDE SAN LUIS', 'title' => 'Little Strikers Baby - Sábado',    'min_age' => 1, 'max_age' => 2, 'day' => 6, 'start' => '09:00:00'],
            ['program_slug' => 'little-strikers', 'branch_name' => 'SEDE SAN LUIS', 'title' => 'Little Strikers Mini - Lunes',     'min_age' => 2, 'max_age' => 3, 'day' => 1, 'start' => '16:00:00'],
            ['program_slug' => 'little-strikers', 'branch_name' => 'SEDE SAN LUIS', 'title' => 'Little Strikers Mini - Miércoles', 'min_age' => 2, 'max_age' => 3, 'day' => 3, 'start' => '16:00:00'],
            ['program_slug' => 'little-strikers', 'branch_name' => 'SEDE SAN LUIS', 'title' => 'Little Strikers Mini - Sábado',    'min_age' => 2, 'max_age' => 3, 'day' => 6, 'start' => '10:00:00'],
            ['program_slug' => 'little-strikers', 'branch_name' => 'SEDE SAN LUIS', 'title' => 'Little Strikers Super - Lunes',     'min_age' => 3, 'max_age' => 4, 'day' => 1, 'start' => '17:00:00'],
            ['program_slug' => 'little-strikers', 'branch_name' => 'SEDE SAN LUIS', 'title' => 'Little Strikers Super - Miércoles', 'min_age' => 3, 'max_age' => 4, 'day' => 3, 'start' => '17:00:00'],
            ['program_slug' => 'little-strikers', 'branch_name' => 'SEDE SAN LUIS', 'title' => 'Little Strikers Super - Sábado',    'min_age' => 3, 'max_age' => 4, 'day' => 6, 'start' => '11:00:00'],

            // ── SEDE LOS CAMPITOS · Little Strikers ──
            ['program_slug' => 'little-strikers', 'branch_name' => 'SEDE LOS CAMPITOS', 'title' => 'Little Strikers Baby - Martes', 'min_age' => 1, 'max_age' => 2, 'day' => 2, 'start' => '16:00:00'],
            ['program_slug' => 'little-strikers', 'branch_name' => 'SEDE LOS CAMPITOS', 'title' => 'Little Strikers Baby - Jueves', 'min_age' => 1, 'max_age' => 2, 'day' => 4, 'start' => '16:00:00'],
            ['program_slug' => 'little-strikers', 'branch_name' => 'SEDE LOS CAMPITOS', 'title' => 'Little Strikers Mini - Martes', 'min_age' => 2, 'max_age' => 3, 'day' => 2, 'start' => '16:00:00'],
            ['program_slug' => 'little-strikers', 'branch_name' => 'SEDE LOS CAMPITOS', 'title' => 'Little Strikers Mini - Jueves', 'min_age' => 2, 'max_age' => 3, 'day' => 4, 'start' => '16:00:00'],
            ['program_slug' => 'little-strikers', 'branch_name' => 'SEDE LOS CAMPITOS', 'title' => 'Little Strikers Super - Martes', 'min_age' => 3, 'max_age' => 4, 'day' => 2, 'start' => '17:00:00'],
            ['program_slug' => 'little-strikers', 'branch_name' => 'SEDE LOS CAMPITOS', 'title' => 'Little Strikers Super - Jueves', 'min_age' => 3, 'max_age' => 4, 'day' => 4, 'start' => '17:00:00'],

            // ── SEDE LOS CHORROS · Little Strikers ──
            ['program_slug' => 'little-strikers', 'branch_name' => 'SEDE LOS CHORROS', 'title' => 'Little Strikers Baby - Lunes',     'min_age' => 1, 'max_age' => 2, 'day' => 1, 'start' => '16:00:00'],
            ['program_slug' => 'little-strikers', 'branch_name' => 'SEDE LOS CHORROS', 'title' => 'Little Strikers Baby - Miércoles', 'min_age' => 1, 'max_age' => 2, 'day' => 3, 'start' => '16:00:00'],
            ['program_slug' => 'little-strikers', 'branch_name' => 'SEDE LOS CHORROS', 'title' => 'Little Strikers Baby - Sábado',    'min_age' => 1, 'max_age' => 2, 'day' => 6, 'start' => '09:00:00'],
            ['program_slug' => 'little-strikers', 'branch_name' => 'SEDE LOS CHORROS', 'title' => 'Little Strikers Mini - Lunes',     'min_age' => 2, 'max_age' => 3, 'day' => 1, 'start' => '16:00:00'],
            ['program_slug' => 'little-strikers', 'branch_name' => 'SEDE LOS CHORROS', 'title' => 'Little Strikers Mini - Miércoles', 'min_age' => 2, 'max_age' => 3, 'day' => 3, 'start' => '16:00:00'],
            ['program_slug' => 'little-strikers', 'branch_name' => 'SEDE LOS CHORROS', 'title' => 'Little Strikers Mini - Sábado',    'min_age' => 2, 'max_age' => 3, 'day' => 6, 'start' => '09:00:00'],
            ['program_slug' => 'little-strikers', 'branch_name' => 'SEDE LOS CHORROS', 'title' => 'Little Strikers Super - Lunes',     'min_age' => 3, 'max_age' => 4, 'day' => 1, 'start' => '17:00:00'],
            ['program_slug' => 'little-strikers', 'branch_name' => 'SEDE LOS CHORROS', 'title' => 'Little Strikers Super - Miércoles', 'min_age' => 3, 'max_age' => 4, 'day' => 3, 'start' => '17:00:00'],
            ['program_slug' => 'little-strikers', 'branch_name' => 'SEDE LOS CHORROS', 'title' => 'Little Strikers Super - Sábado',    'min_age' => 3, 'max_age' => 4, 'day' => 6, 'start' => '10:00:00'],

            // ── SEDE LOS CHORROS · Little Paddlers ──
            ['program_slug' => 'little-paddlers', 'branch_name' => 'SEDE LOS CHORROS', 'title' => 'Little Paddlers Baby - Martes',  'min_age' => 2, 'max_age' => 3, 'day' => 2, 'start' => '16:00:00'],
            ['program_slug' => 'little-paddlers', 'branch_name' => 'SEDE LOS CHORROS', 'title' => 'Little Paddlers Baby - Jueves',  'min_age' => 2, 'max_age' => 3, 'day' => 4, 'start' => '16:00:00'],
            ['program_slug' => 'little-paddlers', 'branch_name' => 'SEDE LOS CHORROS', 'title' => 'Little Paddlers Baby - Sábado',  'min_age' => 2, 'max_age' => 3, 'day' => 6, 'start' => '09:00:00'],
            ['program_slug' => 'little-paddlers', 'branch_name' => 'SEDE LOS CHORROS', 'title' => 'Little Paddlers Mini - Martes',  'min_age' => 3, 'max_age' => 4, 'day' => 2, 'start' => '16:00:00'],
            ['program_slug' => 'little-paddlers', 'branch_name' => 'SEDE LOS CHORROS', 'title' => 'Little Paddlers Mini - Jueves',  'min_age' => 3, 'max_age' => 4, 'day' => 4, 'start' => '16:00:00'],
            ['program_slug' => 'little-paddlers', 'branch_name' => 'SEDE LOS CHORROS', 'title' => 'Little Paddlers Mini - Sábado',  'min_age' => 3, 'max_age' => 4, 'day' => 6, 'start' => '09:00:00'],
            ['program_slug' => 'little-paddlers', 'branch_name' => 'SEDE LOS CHORROS', 'title' => 'Little Paddlers Super - Martes', 'min_age' => 4, 'max_age' => 5, 'day' => 2, 'start' => '17:00:00'],
            ['program_slug' => 'little-paddlers', 'branch_name' => 'SEDE LOS CHORROS', 'title' => 'Little Paddlers Super - Jueves', 'min_age' => 4, 'max_age' => 5, 'day' => 4, 'start' => '17:00:00'],
            ['program_slug' => 'little-paddlers', 'branch_name' => 'SEDE LOS CHORROS', 'title' => 'Little Paddlers Super - Sábado', 'min_age' => 4, 'max_age' => 5, 'day' => 6, 'start' => '10:00:00'],

            // ── SEDE LOS CAMPITOS · Little Paddlers ──
            ['program_slug' => 'little-paddlers', 'branch_name' => 'SEDE LOS CAMPITOS', 'title' => 'Little Paddlers Baby - Miércoles', 'min_age' => 2, 'max_age' => 3, 'day' => 3, 'start' => '16:00:00'],
            ['program_slug' => 'little-paddlers', 'branch_name' => 'SEDE LOS CAMPITOS', 'title' => 'Little Paddlers Baby - Viernes',   'min_age' => 2, 'max_age' => 3, 'day' => 5, 'start' => '16:00:00'],
            ['program_slug' => 'little-paddlers', 'branch_name' => 'SEDE LOS CAMPITOS', 'title' => 'Little Paddlers Baby - Sábado',    'min_age' => 2, 'max_age' => 3, 'day' => 6, 'start' => '09:00:00'],
            ['program_slug' => 'little-paddlers', 'branch_name' => 'SEDE LOS CAMPITOS', 'title' => 'Little Paddlers Mini - Miércoles', 'min_age' => 3, 'max_age' => 4, 'day' => 3, 'start' => '16:00:00'],
            ['program_slug' => 'little-paddlers', 'branch_name' => 'SEDE LOS CAMPITOS', 'title' => 'Little Paddlers Mini - Viernes',   'min_age' => 3, 'max_age' => 4, 'day' => 5, 'start' => '16:00:00'],
            ['program_slug' => 'little-paddlers', 'branch_name' => 'SEDE LOS CAMPITOS', 'title' => 'Little Paddlers Mini - Sábado',    'min_age' => 3, 'max_age' => 4, 'day' => 6, 'start' => '09:00:00'],
            ['program_slug' => 'little-paddlers', 'branch_name' => 'SEDE LOS CAMPITOS', 'title' => 'Little Paddlers Super - Miércoles', 'min_age' => 4, 'max_age' => 5, 'day' => 3, 'start' => '17:00:00'],
            ['program_slug' => 'little-paddlers', 'branch_name' => 'SEDE LOS CAMPITOS', 'title' => 'Little Paddlers Super - Viernes',   'min_age' => 4, 'max_age' => 5, 'day' => 5, 'start' => '17:00:00'],
            ['program_slug' => 'little-paddlers', 'branch_name' => 'SEDE LOS CAMPITOS', 'title' => 'Little Paddlers Super - Sábado',    'min_age' => 4, 'max_age' => 5, 'day' => 6, 'start' => '10:00:00'],
        ];

        foreach ($catalog as $courseData) {
            $program = $programs[$courseData['program_slug']] ?? null;
            $branch = $branches->get($courseData['branch_name']);

            if (! $program || ! $branch) {
                continue;
            }

            $course = Course::query()->updateOrCreate(
                [
                    'program_id' => $program->id,
                    'branch_id' => $branch->id,
                    'title' => $courseData['title'],
                ],
                [
                    'description' => 'Clase regular de ' . $program->name,
                    'min_age' => $courseData['min_age'],
                    'max_age' => $courseData['max_age'],
                    'capacity' => 12,
                    'monthly_fee' => 95.00,
                    'start_date' => $today->toDateString(),
                    'end_date' => $endDate->toDateString(),
                    'active' => true,
                ]
            );

            $this->seedCourseClasses($course, $branch->id, $today, $endDate, $courseData['day'], $courseData['start']);
        }
    }

    protected function seedCourseClasses(Course $course, int $branchId, Carbon $startDate, Carbon $endDate, int $weekday, string $startTime): void
    {
        $endTime = Carbon::createFromFormat('H:i:s', $startTime)->addMinutes(60)->format('H:i:s');

        $cursor = (clone $startDate)->startOfDay();
        while ((int) $cursor->dayOfWeekIso !== $weekday) {
            $cursor->addDay();
        }

        while ($cursor->lte($endDate)) {
            LBClass::query()->firstOrCreate(
                [
                    'course_id' => $course->id,
                    'branch_id' => $branchId,
                    'date' => $cursor->toDateString(),
                    'start_time' => $startTime,
                ],
                [
                    'end_time' => $endTime,
                    'coach_id' => null,
                ]
            );

            $cursor->addWeek();
        }
    }
}
