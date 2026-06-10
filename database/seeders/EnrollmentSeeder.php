<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class EnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $parents = User::where('role', 'Padre')->get();
        if ($parents->isEmpty()) {
            $this->command->warn('No hay usuarios con rol Padre. Creando padres...');
            for ($i = 0; $i < 5; $i++) {
                $parent = new User;
                $parent->name = 'Padre '.($i + 1);
                $parent->email = 'padre'.($i + 1).'@example.com';
                $parent->password = bcrypt('password');
                $parent->role = 'Padre';
                $parent->whatsapp = '123456789'.$i;
                $parent->dial_code = '+1';
                $parent->save();
                $parents->push($parent);
            }
        }

        $branches = Branch::all();
        if ($branches->isEmpty()) {
            $this->command->warn('No hay branches. Creando branch...');
            $branch = new Branch;
            $branch->name = 'Sede Principal';
            $branch->address = 'Direccion Default';
            $branch->save();
            $branches->push($branch);
        }

        $program = Program::first();
        if (! $program) {
            $program = Program::create([
                'name' => 'Programa General',
                'slug' => 'programa-general',
                'description' => 'Programa generado por seeder.',
                'enrollment_fee' => 50.00,
            ]);
        }

        $courses = Course::all();
        if ($courses->isEmpty()) {
            $this->command->warn('No hay courses. Creando courses...');
            $courseNames = ['Futbol', 'Natacion', 'Baloncesto', 'Voleibol', 'Tenis'];
            for ($i = 0; $i < count($courseNames); $i++) {
                $course = new Course;
                $course->title = $courseNames[$i];
                $course->description = 'Curso de '.$courseNames[$i];
                $course->program_id = $program->id;
                $course->monthly_fee = 95.00;
                $course->start_date = now();
                $course->end_date = now()->addMonths(3);
                $course->branch_id = $branches->first()->id;
                $course->save();
                $courses->push($course);
            }
        }

        $statuses = ['active', 'completed', 'cancelled', 'pending'];
        $paymentMethods = ['manual', 'card', 'pending'];
        $paymentStatuses = ['paid', 'pending'];

        $usedPairs = [];

        for ($i = 0; $i < 20; $i++) {
            $parent = $parents->random();
            $student = Student::where('user_id', $parent->id)->first();

            if (! $student) {
                $student = new Student;
                $student->user_id = $parent->id;
                $student->name = 'Estudiante '.($i + 1);
                $student->birthdate = now()->subYears(rand(5, 15));
                $student->save();
            }

            $courseId = $courses->random()->id;
            $pairKey = $student->id.'-'.$courseId;
            if (in_array($pairKey, $usedPairs)) {
                continue;
            }
            $usedPairs[] = $pairKey;

            $courseModel = Course::find($courseId);

            $enrollment = new Enrollment;
            $enrollment->student_id = $student->id;
            $enrollment->program_id = $courseModel->program_id ?? $program->id;
            $enrollment->parent_id = $parent->id;
            $enrollment->status = $statuses[rand(0, count($statuses) - 1)];
            $enrollment->payment_method = $paymentMethods[rand(0, count($paymentMethods) - 1)];
            $enrollment->payment_status = $paymentStatuses[rand(0, count($paymentStatuses) - 1)];
            $enrollment->is_free_trial = false;
            $enrollment->terms_accepted = true;
            $enrollment->image_consent_accepted = true;
            $enrollment->save();

            $enrollment->courses()->syncWithoutDetaching([$courseId]);
        }

        $this->command->info('EnrollmentSeeder completado: enrollments creados');
    }
}
