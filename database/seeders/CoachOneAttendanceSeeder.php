<?php

namespace Database\Seeders;

use App\Models\Enrollment;
use App\Models\LBClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class CoachOneAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $coach = User::query()
            ->where('email', 'coach1@example.com')
            ->orWhere('name', 'Coach 1')
            ->first();

        if (! $coach) {
            $this->command?->warn('No se encontro Coach 1.');

            return;
        }

        $courseIds = LBClass::query()
            ->where('coach_id', $coach->id)
            ->pluck('course_id')
            ->unique()
            ->values();

        if ($courseIds->isEmpty()) {
            $this->command?->warn('Coach 1 no tiene clases asignadas.');

            return;
        }

        $parents = User::query()
            ->where('role', 'Padre')
            ->orderBy('id')
            ->take(8)
            ->get();

        if ($parents->isEmpty()) {
            $this->command?->warn('No hay padres para crear estudiantes de prueba.');

            return;
        }

        $createdStudents = 0;
        $createdEnrollments = 0;

        foreach ($parents as $index => $parent) {
            $student = Student::query()->firstOrCreate(
                [
                    'user_id' => $parent->id,
                    'name' => 'Alumno Coach1 '.($index + 1),
                ],
                [
                    'birthdate' => now()->subYears(9 + ($index % 3))->toDateString(),
                    'medical_notes' => null,
                ]
            );

            if ($student->wasRecentlyCreated) {
                $createdStudents++;
            }

            foreach ($courseIds as $courseId) {
                $enrollment = Enrollment::query()->firstOrCreate(
                    [
                        'student_id' => $student->id,
                        'course_id' => $courseId,
                    ],
                    [
                        'parent_id' => $parent->id,
                        'status' => 'pending',
                        'payment_method' => 'manual',
                        'payment_status' => 'pending',
                    ]
                );

                if ($enrollment->wasRecentlyCreated) {
                    $createdEnrollments++;
                }
            }
        }

        $this->command?->info("CoachOneAttendanceSeeder: estudiantes nuevos={$createdStudents}, inscripciones nuevas={$createdEnrollments}");
    }
}
