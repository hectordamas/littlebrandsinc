<?php

use App\Models\{Branch, Course, LBClass, Program, User};
use Carbon\Carbon;

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

    $this->program = Program::create([
        'name' => 'Soccer Program',
        'slug' => 'soccer-program',
        'description' => 'Test program',
        'enrollment_fee' => 50.00,
        'active' => true,
    ]);

    $this->coach = User::factory()->create([
        'role' => 'Coach',
    ]);
});

test('al acortar el rango de fechas del curso se eliminan las clases fuera del rango', function () {
    $this->actingAs($this->admin);

    // Un curso del 1 al 30 de Junio 2026
    $course = Course::create([
        'title' => 'Curso de Prueba',
        'program_id' => $this->program->id,
        'branch_id' => $this->branch->id,
        'start_date' => '2026-06-01',
        'end_date' => '2026-06-30',
        'capacity' => 10,
        'active' => true,
    ]);

    $course->coaches()->attach($this->coach->id);

    // Creamos clases en varias fechas
    $class1 = LBClass::create([
        'course_id' => $course->id,
        'branch_id' => $this->branch->id,
        'date' => '2026-06-05', // Dentro
        'start_time' => '10:00:00',
        'end_time' => '11:00:00',
        'coach_id' => $this->coach->id,
    ]);

    $class2 = LBClass::create([
        'course_id' => $course->id,
        'branch_id' => $this->branch->id,
        'date' => '2026-06-15', // Dentro
        'start_time' => '10:00:00',
        'end_time' => '11:00:00',
        'coach_id' => $this->coach->id,
    ]);

    $class3 = LBClass::create([
        'course_id' => $course->id,
        'branch_id' => $this->branch->id,
        'date' => '2026-06-28', // Quedará FUERA
        'start_time' => '10:00:00',
        'end_time' => '11:00:00',
        'coach_id' => $this->coach->id,
    ]);

    // Modificamos el rango a 2026-06-01 hasta 2026-06-20
    $response = $this->put(route('courses.update', $course->id), [
        'title' => 'Curso de Prueba Editado',
        'program_id' => $this->program->id,
        'description' => 'Test',
        'min_age' => 1.5,
        'max_age' => 5.0,
        'capacity' => 10,
        'price' => 100,
        'monthly_fee' => 50,
        'start_date' => '2026-06-01',
        'end_date' => '2026-06-20',
        'branch_id' => $this->branch->id,
        'coach_ids' => [$this->coach->id],
        'active' => 1,
    ]);

    $response->assertRedirect();

    // Verificamos que las clases 1 y 2 sigan existiendo
    expect(LBClass::find($class1->id))->not->toBeNull();
    expect(LBClass::find($class2->id))->not->toBeNull();

    // Verificamos que la clase 3 haya sido eliminada
    expect(LBClass::find($class3->id))->toBeNull();
});

test('al expandir el rango de fechas con auto_extend se generan nuevas clases basadas en el patron de las existentes', function () {
    $this->actingAs($this->admin);

    // Un curso del 1 al 15 de Junio 2026.
    // El 2026-06-06 es un Sábado.
    // El 2026-06-13 es un Sábado.
    // El 2026-06-20 es un Sábado (fuera de rango inicialmente).
    // El 2026-06-27 es un Sábado (fuera de rango inicialmente).
    $course = Course::create([
        'title' => 'Curso Recurrente',
        'program_id' => $this->program->id,
        'branch_id' => $this->branch->id,
        'start_date' => '2026-06-01',
        'end_date' => '2026-06-15',
        'capacity' => 10,
        'active' => true,
    ]);

    $course->coaches()->attach($this->coach->id);

    // Programamos clase el sábado 13 de junio de 10:00 a 11:00
    LBClass::create([
        'course_id' => $course->id,
        'branch_id' => $this->branch->id,
        'date' => '2026-06-13',
        'start_time' => '10:00:00',
        'end_time' => '11:00:00',
        'coach_id' => $this->coach->id,
    ]);

    // Extendemos el rango al 30 de Junio 2026 con auto_extend_classes = true
    $response = $this->put(route('courses.update', $course->id), [
        'title' => 'Curso Recurrente',
        'program_id' => $this->program->id,
        'description' => 'Test',
        'min_age' => 1.5,
        'max_age' => 5.0,
        'capacity' => 10,
        'price' => 100,
        'monthly_fee' => 50,
        'start_date' => '2026-06-01',
        'end_date' => '2026-06-30',
        'branch_id' => $this->branch->id,
        'coach_ids' => [$this->coach->id],
        'active' => 1,
        'auto_extend_classes' => 1,
    ]);

    $response->assertRedirect();

    // Sábados en ese rango:
    // 06 de junio (debería crearse porque no había clase programada allí, y coincide con patrón sábado 10:00-11:00)
    // 13 de junio (ya existía)
    // 20 de junio (nuevo en rango extendido: debería crearse)
    // 27 de junio (nuevo en rango extendido: debería crearse)
    $datesWithClasses = LBClass::where('course_id', $course->id)->get()->pluck('date')->map(fn($d) => $d->toDateString())->toArray();
    expect($datesWithClasses)->toContain('2026-06-06');
    expect($datesWithClasses)->toContain('2026-06-13');
    expect($datesWithClasses)->toContain('2026-06-20');
    expect($datesWithClasses)->toContain('2026-06-27');
    expect(count($datesWithClasses))->toBe(4);
});

test('al expandir el rango de fechas sin auto_extend no se generan nuevas clases', function () {
    $this->actingAs($this->admin);

    $course = Course::create([
        'title' => 'Curso Recurrente 2',
        'program_id' => $this->program->id,
        'branch_id' => $this->branch->id,
        'start_date' => '2026-06-01',
        'end_date' => '2026-06-15',
        'capacity' => 10,
        'active' => true,
    ]);

    $course->coaches()->attach($this->coach->id);

    // Programamos clase el sábado 13 de junio
    LBClass::create([
        'course_id' => $course->id,
        'branch_id' => $this->branch->id,
        'date' => '2026-06-13',
        'start_time' => '10:00:00',
        'end_time' => '11:00:00',
        'coach_id' => $this->coach->id,
    ]);

    // Extendemos el rango al 30 de Junio 2026 con auto_extend_classes = false
    $response = $this->put(route('courses.update', $course->id), [
        'title' => 'Curso Recurrente 2',
        'program_id' => $this->program->id,
        'description' => 'Test',
        'min_age' => 1.5,
        'max_age' => 5.0,
        'capacity' => 10,
        'price' => 100,
        'monthly_fee' => 50,
        'start_date' => '2026-06-01',
        'end_date' => '2026-06-30',
        'branch_id' => $this->branch->id,
        'coach_ids' => [$this->coach->id],
        'active' => 1,
        'auto_extend_classes' => 0, // Desactivado
    ]);

    $response->assertRedirect();

    // Solo debe existir la clase original del 13 de junio
    $datesWithClasses = LBClass::where('course_id', $course->id)->get()->pluck('date')->map(fn($d) => $d->toDateString())->toArray();
    expect($datesWithClasses)->toContain('2026-06-13');
    expect(count($datesWithClasses))->toBe(1);
});
