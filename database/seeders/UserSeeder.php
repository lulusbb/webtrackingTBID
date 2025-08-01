<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Marketing User',
            'email' => 'marketing@example.com',
            'password' => Hash::make('password'),
            'role' => 'marketing',
        ]);

        User::create([
            'name' => 'Studio User',
            'email' => 'studio@example.com',
            'password' => Hash::make('password'),
            'role' => 'studio',
        ]);

        User::create([
            'name' => 'Project User',
            'email' => 'project@example.com',
            'password' => Hash::make('password'),
            'role' => 'project',
        ]);
    }
}
