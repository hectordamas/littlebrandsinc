<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class EnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $parents = User::where('role', 'Padre')->get();
        if ($parents->isEmpty()) {
            $this->command->warn('No hay usuarios con rol Padré. Creando padres...');
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

        $courses = Course::all();
        if ($courses->isEmpty()) {
            $this->command->warn('No hay courses. Creando courses...');
            $courseNames = ['Futbol', 'Natacion', 'Baloncesto', 'Voleibol', 'Tenis'];
            for ($i = 0; $i < count($courseNames); $i++) {
                $course = new Course;
                $course->title = $courseNames[$i];
                $course->description = 'Curso de '.$courseNames[$i];
                $course->price = 50 + ($i * 10);
                $course->start_date = now();
                $course->end_date = now()->addMonths(3);
                $course->branch_id = $branches->first()->id;
                $course->save();
                $courses->push($course);
            }
        }

        $statuses = ['active', 'completed', 'cancelled', 'pending'];
        $paymentMethods = ['cash', 'transfer', 'zelle', 'pago_movil'];
        $paymentStatuses = ['paid', 'pending', 'failed'];

        $usedPairs = [];

        for ($i = 0; $i < 20; $i++) {
            $parent = $parents->random();
            $student = Student::where('user_id', $parent->id)->first();

            if (! $student) {
                $student = new Student;
                $student->user_id = $parent->id;
                $student->name = 'Estudiante '.($i + 1);
                $student->birthdate = now()->subYears(rand(5, 15));
                $student->level = ['principiante', 'intermedio', 'avanzado'][rand(0, 2)];
                $student->save();
            }

            $courseId = $courses->random()->id;
            $pairKey = $student->id.'-'.$courseId;
            if (in_array($pairKey, $usedPairs)) {
                continue;
            }
            $usedPairs[] = $pairKey;

            $enrollment = new Enrollment;
            $enrollment->student_id = $student->id;
            $enrollment->course_id = $courseId;
            $enrollment->parent_id = $parent->id;
            $enrollment->status = $statuses[rand(0, count($statuses) - 1)];
            $enrollment->payment_method = $paymentMethods[rand(0, count($paymentMethods) - 1)];
            $enrollment->payment_status = $paymentStatuses[rand(0, count($paymentStatuses) - 1)];
            $enrollment->save();
        }

        $this->command->info('EnrollmentSeeder completado: 20 enrollments creados');
    }
}
