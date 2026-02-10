<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@isuremedia.com'],
            [
                'first_name' => 'System',
                'last_name' => 'Admin',
                'role' => 'admin',
                'password' => Hash::make('Password@123'),
                'status' => 1,
            ]
        );

        $this->command->info('Admin user created/updated successfully: admin@isuremedia.com / Password@123');
    }
}
