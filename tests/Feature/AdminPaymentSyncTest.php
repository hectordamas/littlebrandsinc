<?php

use App\Models\{Account, AccountReceivable, Branch, Course, Enrollment, EnrollmentInstallment, Program, Student, User};
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'Administrador']);
    $this->parent = User::factory()->create(['role' => 'Padre']);

    $this->branch = Branch::create([
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

    $this->course = Course::create([
        'title' => 'Baby - Lunes',
        'program_id' => $this->program->id,
        'branch_id' => $this->branch->id,
        'min_age' => 1.5,
        'max_age' => 3.0,
        'capacity' => 15,
        'monthly_fee' => 40.00,
        'start_date' => now()->startOfMonth(),
        'end_date' => now()->addMonths(1)->endOfMonth(), // 2 months total: Month 1 & Month 2
        'active' => true,
    ]);

    $this->student = Student::create([
        'user_id' => $this->parent->id,
        'name' => 'Test Child',
        'birthdate' => now()->subYears(2)->format('Y-m-d'),
    ]);

    $this->enrollment = Enrollment::create([
        'student_id' => $this->student->id,
        'program_id' => $this->program->id,
        'parent_id' => $this->parent->id,
        'status' => 'active',
        'payment_method' => 'manual',
        'payment_status' => 'pending',
        'terms_accepted' => true,
    ]);

    $this->enrollment->courses()->sync([$this->course->id]);

    // Total receivable: 50 (enrollment fee) + 40 (month 1) + 40 (month 2) = 130
    $this->receivable = AccountReceivable::create([
        'branch_id' => $this->branch->id,
        'enrollment_id' => $this->enrollment->id,
        'title' => 'Inscripción #1 - Test Child (Little Strikers)',
        'amount_total' => 130.00,
        'balance_due' => 130.00,
        'currency' => 'USD',
        'status' => 'pending',
    ]);

    // Installment 1 (Month 1)
    $this->installment1 = EnrollmentInstallment::create([
        'enrollment_id' => $this->enrollment->id,
        'account_receivable_id' => $this->receivable->id,
        'period_year' => (int) now()->year,
        'period_month' => (int) now()->month,
        'due_date' => now()->toDateString(),
        'amount' => 40.00,
        'currency' => 'USD',
        'status' => 'pending',
    ]);

    // Installment 2 (Month 2)
    $this->installment2 = EnrollmentInstallment::create([
        'enrollment_id' => $this->enrollment->id,
        'account_receivable_id' => $this->receivable->id,
        'period_year' => (int) now()->addMonth()->year,
        'period_month' => (int) now()->addMonth()->month,
        'due_date' => now()->addMonth()->toDateString(),
        'amount' => 40.00,
        'currency' => 'USD',
        'status' => 'pending',
    ]);

    $this->account = Account::create([
        'name' => 'Caja Chica',
        'slug' => 'caja-chica',
        'type' => 'cash',
        'currency' => 'USD',
        'active' => true,
    ]);
});

test('abono del administrador en CxC se refleja en las cuotas del padre', function () {
    // 1. Verificar estado inicial: ambas cuotas pendientes
    expect($this->installment1->fresh()->status)->toBe('pending');
    expect($this->installment2->fresh()->status)->toBe('pending');

    // 2. Administrador registra un abono de $90 (cubre matrícula $50 + cuota 1 $40)
    $this->actingAs($this->admin);

    $response = $this->post(route('finance.collections.payments.store', $this->receivable), [
        'account_id' => $this->account->id,
        'amount' => 90.00,
        'payment_date' => now()->toDateString(),
        'notes' => 'Abono inicial',
    ]);

    $response->assertRedirect();

    // 3. Verificar que la CxC se actualizó a 'partial' con balance_due = 40
    $this->receivable->refresh();
    expect($this->receivable->status)->toBe('partial');
    expect((float) $this->receivable->balance_due)->toEqual(40.00);

    // 4. Verificar que la cuota 1 se pagó automáticamente y la cuota 2 sigue pendiente
    expect($this->installment1->fresh()->status)->toBe('paid');
    expect($this->installment1->fresh()->paid_at)->not->toBeNull();
    expect($this->installment2->fresh()->status)->toBe('pending');

    // 5. Administrador registra otro abono de $40 (cubre la cuota 2)
    $response2 = $this->post(route('finance.collections.payments.store', $this->receivable), [
        'account_id' => $this->account->id,
        'amount' => 40.00,
        'payment_date' => now()->toDateString(),
        'notes' => 'Abono final',
    ]);

    $response2->assertRedirect();

    // 6. Verificar que la CxC se actualizó a 'paid' con balance_due = 0
    $this->receivable->refresh();
    expect($this->receivable->status)->toBe('paid');
    expect((float) $this->receivable->balance_due)->toEqual(0.00);

    // 7. Verificar que ambas cuotas están pagadas
    expect($this->installment1->fresh()->status)->toBe('paid');
    expect($this->installment2->fresh()->status)->toBe('paid');
    expect($this->installment2->fresh()->paid_at)->not->toBeNull();
});
