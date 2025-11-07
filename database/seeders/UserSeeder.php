<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@tukangbangun.id'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'marketing@tukangbangun.id'],
            [
                'name' => 'Marketing User',
                'password' => Hash::make('password'),
                'role' => 'marketing',
            ]
        );

        User::updateOrCreate(
            ['email' => 'studio@tukangbangun.id'],
            [
                'name' => 'Studio User',
                'password' => Hash::make('password'),
                'role' => 'studio',
            ]
        );

        User::updateOrCreate(
            ['email' => 'project@tukangbangun.id'],
            [
                'name' => 'Project User',
                'password' => Hash::make('password'),
                'role' => 'project',
            ]
        );

        User::updateOrCreate(
            ['email' => 'ceo@tukangbangun.id'], // kalau sudah ada, update saja
            [
                'name' => 'CEO User',
                'password' => Hash::make('password'),
                'role' => 'ceo',
            ]
        );
    }
}
