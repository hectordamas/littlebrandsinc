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

        $additionalBranches = ['Volleyball', 'Baseball', 'Hockey', 'Golf', 'Running'];
        $additionalBranches = [
            [
            'name' => 'Volleyball',
            'address' => '123 Volleyball St',
            'email' => 'volleyball@littlebrands.com',
            'phone' => '555-0101',
            'active' => true,
            'logo' => null,
            ],
            [
            'name' => 'Baseball',
            'address' => '456 Baseball Ave',
            'email' => 'baseball@littlebrands.com',
            'phone' => '555-0102',
            'active' => true,
            'logo' => null,
            ],
            [
            'name' => 'Hockey',
            'address' => '789 Hockey Blvd',
            'email' => 'hockey@littlebrands.com',
            'phone' => '555-0103',
            'active' => true,
            'logo' => null,
            ],
            [
            'name' => 'Golf',
            'address' => '101 Golf Ln',
            'email' => 'golf@littlebrands.com',
            'phone' => '555-0104',
            'active' => true,
            'logo' => null,
            ],
            [
            'name' => 'Running',
            'address' => '202 Running Rd',
            'email' => 'running@littlebrands.com',
            'phone' => '555-0105',
            'active' => true,
            'logo' => null,
            ],
        ];

        foreach ($additionalBranches as $branchData) {
            $branch = \App\Models\Branch::firstOrNew(['name' => $branchData['name']]);
            $branch->address = $branchData['address'];
            $branch->email = $branchData['email'];
            $branch->phone = $branchData['phone'];
            $branch->active = $branchData['active'];
            $branch->logo = $branchData['logo'];
            $branch->save();
        }
    }
}
