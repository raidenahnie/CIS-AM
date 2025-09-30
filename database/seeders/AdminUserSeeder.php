<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin user already exists
        $adminExists = User::where('email', 'admin@cis-am.com')->exists();
        
        if (!$adminExists) {
            User::create([
                'name' => 'System Administrator',
                'email' => 'admin@cis-am.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);

            $this->command->info('Admin user created successfully!');
            $this->command->info('Email: admin@cis-am.com');
            $this->command->info('Password: admin123');
            $this->command->warn('Please change the password after first login for security.');
        } else {
            $this->command->info('Admin user already exists.');
        }
    }
}
