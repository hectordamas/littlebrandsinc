<?php

use App\Models\{AccountReceivable, Branch, Course, Enrollment, Program, Student, User};
use Illuminate\Support\Facades\Artisan;

/**
 * Flujo 5: FinanceCollections — syncEnrollmentReceivables genera
 * cuentas por cobrar correctas con programa + cursos.
 */

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'Administrador']);

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
            'enrollment_fee' => 50.00,
            'active' => true,
        ]
    );

    $this->course1 = Course::create([
        'title' => 'Baby - Lunes',
        'program_id' => $this->program->id,
        'branch_id' => $branch->id,
        'min_age' => 1.5,
        'max_age' => 3.0,
        'capacity' => 15,
        'monthly_fee' => 40.00,
        'start_date' => now()->startOfMonth(),
        'end_date' => now()->addMonths(2)->endOfMonth(),
        'active' => true,
    ]);

    $this->course2 = Course::create([
        'title' => 'Mini - Miércoles',
        'program_id' => $this->program->id,
        'branch_id' => $branch->id,
        'min_age' => 2.0,
        'max_age' => 4.0,
        'capacity' => 15,
        'monthly_fee' => 45.00,
        'start_date' => now()->startOfMonth(),
        'end_date' => now()->addMonths(2)->endOfMonth(),
        'active' => true,
    ]);

    $parent = User::factory()->create(['role' => 'Padre']);
    $student = Student::create([
        'user_id' => $parent->id,
        'name' => 'Test Child',
        'birthdate' => now()->subYears(2)->format('Y-m-d'),
    ]);

    // Crear enrollment con programa y 2 cursos (payment_status: pending debe generar receivable)
    $this->enrollment = Enrollment::create([
        'student_id' => $student->id,
        'program_id' => $this->program->id,
        'parent_id' => $parent->id,
        'status' => 'active',
        'payment_method' => 'manual',
        'payment_status' => 'pending',
        'terms_accepted' => true,
    ]);

    $this->enrollment->courses()->sync([$this->course1->id, $this->course2->id]);
});

test('syncEnrollmentReceivables genera cuenta por cobrar correcta', function () {
    $this->actingAs($this->admin);

    // Visitar colecciones dispara syncEnrollmentReceivables
    $response = $this->get(route('finance.collections'));

    $response->assertOk();

    // Verificar receivable creado
    $receivable = AccountReceivable::where('enrollment_id', $this->enrollment->id)->first();
    expect($receivable)->not->toBeNull();
    expect($receivable->status)->toBe('pending');

    // Calcular total esperado: enrollment_fee (50) + course1.monthly_fee*3meses (40*3) + course2.monthly_fee*3meses (45*3)
    // = 50 + 120 + 135 = 305
    $expectedTotal = 50.00 + (40.00 * 3) + (45.00 * 3);
    expect((float) $receivable->amount_total)->toBe($expectedTotal);
    expect((float) $receivable->balance_due)->toBe($expectedTotal);

    // Verificar título incluye programa y estudiante
    expect($receivable->title)->toContain('Little Strikers');
    expect($receivable->title)->toContain('Test Child');
});

test('enrollment free trial no genera cuenta por cobrar', function () {
    $this->actingAs($this->admin);

    // Free trial no debe tener receivable
    $this->enrollment->update(['is_free_trial' => true]);

    $this->get(route('finance.collections'));

    $receivable = AccountReceivable::where('enrollment_id', $this->enrollment->id)->first();
    expect($receivable)->toBeNull();
});
