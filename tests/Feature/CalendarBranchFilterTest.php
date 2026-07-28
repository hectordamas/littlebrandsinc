<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Course;
use App\Models\LBClass;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarBranchFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_calendar_events_filters_by_branch_id(): void
    {
        $admin = User::factory()->create(['role' => 'Administrador']);
        $program = Program::create(['name' => 'Programa Test', 'slug' => 'programa-test', 'active' => true]);

        $branch1 = Branch::create(['name' => 'Sede A']);
        $branch2 = Branch::create(['name' => 'Sede B']);

        $course1 = Course::create([
            'title' => 'Curso Sede A',
            'program_id' => $program->id,
            'branch_id' => $branch1->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'active' => true,
        ]);

        $course2 = Course::create([
            'title' => 'Curso Sede B',
            'program_id' => $program->id,
            'branch_id' => $branch2->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'active' => true,
        ]);

        $class1 = LBClass::create([
            'course_id' => $course1->id,
            'branch_id' => $branch1->id,
            'date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
        ]);

        $class2 = LBClass::create([
            'course_id' => $course2->id,
            'branch_id' => $branch2->id,
            'date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
        ]);

        // Consulta sin filtro de sede
        $responseAll = $this->actingAs($admin)
            ->getJson(route('calendar.events', [
                'start' => now()->subDay()->toDateString(),
                'end' => now()->addDay()->toDateString(),
            ]));

        $responseAll->assertOk()->assertJsonCount(2);

        // Consulta filtrando por Sede 1
        $responseBranch1 = $this->actingAs($admin)
            ->getJson(route('calendar.events', [
                'branch_id' => $branch1->id,
                'start' => now()->subDay()->toDateString(),
                'end' => now()->addDay()->toDateString(),
            ]));

        $responseBranch1->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['title' => 'Curso Sede A']);

        // Consulta filtrando por Sede 2
        $responseBranch2 = $this->actingAs($admin)
            ->getJson(route('calendar.events', [
                'branch_id' => $branch2->id,
                'start' => now()->subDay()->toDateString(),
                'end' => now()->addDay()->toDateString(),
            ]));

        $responseBranch2->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['title' => 'Curso Sede B']);
    }
}
