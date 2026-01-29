<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update the role column to include 'manager'
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'student', 'manager') NOT NULL DEFAULT 'student'");

        $manager = User::create([
            'first_name' => 'Manager',
            'last_name' => 'User',
            'email' => 'manager@isuremedia.com',
            'password' => Hash::make('Password@123'),
            'role' => 'manager',
            'status' => 1,
            'is_blocked' => false,
        ]);

        echo "✓ Manager user created (manager@isuremedia.com)\n";
    }
}
