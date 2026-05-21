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
                    'active' => true,
                ]
            ),
            'little-paddlers' => Program::query()->firstOrCreate(
                ['slug' => 'little-paddlers'],
                [
                    'name' => 'Little Paddlers',
                    'description' => 'Programa de padel infantil.',
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
            [
                'program_slug' => 'little-strikers',
                'branch_name' => 'SEDE SAN LUIS',
                'title' => 'Baby Strikers (18 a 24 meses)',
                'min_age' => 1,
                'max_age' => 2,
                'schedule' => [
                    ['day' => 1, 'start' => '16:00:00'],
                    ['day' => 3, 'start' => '16:00:00'],
                    ['day' => 6, 'start' => '09:00:00'],
                ],
            ],
            [
                'program_slug' => 'little-strikers',
                'branch_name' => 'SEDE SAN LUIS',
                'title' => 'Mini Strikers (24 a 36 meses)',
                'min_age' => 2,
                'max_age' => 3,
                'schedule' => [
                    ['day' => 1, 'start' => '16:00:00'],
                    ['day' => 3, 'start' => '16:00:00'],
                    ['day' => 6, 'start' => '10:00:00'],
                ],
            ],
            [
                'program_slug' => 'little-strikers',
                'branch_name' => 'SEDE SAN LUIS',
                'title' => 'Super Strikers (36 a 48 meses)',
                'min_age' => 3,
                'max_age' => 4,
                'schedule' => [
                    ['day' => 1, 'start' => '17:00:00'],
                    ['day' => 3, 'start' => '17:00:00'],
                    ['day' => 6, 'start' => '11:00:00'],
                ],
            ],
            [
                'program_slug' => 'little-strikers',
                'branch_name' => 'SEDE LOS CAMPITOS',
                'title' => 'Baby Strikers (18 a 24 meses)',
                'min_age' => 1,
                'max_age' => 2,
                'schedule' => [
                    ['day' => 2, 'start' => '16:00:00'],
                    ['day' => 4, 'start' => '16:00:00'],
                ],
            ],
            [
                'program_slug' => 'little-strikers',
                'branch_name' => 'SEDE LOS CAMPITOS',
                'title' => 'Mini Strikers (24 a 36 meses)',
                'min_age' => 2,
                'max_age' => 3,
                'schedule' => [
                    ['day' => 2, 'start' => '16:00:00'],
                    ['day' => 4, 'start' => '16:00:00'],
                ],
            ],
            [
                'program_slug' => 'little-strikers',
                'branch_name' => 'SEDE LOS CAMPITOS',
                'title' => 'Super Strikers (36 a 48 meses)',
                'min_age' => 3,
                'max_age' => 4,
                'schedule' => [
                    ['day' => 2, 'start' => '17:00:00'],
                    ['day' => 4, 'start' => '17:00:00'],
                ],
            ],
            [
                'program_slug' => 'little-strikers',
                'branch_name' => 'SEDE LOS CHORROS',
                'title' => 'Baby Strikers (18 a 24 meses)',
                'min_age' => 1,
                'max_age' => 2,
                'schedule' => [
                    ['day' => 6, 'start' => '09:00:00'],
                ],
            ],
            [
                'program_slug' => 'little-strikers',
                'branch_name' => 'SEDE LOS CHORROS',
                'title' => 'Mini Strikers (24 a 36 meses)',
                'min_age' => 2,
                'max_age' => 3,
                'schedule' => [
                    ['day' => 6, 'start' => '09:00:00'],
                ],
            ],
            [
                'program_slug' => 'little-strikers',
                'branch_name' => 'SEDE LOS CHORROS',
                'title' => 'Super Strikers (36 a 48 meses)',
                'min_age' => 3,
                'max_age' => 4,
                'schedule' => [
                    ['day' => 6, 'start' => '10:00:00'],
                ],
            ],
            [
                'program_slug' => 'little-paddlers',
                'branch_name' => 'SEDE LOS CHORROS',
                'title' => 'Baby Paddlers (2 a 3 años)',
                'min_age' => 2,
                'max_age' => 3,
                'schedule' => [
                    ['day' => 2, 'start' => '16:00:00'],
                ],
            ],
            [
                'program_slug' => 'little-paddlers',
                'branch_name' => 'SEDE LOS CHORROS',
                'title' => 'Mini Paddlers (3 a 4 años)',
                'min_age' => 3,
                'max_age' => 4,
                'schedule' => [
                    ['day' => 2, 'start' => '16:00:00'],
                ],
            ],
            [
                'program_slug' => 'little-paddlers',
                'branch_name' => 'SEDE LOS CHORROS',
                'title' => 'Super Paddlers (4 a 5 años)',
                'min_age' => 4,
                'max_age' => 5,
                'schedule' => [
                    ['day' => 2, 'start' => '17:00:00'],
                ],
            ],
            [
                'program_slug' => 'little-paddlers',
                'branch_name' => 'SEDE LOS CAMPITOS',
                'title' => 'Baby Paddlers (2 a 3 años)',
                'min_age' => 2,
                'max_age' => 3,
                'schedule' => [
                    ['day' => 3, 'start' => '16:00:00'],
                ],
            ],
            [
                'program_slug' => 'little-paddlers',
                'branch_name' => 'SEDE LOS CAMPITOS',
                'title' => 'Mini Paddlers (3 a 4 años)',
                'min_age' => 3,
                'max_age' => 4,
                'schedule' => [
                    ['day' => 3, 'start' => '16:00:00'],
                ],
            ],
            [
                'program_slug' => 'little-paddlers',
                'branch_name' => 'SEDE LOS CAMPITOS',
                'title' => 'Super Paddlers (4 a 5 años)',
                'min_age' => 4,
                'max_age' => 5,
                'schedule' => [
                    ['day' => 3, 'start' => '17:00:00'],
                ],
            ],
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
                    'price' => 35.00,
                    'monthly_fee' => 95.00,
                    'start_date' => $today->toDateString(),
                    'end_date' => $endDate->toDateString(),
                    'active' => true,
                ]
            );

            $this->seedCourseClasses($course, $branch->id, $today, $endDate, $courseData['schedule']);
        }
    }

    protected function seedCourseClasses(Course $course, int $branchId, Carbon $startDate, Carbon $endDate, array $schedule): void
    {
        foreach ($schedule as $slot) {
            $weekday = (int) $slot['day'];
            $startTime = (string) $slot['start'];
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
}
