<?php

use App\Models\{Branch, Course, Enrollment, Program, Student, User};

/**
 * Flujo 6: StudentsController@show carga correctamente
 * enrollments.courses.branch sin errores 500.
 */

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'Administrador']);
    $parent = User::factory()->create(['role' => 'Padre']);

    $branch = Branch::create([
        'name' => 'SEDE SAN LUIS',
        'address' => 'San Luis',
        'email' => 'test@test.com',
        'phone' => '+58 424-0000001',
        'active' => true,
    ]);

    $program = Program::firstOrCreate(
        ['slug' => 'little-strikers'],
        [
            'name' => 'Little Strikers',
            'enrollment_fee' => 50.00,
            'active' => true,
        ]
    );

    $course = Course::create([
        'title' => 'Baby - Lunes',
        'program_id' => $program->id,
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
        'user_id' => $parent->id,
        'name' => 'Test Child',
        'birthdate' => now()->subYears(2)->format('Y-m-d'),
    ]);

    $enrollment = Enrollment::create([
        'student_id' => $this->student->id,
        'program_id' => $program->id,
        'parent_id' => $parent->id,
        'status' => 'active',
        'payment_method' => 'manual',
        'payment_status' => 'pending',
        'terms_accepted' => true,
    ]);

    $enrollment->courses()->sync([$course->id]);
});

test('students show carga enrollments con cursos y sedes sin errores', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('students.show', $this->student));

    $response->assertOk();

    // Verificar que la respuesta contiene datos del estudiante
    $response->assertSee($this->student->name);

    // Verificar que se cargaron las relaciones correctamente
    $student = Student::with([
        'enrollments.courses.branch',
    ])->find($this->student->id);

    expect($student->enrollments)->toHaveCount(1);
    expect($student->enrollments->first()->courses)->toHaveCount(1);
    expect($student->enrollments->first()->courses->first()->branch)->not->toBeNull();
    expect($student->enrollments->first()->courses->first()->branch->name)->toBe('SEDE SAN LUIS');
});

test('student list index no da error 500', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('students.index'));

    $response->assertOk();
    $response->assertSee($this->student->name);
});
