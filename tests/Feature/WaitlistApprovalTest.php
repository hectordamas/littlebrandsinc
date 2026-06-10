<?php

use App\Models\{Branch, Course, Enrollment, Program, Student, User, Waitlist};

/**
 * Flujo 3: Aprobación lista de espera — WaitlistController@approve
 * crea enrollment y asigna curso.
 */

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'Administrador']);
    $this->parent = User::factory()->create(['role' => 'Padre']);

    $branch = Branch::create([
        'name' => 'SEDE SAN LUIS',
        'address' => 'San Luis',
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

    $this->course = Course::create([
        'title' => 'Baby - Lunes',
        'program_id' => $this->program->id,
        'branch_id' => $branch->id,
        'min_age' => 1.5,
        'max_age' => 3.0,
        'capacity' => 15,
        'monthly_fee' => 40.00,
        'start_date' => now()->startOfMonth(),
        'end_date' => now()->addMonths(3)->endOfMonth(),
        'active' => true,
    ]);

    $this->student = Student::create([
        'user_id' => $this->parent->id,
        'name' => 'Test Child',
        'birthdate' => now()->subYears(2)->format('Y-m-d'),
    ]);

    // Crear entrada en lista de espera
    $this->waitlist = Waitlist::create([
        'student_id' => $this->student->id,
        'course_id' => $this->course->id,
        'parent_id' => $this->parent->id,
        'status' => 'pending',
        'notes' => 'Quiere entrar al curso',
    ]);
});

test('admin aprueba entrada en lista de espera y asigna curso', function () {
    $this->actingAs($this->admin);

    $response = $this->patch(route('waitlists.approve', $this->waitlist));

    $response->assertRedirect();

    // Verificar waitlist marcada como approved
    $this->waitlist->refresh();
    expect($this->waitlist->status)->toBe('approved');

    // Verificar enrollment creado con el curso
    $enrollment = Enrollment::where('student_id', $this->student->id)
        ->where('program_id', $this->course->program_id)
        ->first();

    expect($enrollment)->not->toBeNull();
    expect($enrollment->courses->pluck('id'))->toContain($this->course->id);
});

test('admin rechaza entrada en lista de espera', function () {
    $this->actingAs($this->admin);

    $response = $this->patch(route('waitlists.reject', $this->waitlist), [
        'rejection_reason' => 'Cupo lleno',
    ]);

    $response->assertRedirect();

    $this->waitlist->refresh();
    expect($this->waitlist->status)->toBe('rejected');
});
