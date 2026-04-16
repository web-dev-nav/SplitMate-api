<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AssignUuidsToUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Assign UUIDs to all users that don't have one
        User::whereNull('uuid')->each(function ($user) {
            $user->update(['uuid' => Str::uuid()]);
        });
    }
}
