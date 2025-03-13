<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        if (env('ADMIN_EMAIL') && env('ADMIN_PASSWORD')) {
            User::firstOrCreate(
                ['email' => env('ADMIN_EMAIL')],
                [
                    'name' => env('ADMIN_NAME', 'Admin'),
                    'email' => env('ADMIN_EMAIL'),
                    'password' => Hash::make(env('ADMIN_PASSWORD')),
                    'role' => 2
                ]
            );
            $this->command->info('Admin user created successfully');
        } else {
            $this->command->error('Admin credentials not found in .env');
        }
    }
}