<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@aftereight.com',
            'password' => Hash::make('password'),
            'role' => Role::ADMIN,
            'phone' => '+1234567890',
            'email_verified_at' => now(),
        ]);

        // Create operator user
        User::create([
            'name' => 'Operator User',
            'email' => 'operator@aftereight.com',
            'password' => Hash::make('password'),
            'role' => Role::OPERATOR,
            'phone' => '+1234567891',
            'email_verified_at' => now(),
        ]);

        // Create test regular users
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'role' => Role::USER,
            'phone' => '+1234567892',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password'),
            'role' => Role::USER,
            'phone' => '+1234567893',
            'email_verified_at' => now(),
        ]);

        // Create additional test users
        User::factory(10)->create([
            'role' => Role::USER,
        ]);
    }
}
