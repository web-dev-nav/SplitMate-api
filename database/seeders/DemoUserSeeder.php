<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoUserSeeder extends Seeder
{
    /**
     * Seed a demo user into the database (runs only once via firstOrCreate).
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'demo@splitmate.com'],
            [
                'uuid' => Str::uuid()->toString(),
                'name' => 'Demo User',
                'password' => Hash::make('demo1234'),
                'is_active' => true,
            ]
        );
    }
}
