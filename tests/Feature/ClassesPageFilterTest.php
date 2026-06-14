<?php

use App\Models\{Branch, Course, Program, LBClass};
use Carbon\Carbon;

beforeEach(function () {
    // Clean up programs and branches if any exist
    Program::query()->delete();
    Branch::query()->delete();
    Course::query()->delete();

    // Create programs
    $this->strikers = Program::create([
        'name' => 'Little Strikers',
        'slug' => 'little-strikers',
        'description' => 'Fútbol infantil',
        'enrollment_fee' => 50.00,
        'active' => true,
    ]);

    // Create Los Chorros branch
    $this->chorros = Branch::create([
        'name' => 'SEDE LOS CHORROS',
        'address' => 'Los Chorros',
        'email' => 'chorros@test.com',
        'phone' => '+58 424-0000002',
        'active' => true,
    ]);

    // Create another branch (e.g. San Luis)
    $this->sanLuis = Branch::create([
        'name' => 'SEDE SAN LUIS',
        'address' => 'San Luis',
        'email' => 'sanluis@test.com',
        'phone' => '+58 424-0000003',
        'active' => true,
    ]);

    // Create courses for Los Chorros: one on Saturday and one on Monday
    $this->saturdayCourse = Course::create([
        'title' => 'Little Strikers Baby - Sábado',
        'program_id' => $this->strikers->id,
        'branch_id' => $this->chorros->id,
        'min_age' => 1.5,
        'max_age' => 2.0,
        'capacity' => 12,
        'monthly_fee' => 95.00,
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonths(6)->toDateString(),
        'active' => true,
    ]);

    $this->mondayCourse = Course::create([
        'title' => 'Little Strikers Baby - Lunes',
        'program_id' => $this->strikers->id,
        'branch_id' => $this->chorros->id,
        'min_age' => 1.5,
        'max_age' => 2.0,
        'capacity' => 12,
        'monthly_fee' => 95.00,
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonths(6)->toDateString(),
        'active' => true,
    ]);

    // Create courses for San Luis: one on Monday
    $this->sanLuisCourse = Course::create([
        'title' => 'Little Strikers Baby - Lunes San Luis',
        'program_id' => $this->strikers->id,
        'branch_id' => $this->sanLuis->id,
        'min_age' => 1.5,
        'max_age' => 2.0,
        'capacity' => 12,
        'monthly_fee' => 95.00,
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonths(6)->toDateString(),
        'active' => true,
    ]);

    // Seed classes for the courses so they have valid schedules
    // Saturday course classes (weekday 6)
    $saturday = Carbon::parse('next Saturday');
    LBClass::create([
        'course_id' => $this->saturdayCourse->id,
        'branch_id' => $this->chorros->id,
        'date' => $saturday->toDateString(),
        'start_time' => '09:00:00',
        'end_time' => '10:00:00',
    ]);

    // Monday course classes (weekday 1)
    $monday = Carbon::parse('next Monday');
    LBClass::create([
        'course_id' => $this->mondayCourse->id,
        'branch_id' => $this->chorros->id,
        'date' => $monday->toDateString(),
        'start_time' => '16:00:00',
        'end_time' => '17:00:00',
    ]);

    // San Luis course classes (weekday 1)
    LBClass::create([
        'course_id' => $this->sanLuisCourse->id,
        'branch_id' => $this->sanLuis->id,
        'date' => $monday->toDateString(),
        'start_time' => '16:00:00',
        'end_time' => '17:00:00',
    ]);
});

test('classes page loads successfully and filters out non-Saturday classes for Los Chorros', function () {
    $response = $this->get(route('classes.index'));

    $response->assertOk();

    // The schedules passed to the view should not contain the Monday course for Los Chorros
    $strikersSchedules = $response->viewData('strikersSchedules');

    // Find the branch data in the schedules array
    $chorrosBranchData = collect($strikersSchedules)->first(fn ($b) => $b['branch'] === 'SEDE LOS CHORROS');
    $sanLuisBranchData = collect($strikersSchedules)->first(fn ($b) => $b['branch'] === 'SEDE SAN LUIS');

    expect($chorrosBranchData)->not->toBeNull();
    expect($sanLuisBranchData)->not->toBeNull();

    // For Los Chorros, only the Saturday course should remain
    $chorrosItems = $chorrosBranchData['items'];
    expect($chorrosItems)->toHaveCount(1);
    expect($chorrosItems[0]['title'])->toBe('Little Strikers Baby - Sábado');

    // For San Luis, the Monday course should remain
    $sanLuisItems = $sanLuisBranchData['items'];
    expect($sanLuisItems)->toHaveCount(1);
    expect($sanLuisItems[0]['title'])->toBe('Little Strikers Baby - Lunes San Luis');
});
