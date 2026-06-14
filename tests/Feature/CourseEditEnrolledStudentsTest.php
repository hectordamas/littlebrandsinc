<?php

use App\Models\{Branch, Course, Program, User, Student, Enrollment};

beforeEach(function () {
    User::query()->delete();
    Course::query()->delete();
    Branch::query()->delete();
    Program::query()->delete();
    Student::query()->delete();
    Enrollment::query()->delete();

    $this->admin = User::factory()->create(['role' => 'Administrador']);

    $this->branch = Branch::create([
        'name' => 'SEDE SAN LUIS',
        'address' => 'San Luis',
        'email' => 'sanluis@test.com',
        'phone' => '+58 424-0000003',
        'active' => true,
    ]);

    $this->program = Program::create([
        'name' => 'Little Strikers',
        'slug' => 'little-strikers',
        'description' => 'Fútbol infantil',
        'enrollment_fee' => 50.00,
        'active' => true,
    ]);

    $this->course = Course::create([
        'title' => 'Little Strikers Baby - Lunes',
        'program_id' => $this->program->id,
        'branch_id' => $this->branch->id,
        'min_age' => 1.5,
        'max_age' => 2.0,
        'capacity' => 12,
        'monthly_fee' => 95.00,
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonths(6)->toDateString(),
        'active' => true,
    ]);

    $this->parent = User::factory()->create([
        'role' => 'Padre',
        'name' => 'Jane Parent',
        'email' => 'parent@example.com',
        'dial_code' => '+58',
        'whatsapp' => '4129999999',
    ]);

    $this->student = Student::create([
        'name' => 'Baby Student',
        'birthdate' => '2024-01-01',
        'active' => true,
        'user_id' => $this->parent->id,
    ]);

    $this->enrollment = Enrollment::create([
        'student_id' => $this->student->id,
        'parent_id' => $this->parent->id,
        'program_id' => $this->program->id,
        'status' => 'active',
        'payment_status' => 'paid',
        'is_free_trial' => false,
        'terms_accepted' => true,
    ]);

    $this->course->enrollments()->attach($this->enrollment->id);
});

test('admin can see course edit page with enrolled students list and payment status', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('courses.edit', $this->course->id));

    $response->assertOk();
    $response->assertSee('Baby Student');
    $response->assertSee('Jane Parent');
    $response->assertSee('parent@example.com');
    $response->assertSee('+58 4129999999');
    $response->assertSee('Pagado');
});
