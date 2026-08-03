<?php

use App\Mail\LandingContactConfirmationMailable;
use App\Mail\LandingContactMailable;
use App\Models\Branch;
use App\Models\ContactMessage;
use App\Models\Program;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    ContactMessage::query()->delete();
});

test('public user submitting contact form queues email to admin and user and saves to database', function () {
    Mail::fake();

    $program = Program::query()->firstOrCreate(['name' => 'Little Strikers Test', 'slug' => 'little-strikers-test'], ['active' => true]);
    $branch = Branch::query()->firstOrCreate(['name' => 'Sede San Luis Test']);

    $payload = [
        'representative_name' => 'Carlos Rodriguez',
        'child_name' => 'Carlitos',
        'child_age' => 4,
        'program_id' => $program->id,
        'branch_id' => $branch->id,
        'phone' => '+58 412 1112233',
        'email' => 'carlos@example.com',
        'comment' => 'Deseo información sobre las clases de fútbol.',
    ];

    $response = $this->post(route('landing.contact'), $payload);

    $response->assertRedirect();
    $this->assertDatabaseHas('contact_messages', [
        'representative_name' => 'Carlos Rodriguez',
        'email' => 'carlos@example.com',
    ]);

    Mail::assertQueued(LandingContactMailable::class, function ($mail) {
        return $mail->payload['representative_name'] === 'Carlos Rodriguez';
    });

    Mail::assertQueued(LandingContactConfirmationMailable::class, function ($mail) {
        return $mail->hasTo('carlos@example.com');
    });
});

test('contact form submission persists to database inbox even if mail dispatch throws exception', function () {
    Mail::shouldReceive('to')->andThrow(new \Exception('SMTP connection error simulation'));

    $program = Program::query()->firstOrCreate(['name' => 'Little Strikers Test 2', 'slug' => 'little-strikers-test-2'], ['active' => true]);
    $branch = Branch::query()->firstOrCreate(['name' => 'Sede Los Campitos Test']);

    $payload = [
        'representative_name' => 'Ana Gomez',
        'child_name' => 'Sofia',
        'child_age' => 3,
        'program_id' => $program->id,
        'branch_id' => $branch->id,
        'phone' => '+58 414 4445566',
        'email' => 'ana@example.com',
        'comment' => 'Consulta con error simulado de mail.',
    ];

    $response = $this->post(route('landing.contact'), $payload);

    // Submission still succeeds and redirects with success
    $response->assertRedirect();
    $this->assertDatabaseHas('contact_messages', [
        'representative_name' => 'Ana Gomez',
        'email' => 'ana@example.com',
    ]);
});
