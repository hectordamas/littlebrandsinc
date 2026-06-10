<?php

use App\Models\{Branch, Course, Enrollment, Program, Student, User};
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;

/**
 * Flujo 1: Wizard completo — registro estudiante nuevo,
 * selección programa + cursos, pago manual con comprobante.
 */

beforeEach(function () {
    $this->branch = Branch::create([
        'name' => 'SEDE SAN LUIS',
        'address' => 'San Luis, Caracas',
        'email' => 'test@test.com',
        'phone' => '+58 424-0000001',
        'active' => true,
    ]);

    $this->program = Program::firstOrCreate(
        ['slug' => 'little-strikers'],
        [
            'name' => 'Little Strikers',
            'description' => 'Test',
            'enrollment_fee' => 50.00,
            'active' => true,
        ]
    );

    $this->course1 = Course::create([
        'title' => 'Baby - Lunes',
        'program_id' => $this->program->id,
        'branch_id' => $this->branch->id,
        'min_age' => 1.5,
        'max_age' => 3.0,
        'capacity' => 15,
        'monthly_fee' => 40.00,
        'start_date' => now()->startOfMonth(),
        'end_date' => now()->addMonths(3)->endOfMonth(),
        'active' => true,
    ]);

    $this->course2 = Course::create([
        'title' => 'Mini - Miércoles',
        'program_id' => $this->program->id,
        'branch_id' => $this->branch->id,
        'min_age' => 2.0,
        'max_age' => 4.0,
        'capacity' => 15,
        'monthly_fee' => 45.00,
        'start_date' => now()->startOfMonth(),
        'end_date' => now()->addMonths(3)->endOfMonth(),
        'active' => true,
    ]);
});

test('wizard crea enrollment con programa y múltiples cursos vía pago manual', function () {
    // Registrar nuevo usuario + estudiante + enrollment
    $response = $this->post(route('students.register'), [
        'user_type' => 'new',
        'name' => 'Test Parent',
        'email' => 'parent@test.com',
        'whatsapp' => '4121234567',
        'dial_code' => '+58',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'student_name' => 'Test Child',
        'birthdate' => now()->subYears(2)->format('Y-m-d'),
        'medical_notes' => '',
        'terms' => 'on',
        'program_id' => $this->program->id,
        'course_ids' => [$this->course1->id, $this->course2->id],
    ]);

    $response->assertRedirect(route('home'));

    // Verificar enrollment creado
    $enrollment = Enrollment::first();
    expect($enrollment)->not->toBeNull();
    expect($enrollment->program_id)->toBe($this->program->id);
    expect($enrollment->status)->toBe('active');
    expect($enrollment->terms_accepted)->toBeTrue();

    // Verificar cursos sincronizados en pivot
    $courseIds = $enrollment->courses->pluck('id')->toArray();
    expect($courseIds)->toContain($this->course1->id);
    expect($courseIds)->toContain($this->course2->id);
    expect(count($courseIds))->toBe(2);

    // Verificar estudiante creado
    $student = Student::first();
    expect($student)->not->toBeNull();
    expect($student->name)->toBe('Test Child');

    // Verificar usuario creado
    $user = User::where('email', 'parent@test.com')->first();
    expect($user)->not->toBeNull();
    expect(Hash::check('password123', $user->password))->toBeTrue();
});
