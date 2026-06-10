<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UsersSeeder::class);
        $this->call(BranchesSeeder::class);
        $this->call(ProgramsCoursesSeeder::class);
        $this->call(FinancialAccountsSeeder::class);
        // Deshabilitado para producción
        // $this->call(FinancialMovementsSeeder::class);
        $this->call(ExcelImportSeeder::class);
    }
}
