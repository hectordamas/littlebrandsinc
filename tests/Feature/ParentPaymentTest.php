<?php

use App\Models\{AccountReceivable, Branch, Course, Enrollment, ParentPayment, Program, Student, Transaction, User};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Flujo 4: Pago padre (comprobante) → admin aprueba → se crea Transaction.
 */

beforeEach(function () {
    Storage::fake('public');

    $this->admin = User::factory()->create(['role' => 'Administrador']);
    $this->parent = User::factory()->create(['role' => 'Padre']);

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

    $student = Student::create([
        'user_id' => $this->parent->id,
        'name' => 'Test Child',
        'birthdate' => now()->subYears(2)->format('Y-m-d'),
    ]);

    $this->enrollment = Enrollment::create([
        'student_id' => $student->id,
        'program_id' => $program->id,
        'parent_id' => $this->parent->id,
        'status' => 'active',
        'payment_method' => 'manual',
        'payment_status' => 'pending',
        'terms_accepted' => true,
    ]);

    $this->enrollment->courses()->sync([$course->id]);

    $this->receivable = AccountReceivable::create([
        'branch_id' => $branch->id,
        'enrollment_id' => $this->enrollment->id,
        'title' => 'Inscripción #1 - Little Strikers - Baby - Lunes',
        'amount_total' => 90.00,
        'balance_due' => 90.00,
        'currency' => 'USD',
        'status' => 'pending',
    ]);
});

test('padre sube comprobante de pago y admin lo aprueba', function () {
    // Padre sube comprobante
    $this->actingAs($this->parent);

    $file = UploadedFile::fake()->image('comprobante.jpg');

    $response = $this->post(route('parent.payments.store'), [
        'account_receivable_id' => $this->receivable->id,
        'amount' => 90.00,
        'reference' => 'Pago marzo 2026',
        'payment_receipt' => $file,
    ]);

    $response->assertRedirect();

    $payment = ParentPayment::first();
    expect($payment)->not->toBeNull();
    expect($payment->status)->toBe('pending');
    expect((float) $payment->amount)->toEqual(90.00);
    expect($payment->account_receivable_id)->toBe($this->receivable->id);

    // Admin aprueba el pago
    $this->actingAs($this->admin);

    $response = $this->patch(route('finance.parent-payments.approve', $payment));

    $response->assertRedirect();

    $payment->refresh();
    expect($payment->status)->toBe('approved');
    expect($payment->approved_by)->toBe($this->admin->id);

    // Verificar que se creó una Transaction
    $transaction = Transaction::where('account_receivable_id', $this->receivable->id)
        ->where('type', 'income')
        ->first();

    expect($transaction)->not->toBeNull();
    expect((float) $transaction->amount)->toEqual(90.00);
});

test('admin rechaza comprobante de pago', function () {
    $this->actingAs($this->parent);

    $file = UploadedFile::fake()->image('comprobante.jpg');

    $response = $this->post(route('parent.payments.store'), [
        'account_receivable_id' => $this->receivable->id,
        'amount' => 90.00,
        'payment_receipt' => $file,
    ]);

    $response->assertStatus(302);

    $payment = ParentPayment::first();
    expect($payment)->not->toBeNull();

    // Admin rechaza
    $this->actingAs($this->admin);

    $rejectResponse = $this->patch(route('finance.parent-payments.reject', ['payment' => $payment->id]), [
        'rejected_reason' => 'Comprobante ilegible',
    ]);

    $rejectResponse->assertRedirect();

    $payment->refresh();
    expect($payment->status)->toBe('rejected');
    expect($payment->rejected_reason)->toBe('Comprobante ilegible');
});
