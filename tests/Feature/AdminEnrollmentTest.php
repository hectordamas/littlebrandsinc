<?php

use App\Models\{Branch, Course, Enrollment, Program, Student, User};
use App\Models\AccountReceivable;
use Illuminate\Support\Facades\Hash;

/**
 * Flujo 2: Admin crea enrollment multi-curso vía EnrollmentController@store.
 */

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => 'Administrador',
    ]);

    $this->branch = Branch::create([
        'name' => 'SEDE SAN LUIS',
        'address' => 'San Luis',
        'email' => 'admin@test.com',
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

    $parent = User::factory()->create(['role' => 'Padre']);

    $this->student = Student::create([
        'user_id' => $parent->id,
        'name' => 'Test Child',
        'birthdate' => now()->subYears(2)->format('Y-m-d'),
    ]);
});

test('admin crea enrollment con múltiples cursos', function () {
    $this->actingAs($this->admin);

    $response = $this->post(route('enrollment.store'), [
        'student_id' => $this->student->id,
        'user_id' => $this->student->user_id,
        'program_id' => $this->program->id,
        'course_ids' => [$this->course1->id, $this->course2->id],
        'payment_status' => 'pending',
        'is_free_trial' => false,
        'image_consent_accepted' => true,
    ]);

    $response->assertRedirect();

    $enrollment = Enrollment::where('student_id', $this->student->id)
        ->where('program_id', $this->program->id)
        ->first();

    expect($enrollment)->not->toBeNull();
    expect($enrollment->courses->count())->toBe(2);

    $courseIds = $enrollment->courses->pluck('id')->toArray();
    expect($courseIds)->toContain($this->course1->id);
    expect($courseIds)->toContain($this->course2->id);
});

test('admin crea enrollment free trial sin generar cuenta por cobrar', function () {
    $this->actingAs($this->admin);

    $response = $this->post(route('enrollment.store'), [
        'student_id' => $this->student->id,
        'user_id' => $this->student->user_id,
        'program_id' => $this->program->id,
        'course_ids' => [$this->course1->id],
        'payment_status' => 'paid',
        'is_free_trial' => true,
        'image_consent_accepted' => true,
    ]);

    $response->assertRedirect();

    $enrollment = Enrollment::where('student_id', $this->student->id)
        ->where('program_id', $this->program->id)
        ->first();

    expect($enrollment)->not->toBeNull();
    expect($enrollment->is_free_trial)->toBeTrue();

    // Free trial no debe generar receivable
    $receivable = AccountReceivable::where('enrollment_id', $enrollment->id)->first();
    expect($receivable)->toBeNull();
});
