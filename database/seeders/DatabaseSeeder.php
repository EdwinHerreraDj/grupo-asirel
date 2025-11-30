<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::factory()->create([
            'name' => 'Super Alminares',
            'email' => 'super@alminares.es',
            'email_verified_at' => now(),
            'password' => Hash::make('bb161077'),
            'remember_token' => Str::random(10),
            'role' => 'super_admin',
        ]);
    }
}
