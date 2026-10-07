<?php

namespace Database\Seeders;

use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create(['name' => 'Membership Admin', 'email' => 'admin@example.com', 'password' => 'ChangeMe123!', 'role' => 'admin', 'active' => true]);
        User::create(['name' => 'Demo Member', 'email' => 'member@example.com', 'password' => 'ChangeMe123!', 'role' => 'member', 'active' => true, 'member_number' => 'MEM-DEMO001', 'joined_on' => today()]);
        MembershipPlan::create(['name' => 'Monthly', 'description' => 'One month of access.', 'duration_months' => 1, 'price' => 35, 'active' => true]);
        MembershipPlan::create(['name' => 'Annual', 'description' => 'Twelve months of access.', 'duration_months' => 12, 'price' => 350, 'active' => true]);
    }
}
