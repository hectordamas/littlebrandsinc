<?php

use App\Models\BirthdayInquiry;
use App\Models\User;

beforeEach(function () {
    BirthdayInquiry::query()->delete();
    User::query()->delete();

    $this->admin = User::factory()->create(['role' => 'Administrador']);
    $this->parent = User::factory()->create(['role' => 'Padre']);
});

test('public user can submit a birthday inquiry and gets stored in database', function () {
    $payload = [
        'representative_name' => 'John Doe',
        'phone' => '+58 412 9999999',
        'email' => 'johndoe@example.com',
        'age_to_celebrate' => 5,
        'event_date' => now()->addDays(10)->format('Y-m-d'),
        'start_time' => '4:00 PM',
        'location_type' => 'sede_los_campitos',
        'estimated_children' => 20,
        'guest_age_range' => '3 a 6 años',
        'program_interest' => 'strikers',
        'additional_services' => ['Torta', 'Piñata'],
        'comments' => 'Por favor que sea con decoración de fútbol.',
    ];

    $response = $this->post(route('birthdays.store'), $payload);

    $response->assertRedirect();
    $this->assertDatabaseHas('birthday_inquiries', [
        'representative_name' => 'John Doe',
        'email' => 'johndoe@example.com',
        'age_to_celebrate' => 5,
        'location_type' => 'sede_los_campitos',
    ]);

    // Check JSON response for AJAX
    $jsonResponse = $this->postJson(route('birthdays.store'), $payload);
    $jsonResponse->assertOk();
    $jsonResponse->assertJsonFragment([
        'success' => true,
        'message' => '¡Tu solicitud de cumpleaños ha sido enviada con éxito! Nuestro equipo se pondrá en contacto contigo muy pronto.',
    ]);
});

test('submitting birthday inquiry fails validation with missing fields', function () {
    $response = $this->postJson(route('birthdays.store'), []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['representative_name', 'phone', 'email', 'age_to_celebrate', 'event_date', 'start_time', 'location_type', 'estimated_children', 'guest_age_range', 'program_interest']);
});

test('admin can access birthdays index and see inquiries', function () {
    $inquiry = BirthdayInquiry::create([
        'representative_name' => 'Alice Smith',
        'phone' => '+58 412 1111111',
        'email' => 'alice@example.com',
        'age_to_celebrate' => 6,
        'event_date' => now()->addDays(15)->format('Y-m-d'),
        'start_time' => '3:00 PM',
        'location_type' => 'sede_san_luis',
        'estimated_children' => 15,
        'guest_age_range' => '4 a 7 años',
        'program_interest' => 'paddlers',
        'additional_services' => ['Torta'],
        'comments' => 'Algún comentario.',
    ]);

    // Guest cannot access
    $this->get(route('birthdays.index'))->assertRedirect(route('login'));

    // Parent cannot access
    $this->actingAs($this->parent);
    $this->get(route('birthdays.index'))->assertStatus(403);

    // Admin can access
    $this->actingAs($this->admin);
    $response = $this->get(route('birthdays.index'));
    $response->assertOk();
    $response->assertSee('Alice Smith');
});

test('admin can mark birthday inquiry as read and unread', function () {
    $inquiry = BirthdayInquiry::create([
        'representative_name' => 'Bob Miller',
        'phone' => '+58 412 2222222',
        'email' => 'bob@example.com',
        'age_to_celebrate' => 7,
        'event_date' => now()->addDays(20)->format('Y-m-d'),
        'start_time' => '5:00 PM',
        'location_type' => 'other',
        'event_location' => 'Salón de fiesta Quinta Altamira',
        'estimated_children' => 25,
        'guest_age_range' => '5 a 8 años',
        'program_interest' => 'strikers',
        'additional_services' => [],
        'comments' => '',
    ]);

    expect($inquiry->read_at)->toBeNull();

    $this->actingAs($this->admin);

    // Mark as read
    $response = $this->patchJson(route('birthdays.read', $inquiry));
    $response->assertOk();
    
    $inquiry->refresh();
    expect($inquiry->read_at)->not->toBeNull();

    // Mark as unread
    $response = $this->patchJson(route('birthdays.unread', $inquiry));
    $response->assertOk();

    $inquiry->refresh();
    expect($inquiry->read_at)->toBeNull();
});
