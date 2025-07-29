<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserSalary;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => 'password', // Your admin password
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Create Dummy User Salary Records (in 'user_salaries' table)
        UserSalary::firstOrCreate(
            ['email' => 'john.doe@example.com'],
            [
                'name' => 'John Doe',
                'salary_local_currency' => 75000.00,
                'salary_euros' => 18000.00,
                'commission' => 500.00,
            ]
        );

        UserSalary::firstOrCreate(
            ['email' => 'jane.smith@example.com'],
            [
                'name' => 'Jane Smith',
                'salary_local_currency' => 82000.00,
                'salary_euros' => 20000.00,
                'commission' => 750.00,
            ]
        );
    }
}
