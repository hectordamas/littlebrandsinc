<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BranchesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [
            [
                'name' => 'SEDE SAN LUIS',
                'address' => 'San Luis, Caracas',
                'email' => 'sanluis@littlebrandsinc.com',
                'phone' => '+58 424-0000001',
                'active' => true,
                'logo' => null,
            ],
            [
                'name' => 'SEDE LOS CAMPITOS',
                'address' => 'Los Campitos, Caracas',
                'email' => 'campitos@littlebrandsinc.com',
                'phone' => '+58 424-0000002',
                'active' => true,
                'logo' => null,
            ],
            [
                'name' => 'SEDE LOS CHORROS',
                'address' => 'Los Chorros, Caracas',
                'email' => 'chorros@littlebrandsinc.com',
                'phone' => '+58 424-0000003',
                'active' => true,
                'logo' => null,
            ],
        ];

        foreach ($branches as $branchData) {
            $branch = \App\Models\Branch::firstOrNew(['name' => $branchData['name']]);
            $branch->address = $branchData['address'];
            $branch->email = $branchData['email'];
            $branch->phone = $branchData['phone'];
            $branch->active = $branchData['active'];
            $branch->logo = $branchData['logo'];
            $branch->save();
        }

        \App\Models\Branch::query()
            ->whereNotIn('name', array_column($branches, 'name'))
            ->delete();
    }
}
