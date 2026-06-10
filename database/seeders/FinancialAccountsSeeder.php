<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class FinancialAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'slug' => 'caja-chica-usd',
                'name' => 'Caja Chica USD',
                'type' => 'cash',
                'currency' => 'USD',
                'active' => true,
                'meta' => ['source' => 'financial_test_seeders'],
            ],
            [
                'slug' => 'banco-principal-usd',
                'name' => 'Banco Principal USD',
                'type' => 'bank',
                'currency' => 'USD',
                'active' => true,
                'meta' => ['source' => 'financial_test_seeders'],
            ],
            [
                'slug' => 'zelle-usd',
                'name' => 'Zelle USD',
                'type' => 'transfer',
                'currency' => 'USD',
                'active' => true,
                'meta' => ['source' => 'financial_test_seeders'],
            ],
        ];

        foreach ($accounts as $data) {
            Account::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'type' => $data['type'],
                    'currency' => $data['currency'],
                    'active' => $data['active'],
                    'meta' => $data['meta'],
                ]
            );
        }
    }
}
